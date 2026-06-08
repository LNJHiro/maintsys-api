<?php

// Define o namespace deste controller dentro da aplicação Laravel
namespace App\Http\Controllers;

// Importa o model User para manipular usuários
use App\Models\User;
// Importa o model Permission para buscar permissões disponíveis no sistema
use App\Models\Permission;
// Importa o model RolePermission para verificar permissões de roles
use App\Models\RolePermission;
// Importa o model UserPermission para manipular permissões individuais de usuários
use App\Models\UserPermission;
// Importa a classe Request para receber e validar dados da requisição HTTP
use Illuminate\Http\Request;
// Importa o facade DB para executar transações no banco de dados
use Illuminate\Support\Facades\DB;
// Importa o facade Hash para gerar hash seguro de senhas
use Illuminate\Support\Facades\Hash;

/**
 * CONTROLLER: UserManagementController
 *
 * Responsável pelo CRUD completo de usuários (não técnicos).
 * Funcionalidades:
 * - Listar usuários
 * - Criar novo usuário
 * - Editar usuário (name, email, password, role)
 * - Deletar usuário
 * - Visualizar permissões de um usuário
 */

// Declara a classe UserManagementController que herda de Controller (base do Laravel)
class UserManagementController extends Controller
{
    /**
     * FUNÇÃO: index()
     * ENTRADA: Nenhuma
     * PROCESSAMENTO:
     *   1. Busca todos os usuários exceto admin_master
     *   2. Ordena por nome
     * SAÍDA: View de listagem com botões Ver / Editar / Deletar
     * USO: GET /usuarios
     */
    public function index()
    {
        // Busca todos os usuários exceto admin_master (não pode ser gerenciado)
        $users = User::where('role', '!=', 'admin_master')->get();

        // Retorna a view de listagem de usuários com os dados
        return view('usuarios.index', [
            'users' => $users, // Lista de usuários
        ]);
    } // fim do método index

    /**
     * FUNÇÃO: create()
     * ENTRADA: Nenhuma
     * PROCESSAMENTO: Prepara view com lista de roles disponíveis
     * SAÍDA: Form de criação de novo usuário
     * USO: GET /usuarios/criar
     */
    public function create()
    {
        // Retorna a view de criação de usuário com os roles disponíveis para seleção
        return view('usuarios.create', [
            'roles' => ['admin', 'usuario'], // Roles que podem ser atribuídos
        ]);
    } // fim do método create

    /**
     * FUNÇÃO: store(Request $request)
     * ENTRADA:
     *   - name: nome completo
     *   - email: email (unique)
     *   - password: senha (mínimo 6 caracteres)
     *   - role: 'admin' ou 'usuario'
     * PROCESSAMENTO:
     *   1. Valida todos os campos
     *   2. Hash da senha com Hash::make()
     *   3. Cria novo User no banco
     *   4. Usuário herda permissões do seu role automaticamente
     * SAÍDA: Redirecionamento para listagem com mensagem de sucesso
     * USO: POST /usuarios
     */
    public function store(Request $request)
    {
        // Valida todos os campos obrigatórios do formulário de criação
        $validated = $request->validate([
            'name'     => 'required|string|max:255',      // Nome é obrigatório, máximo 255 caracteres
            'email'    => 'required|email|unique:users',  // Email é obrigatório, único na tabela users
            'password' => 'required|min:6',               // Senha obrigatória, mínimo 6 caracteres
            'role'     => 'required|in:admin,usuario',    // Role obrigatório, deve ser admin ou usuario
        ]);

        // Substitui a senha em texto puro pelo hash seguro antes de salvar
        $validated['password'] = Hash::make($validated['password']);

        // Cria o novo usuário no banco de dados com os dados validados
        User::create($validated);

        // Redireciona para a listagem de usuários com mensagem de sucesso
        return redirect()->route('usuarios.index')
            ->with('success', 'Usuário criado com sucesso!');
    } // fim do método store

