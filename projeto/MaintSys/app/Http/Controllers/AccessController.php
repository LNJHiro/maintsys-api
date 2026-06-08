<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\RolePermission;
use App\Models\User;
use App\Models\UserPermission;
use Illuminate\Http\Request;
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
        $permissions = Permission::orderBy('modulo')->orderBy('name')->get()->groupBy('modulo');
        $users = User::where('role', '!=', 'admin_master')->orderBy('name')->get();

        $userPermissions = [];
        $userHasIndividual = [];

        foreach ($users as $user) {
            $individual = UserPermission::where('user_id', $user->id)
                ->pluck('permission_id')
                ->toArray();

            $userHasIndividual[$user->id] = (bool) $user->permissions_overridden;

            if (!$user->permissions_overridden) {
                $individual = RolePermission::where('role', $user->role)
                    ->pluck('permission_id')
                    ->toArray();
            }

            $userPermissions[$user->id] = $individual;
        }

        $rolePermissions = [];
        foreach (['admin', 'usuario'] as $role) {
            $rolePermissions[$role] = RolePermission::where('role', $role)
                ->pluck('permission_id')
                ->toArray();
        }

        return view('acesso.dashboard', [
            'permissions' => $permissions,
            'users' => $users,
            'userPermissions' => $userPermissions,
            'userHasIndividual' => $userHasIndividual,
            'rolePermissions' => $rolePermissions,
        ]);
    }

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
        if ($user->role === 'admin_master') {
            return response()->json(['error' => 'Nao e possivel alterar um Admin Master'], 403);
        }

        $validated = $request->validate([
            'inherit' => ['sometimes', 'boolean'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['integer', 'exists:permissions,id'],
        ]);

        $inherit = (bool) ($validated['inherit'] ?? false);
        $permissionIds = array_values(array_unique($validated['permissions'] ?? []));

        DB::transaction(function () use ($user, $inherit, $permissionIds) {
            UserPermission::where('user_id', $user->id)->delete();

            if (!$inherit) {
                foreach ($permissionIds as $permissionId) {
                    UserPermission::create([
                        'user_id' => $user->id,
                        'permission_id' => $permissionId,
                    ]);
                }
            }

            $user->update(['permissions_overridden' => !$inherit]);
            $user->clearPermissionCache();
        });

        $message = $inherit
            ? "Permissoes de '{$user->name}' voltaram a herdar do nivel"
            : "Permissoes de '{$user->name}' atualizadas com sucesso";

        return response()->json(['message' => $message]);
    }

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
        if (!in_array($role, ['admin', 'usuario'])) {
            return response()->json(['error' => 'Role invalido'], 400);
        }

        $validated = $request->validate([
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['integer', 'exists:permissions,id'],
        ]);

        $permissionIds = array_values(array_unique($validated['permissions'] ?? []));

        DB::transaction(function () use ($role, $permissionIds) {
            RolePermission::where('role', $role)->delete();

            foreach ($permissionIds as $permissionId) {
                RolePermission::create([
                    'role' => $role,
                    'permission_id' => $permissionId,
                ]);
            }
        });

        return response()->json(['message' => "Permissoes de '$role' atualizadas com sucesso"]);
    }

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
        $users = User::where('role', '!=', 'admin_master')->get();
        $roles = ['admin', 'usuario'];

        return view('acesso.usuarios', [
            'users' => $users,
            'roles' => $roles,
        ]);
    }

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
        if ($user->role === 'admin_master') {
            return response()->json(['error' => 'Nao e possivel alterar um Admin Master'], 403);
        }

        $validated = $request->validate([
            'role' => ['required', 'in:admin,usuario'],
        ]);

        $newRole = $validated['role'];
        $roleChanged = $user->role !== $newRole;

        DB::transaction(function () use ($user, $newRole, $roleChanged) {
            $user->update(['role' => $newRole]);

            if ($roleChanged) {
                UserPermission::where('user_id', $user->id)->delete();
                $user->update(['permissions_overridden' => false]);
            }

            $user->clearPermissionCache();
        });

        return response()->json(['message' => "Role de '{$user->name}' alterado para '$newRole'"]);
    }
}
