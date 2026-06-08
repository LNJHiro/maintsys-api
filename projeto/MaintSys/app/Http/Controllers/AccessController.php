<?php

// Define o namespace deste controller dentro da aplicação Laravel
namespace App\Http\Controllers;

// Importa o model Permission para manipular permissões do sistema
use App\Models\Permission;
// Importa o model RolePermission para manipular permissões de roles (grupos)
use App\Models\RolePermission;
// Importa o model User para manipular usuários
use App\Models\User;
// Importa o model UserPermission para manipular permissões individuais de usuários
use App\Models\UserPermission;
// Importa a classe Request para receber e validar dados da requisição HTTP
use Illuminate\Http\Request;
// Importa o facade DB para executar transações no banco de dados
use Illuminate\Support\Facades\DB;

/**
 * CONTROLLER: AccessController
 *
 * Responsável pela administração do sistema de permissões e acesso.
 * Funcionalidades:
 * - Gerenciar permissões por role (admin/usuario)
 * - Gerenciar permissões individuais de usuários
 * - Alterar role de usuários
 * - Visualizar e alterar permissões de forma centralizada
 */

// Declara a classe AccessController que herda de Controller (base do Laravel)
class AccessController extends Controller
{
    /**
     * FUNÇÃO: index()
     * ENTRADA: Nenhuma
     * PROCESSAMENTO:
     *   1. Busca todas as permissões ordenadas por módulo
     *   2. Busca todos os usuários (exceto admin_master)
     *   3. Para cada usuário, determina se usa permissões individuais ou do role
     *   4. Monta array com permissões efetivas de cada usuário
     *   5. Monta array com permissões de cada role (admin/usuario)
     * SAÍDA: View com grid de permissões: 2 colunas (admin vs usuario) com checkboxes AJAX
     * USO: GET /acesso
     */
    public function index()
    {
        // Busca todas as permissões ordenadas por módulo e nome, agrupadas por módulo
        $permissions = Permission::orderBy('modulo')->orderBy('name')->get()->groupBy('modulo');
        // Busca todos os usuários exceto admin_master, ordenados por nome
        $users = User::where('role', '!=', 'admin_master')->orderBy('name')->get();

        // Array que armazenará os IDs de permissões efetivas de cada usuário
        $userPermissions = [];
        // Array que indica se cada usuário tem permissões individuais (true) ou herda do role (false)
        $userHasIndividual = [];

        // Itera sobre cada usuário para calcular suas permissões efetivas
        foreach ($users as $user) {
            // Busca os IDs das permissões individuais cadastradas para este usuário
            $individual = UserPermission::where('user_id', $user->id)
                ->pluck('permission_id') // Extrai apenas os IDs das permissões
                ->toArray(); // Converte para array PHP

            // Registra se o usuário usa permissões individuais (true) ou herda do role (false)
            $userHasIndividual[$user->id] = (bool) $user->permissions_overridden;

            // Se o usuário NÃO tem permissões customizadas, usa as permissões do seu role
            if (!$user->permissions_overridden) {
                // Substitui pelas permissões do role do usuário
                $individual = RolePermission::where('role', $user->role)
                    ->pluck('permission_id') // Extrai apenas os IDs das permissões do role
                    ->toArray(); // Converte para array PHP
            } // fim do if de herança de permissões

            // Armazena as permissões efetivas do usuário no array indexado por user_id
            $userPermissions[$user->id] = $individual;
        } // fim do foreach de usuários

        // Array para armazenar permissões de cada role (admin, usuario)
        $rolePermissions = [];
        // Itera sobre os dois roles existentes no sistema
        foreach (['admin', 'usuario'] as $role) {
            // Busca e armazena os IDs de permissões associadas a este role
            $rolePermissions[$role] = RolePermission::where('role', $role)
                ->pluck('permission_id') // Extrai apenas os IDs
                ->toArray(); // Converte para array PHP
        } // fim do foreach de roles

        // Retorna a view do painel de controle de acesso com todos os dados necessários
        return view('acesso.dashboard', [
            'permissions'      => $permissions,      // Permissões agrupadas por módulo
            'users'            => $users,            // Lista de usuários
            'userPermissions'  => $userPermissions,  // Permissões efetivas por usuário
            'userHasIndividual'=> $userHasIndividual,// Indica se usuário tem perms individuais
            'rolePermissions'  => $rolePermissions,  // Permissões por role
        ]);
    } // fim do método index