    /**
     * FUNÇÃO: edit(User $user)
     * ENTRADA: User (model binding)
     * PROCESSAMENTO:
     *   1. Bloqueia edição de admin_master (403)
     *   2. Busca todas as permissões organizadas por módulo
     *   3. Calcula permissões efetivas do usuário (individual ou do role)
     * SAÍDA: Form de edição com campos: name, email, password (opcional), role
     * USO: GET /usuarios/{user}/editar
     */
    public function edit(User $user)
    {
        // Bloqueia edição do admin_master, retornando erro 403
        if ($user->role === 'admin_master') {
            abort(403, 'Não é possível editar um Admin Master');
        } // fim do if de bloqueio admin_master

        // Busca todas as permissões ordenadas por módulo e nome, agrupadas por módulo
        $permissions = Permission::orderBy('modulo')->orderBy('name')->get()->groupBy('modulo');
        // Calcula os IDs das permissões efetivas do usuário (individual ou herdadas do role)
        $userPermissions = $this->effectivePermissionIds($user);

        // Retorna a view de edição com todos os dados necessários para o formulário
        return view('usuarios.edit', [
            'user'            => $user,            // Usuário sendo editado
            'roles'           => ['admin', 'usuario'], // Roles disponíveis
            'permissions'     => $permissions,     // Permissões agrupadas por módulo
            'userPermissions' => $userPermissions, // IDs das permissões efetivas do usuário
        ]);
    } // fim do método edit

    /**
     * FUNÇÃO: update(Request $request, User $user)
     * ENTRADA:
     *   - User (model binding)
     *   - name: novo nome
     *   - email: novo email (unique)
     *   - password: nova senha (opcional — deixar em branco = não muda)
     *   - role: novo role
     * PROCESSAMENTO:
     *   1. Bloqueia edição de admin_master (403)
     *   2. Valida todos os campos
     *   3. Se password preenchida, faz hash; senão remove do array
     *   4. Transação DB:
     *      a. Atualiza dados do usuário
     *      b. Se role mudou: deleta permissões individuais e seta permissions_overridden=false
     *   5. Limpa cache de permissões
     * SAÍDA: Redirecionamento para listagem com sucesso
     * USO: PUT /usuarios/{user}
     */
    public function update(Request $request, User $user)
    {
        // Bloqueia edição do admin_master, retornando erro 403
        if ($user->role === 'admin_master') {
            abort(403, 'Não é possível editar um Admin Master');
        } // fim do if de bloqueio admin_master

        // Valida todos os campos do formulário de edição
        $validated = $request->validate([
            'name'     => 'required|string|max:255',                        // Nome obrigatório
            'email'    => 'required|email|unique:users,email,' . $user->id, // Email único, ignora o próprio usuário
            'password' => 'nullable|min:6',                                 // Senha opcional, mínimo 6 se preenchida
            'role'     => 'required|in:admin,usuario',                      // Role obrigatório e válido
        ]);

        // Verifica se o campo password foi preenchido
        if (!$validated['password']) {
            // Se vazio, remove para não sobrescrever a senha atual
            unset($validated['password']);
        } else {
            // Se preenchido, substitui pelo hash seguro
            $validated['password'] = Hash::make($validated['password']);
        } // fim do if de senha

        // Executa dentro de uma transação para garantir atomicidade
        DB::transaction(function () use ($user, $validated) {
            // Verifica se o role está sendo alterado
            $roleChanged = array_key_exists('role', $validated) && $user->role !== $validated['role'];

            // Atualiza os dados do usuário no banco de dados
            $user->update($validated);

            // Se o role mudou, precisa resetar as permissões para o padrão do novo role
            if ($roleChanged) {
                // Remove todas as permissões individuais do usuário
                UserPermission::where('user_id', $user->id)->delete();
                // Marca que o usuário não tem mais permissões customizadas (herda do role)
                $user->update(['permissions_overridden' => false]);
                // Limpa o cache de permissões em memória para forçar recarga
                $user->clearPermissionCache();
            } // fim do if de role alterado
        }); // fim da transação DB

        // Redireciona para a listagem de usuários com mensagem de sucesso
        return redirect()->route('usuarios.index')
            ->with('success', 'Usuário atualizado com sucesso!');
    } // fim do método update

    /**
     * FUNÇÃO: destroy(User $user)
     * ENTRADA: User (model binding)
     * PROCESSAMENTO:
     *   1. Bloqueia deleção de admin_master (403)
     *   2. Deleta o usuário (cascade deleta user_permissions)
     * SAÍDA: Redirecionamento para listagem com sucesso
     * USO: DELETE /usuarios/{user}
     * NOTA: Técnicos não são deletados, apenas desvinculados
     */
    public function destroy(User $user)
    {
        // Bloqueia deleção do admin_master, retornando erro 403
        if ($user->role === 'admin_master') {
            abort(403, 'Não é possível deletar um Admin Master');
        } // fim do if de bloqueio admin_master

        // Remove o usuário do banco de dados (cascade remove permissões individuais)
        $user->delete();

        // Redireciona para a listagem de usuários com mensagem de sucesso
        return redirect()->route('usuarios.index')
            ->with('success', 'Usuário deletado com sucesso!');
    } // fim do método destroy

    /**
     * FUNÇÃO: showPermissions(User $user)
     * ENTRADA: User (model binding)
     * PROCESSAMENTO:
     *   1. Bloqueia visualização de admin_master (403)
     *   2. Busca todas as permissões por módulo
     *   3. Calcula permissões efetivas do usuário (individual ou role)
     * SAÍDA: View read-only com grid de permissões (✓ ativo / ✗ bloqueado)
     * USO: GET /usuarios/{user}/permissoes
     * NOTA: Apenas visualização; edição feita via /acesso
     */
    public function showPermissions(User $user)
    {
        // Bloqueia visualização do admin_master pois ele tem acesso irrestrito
        if ($user->role === 'admin_master') {
            abort(403, 'Admin Master tem acesso a tudo');
        } // fim do if de bloqueio admin_master

        // Busca todas as permissões ordenadas por módulo e nome, agrupadas por módulo
        $permissions = Permission::orderBy('modulo')->orderBy('name')->get()->groupBy('modulo');
        // Calcula os IDs das permissões efetivas do usuário (individual ou herdadas do role)
        $userPermissions = $this->effectivePermissionIds($user);

        // Retorna a view de visualização de permissões com os dados necessários
        return view('usuarios.permissions', [
            'user'            => $user,            // Usuário sendo visualizado
            'permissions'     => $permissions,     // Permissões agrupadas por módulo
            'userPermissions' => $userPermissions, // IDs das permissões efetivas
        ]);
    } // fim do método showPermissions

    /**
     * FUNÇÃO: effectivePermissionIds(User $user) [PRIVADA]
     * ENTRADA: User
     * PROCESSAMENTO:
     *   1. Busca todas as permissões individuais do usuário (user_permissions)
     *   2. Se permissions_overridden=true → retorna as permissões individuais
     *   3. Se permissions_overridden=false → retorna as permissões do role dele
     * SAÍDA: Array de permission_ids que o usuário tem acesso
     * USO: Interno (edit, showPermissions)
     * LÓGICA: Implementa a hierarquia: individual > role > nada
     */
    private function effectivePermissionIds(User $user): array
    {
        // Busca os IDs de permissões individuais cadastradas para este usuário
        $individual = UserPermission::where('user_id', $user->id)
            ->pluck('permission_id') // Extrai apenas os IDs das permissões
            ->toArray(); // Converte para array PHP

        // Se o usuário tem permissões customizadas, retorna as individuais
        if ($user->permissions_overridden) {
            return $individual;
        } // fim do if de permissões customizadas

        // Caso contrário, busca e retorna as permissões do role do usuário
        return RolePermission::where('role', $user->role)
            ->pluck('permission_id') // Extrai apenas os IDs das permissões do role
            ->toArray(); // Converte para array PHP
    } // fim do método effectivePermissionIds
} // fim da classe UserManagementController