    /**
     * FUNÇÃO: updateUserPermissions(Request $request, User $user)
     * ENTRADA:
     *   - User (model binding)
     *   - inherit (boolean): true = volta a herdar do role, false = usa individual
     *   - permissions[] (array): IDs das permissões individuais (se inherit=false)
     * PROCESSAMENTO:
     *   1. Bloqueia alteração de admin_master (403)
     *   2. Valida os IDs das permissões (devem existir no banco)
     *   3. Deleta todas as permissões individuais antigas
     *   4. Se inherit=false, insere as novas permissões individuais
     *   5. Atualiza flag `permissions_overridden` do usuário
     *   6. Limpa cache de permissões em memória
     * SAÍDA: JSON com mensagem de sucesso
     * USO: POST /acesso/usuario/{user}/permissoes (AJAX)
     */
    public function updateUserPermissions(Request $request, User $user)
    {
        // Bloqueia alteração de usuário admin_master, retornando erro 403
        if ($user->role === 'admin_master') {
            return response()->json(['error' => 'Nao e possivel alterar um Admin Master'], 403);
        } // fim do if de bloqueio admin_master

        // Valida os dados recebidos na requisição
        $validated = $request->validate([
            // inherit é opcional e deve ser booleano (true/false)
            'inherit'      => ['sometimes', 'boolean'],
            // permissions é opcional e deve ser um array
            'permissions'  => ['nullable', 'array'],
            // Cada item do array deve ser inteiro e existir na tabela permissions
            'permissions.*'=> ['integer', 'exists:permissions,id'],
        ]);

        // Obtém o valor de inherit (padrão false se não informado)
        $inherit = (bool) ($validated['inherit'] ?? false);
        // Remove duplicatas e reindexa o array de IDs de permissões
        $permissionIds = array_values(array_unique($validated['permissions'] ?? []));

        // Executa dentro de uma transação para garantir atomicidade
        DB::transaction(function () use ($user, $inherit, $permissionIds) {
            // Remove todas as permissões individuais antigas deste usuário
            UserPermission::where('user_id', $user->id)->delete();

            // Se NÃO está herdando do role, insere as novas permissões individuais
            if (!$inherit) {
                // Itera sobre cada ID de permissão e cria o registro
                foreach ($permissionIds as $permissionId) {
                    // Cria um registro de permissão individual para o usuário
                    UserPermission::create([
                        'user_id'       => $user->id,    // ID do usuário
                        'permission_id' => $permissionId, // ID da permissão
                    ]);
                } // fim do foreach de permissões
            } // fim do if de permissões individuais

            // Atualiza a flag: true = tem perms individuais, false = herda do role
            $user->update(['permissions_overridden' => !$inherit]);
            // Limpa o cache de permissões em memória para forçar recarga
            $user->clearPermissionCache();
        }); // fim da transação DB

        // Define mensagem de feedback conforme a ação realizada
        $message = $inherit
            ? "Permissoes de '{$user->name}' voltaram a herdar do nivel"  // Voltou a herdar
            : "Permissoes de '{$user->name}' atualizadas com sucesso";     // Perms individuais salvas

        // Retorna resposta JSON com a mensagem de sucesso
        return response()->json(['message' => $message]);
    } // fim do método updateUserPermissions

    /**
     * FUNÇÃO: updateRole(Request $request, string $role)
     * ENTRADA:
     *   - role: 'admin' ou 'usuario'
     *   - permissions[] (array): IDs das permissões que este role deve ter
     * PROCESSAMENTO:
     *   1. Valida se role é 'admin' ou 'usuario'
     *   2. Valida se os IDs das permissões existem no banco
     *   3. Deleta TODAS as permissões antigas deste role
     *   4. Insere as novas permissões em DB::transaction() (atômico)
     *   5. Qualquer usuário que herda deste role passará a ter as novas perms
     * SAÍDA: JSON com mensagem de sucesso
     * USO: POST /acesso/role/{role} (AJAX)
     * NOTA: Crítico — alterando permissões de um role afeta todos os usuários que herdam dele
     */
    public function updateRole(Request $request, string $role)
    {
        // Verifica se o role informado é válido (apenas admin ou usuario são permitidos)
        if (!in_array($role, ['admin', 'usuario'])) {
            // Retorna erro 400 se o role não for reconhecido
            return response()->json(['error' => 'Role invalido'], 400);
        } // fim do if de validação do role

        // Valida os dados recebidos na requisição
        $validated = $request->validate([
            // permissions é opcional e deve ser um array
            'permissions'  => ['nullable', 'array'],
            // Cada item deve ser inteiro e existir na tabela permissions
            'permissions.*'=> ['integer', 'exists:permissions,id'],
        ]);

        // Remove duplicatas e reindexa o array de IDs de permissões
        $permissionIds = array_values(array_unique($validated['permissions'] ?? []));

        // Executa dentro de uma transação para garantir atomicidade
        DB::transaction(function () use ($role, $permissionIds) {
            // Remove todas as permissões antigas deste role
            RolePermission::where('role', $role)->delete();

            // Itera sobre cada ID de permissão e cria o novo registro
            foreach ($permissionIds as $permissionId) {
                // Cria um registro associando esta permissão ao role
                RolePermission::create([
                    'role'          => $role,         // Nome do role
                    'permission_id' => $permissionId, // ID da permissão
                ]);
            } // fim do foreach de permissões do role
        }); // fim da transação DB

        // Retorna resposta JSON confirmando a atualização das permissões do role
        return response()->json(['message' => "Permissoes de '$role' atualizadas com sucesso"]);
    } // fim do método updateRole

    /**
     * FUNÇÃO: usuarios()
     * ENTRADA: Nenhuma
     * PROCESSAMENTO:
     *   1. Busca todos os usuários (exceto admin_master)
     *   2. Prepara array com roles disponíveis
     * SAÍDA: View com grid de usuários e suas alterações de role
     * USO: GET /acesso/usuarios
     */
    public function usuarios()
    {
        // Busca todos os usuários exceto admin_master (que não pode ser alterado)
        $users = User::where('role', '!=', 'admin_master')->get();
        // Define os roles disponíveis para atribuição
        $roles = ['admin', 'usuario'];

        // Retorna a view de gerenciamento de usuários com os dados necessários
        return view('acesso.usuarios', [
            'users' => $users,  // Lista de usuários
            'roles' => $roles,  // Roles disponíveis para seleção
        ]);
    } // fim do método usuarios

    /**
     * FUNÇÃO: updateUsuario(Request $request, User $user)
     * ENTRADA:
     *   - User (model binding)
     *   - role: novo role ('admin' ou 'usuario')
     * PROCESSAMENTO:
     *   1. Bloqueia alteração de admin_master (403)
     *   2. Valida que role é 'admin' ou 'usuario'
     *   3. Se role mudou:
     *      - Atualiza o role do usuário
     *      - Deleta permissões individuais (volta a herdar)
     *      - Seta permissions_overridden=false
     *      - Limpa cache
     *   4. Se role não mudou, não faz nada
     * SAÍDA: JSON com mensagem de sucesso
     * USO: PATCH /acesso/usuario/{user} (AJAX)
     * NOTA: Ao mudar role, permissões individuais são perdidas (volta ao padrão)
     */
    public function updateUsuario(Request $request, User $user)
    {
        // Bloqueia alteração do admin_master, retornando erro 403
        if ($user->role === 'admin_master') {
            return response()->json(['error' => 'Nao e possivel alterar um Admin Master'], 403);
        } // fim do if de bloqueio admin_master

        // Valida que o campo role é obrigatório e deve ser admin ou usuario
        $validated = $request->validate([
            'role' => ['required', 'in:admin,usuario'],
        ]);

        // Armazena o novo role informado na requisição
        $newRole = $validated['role'];
        // Verifica se o role realmente mudou comparando com o atual
        $roleChanged = $user->role !== $newRole;

        // Executa dentro de uma transação para garantir atomicidade
        DB::transaction(function () use ($user, $newRole, $roleChanged) {
            // Atualiza o role do usuário no banco de dados
            $user->update(['role' => $newRole]);

            // Se o role mudou, precisa resetar as permissões para o padrão do novo role
            if ($roleChanged) {
                // Remove todas as permissões individuais do usuário
                UserPermission::where('user_id', $user->id)->delete();
                // Marca que o usuário não tem mais permissões customizadas (herda do role)
                $user->update(['permissions_overridden' => false]);
            } // fim do if de role alterado

            // Limpa o cache de permissões em memória para forçar recarga
            $user->clearPermissionCache();
        }); // fim da transação DB

        // Retorna resposta JSON confirmando a alteração do role
        return response()->json(['message' => "Role de '{$user->name}' alterado para '$newRole'"]);
    } // fim do método updateUsuario
} // fim da classe AccessController
