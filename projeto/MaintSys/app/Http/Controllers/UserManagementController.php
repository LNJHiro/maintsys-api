<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Permission;
use App\Models\RolePermission;
use App\Models\UserPermission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        $users = User::where('role', '!=', 'admin_master')->get();

        return view('usuarios.index', [
            'users' => $users,
        ]);
    }

    /**
     * FUNÇÃO: create()
     * ENTRADA: Nenhuma
     * PROCESSAMENTO: Prepara view com lista de roles disponíveis
     * SAÍDA: Form de criação de novo usuário
     * USO: GET /usuarios/criar
     */
    public function create()
    {
        return view('usuarios.create', [
            'roles' => ['admin', 'usuario'],
        ]);
    }

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
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
            'role' => 'required|in:admin,usuario',
        ]);

        $validated['password'] = Hash::make($validated['password']);

        User::create($validated);

        return redirect()->route('usuarios.index')
            ->with('success', 'Usuário criado com sucesso!');
    }

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
        if ($user->role === 'admin_master') {
            abort(403, 'Não é possível editar um Admin Master');
        }

        $permissions = Permission::orderBy('modulo')->orderBy('name')->get()->groupBy('modulo');
        $userPermissions = $this->effectivePermissionIds($user);

        return view('usuarios.edit', [
            'user' => $user,
            'roles' => ['admin', 'usuario'],
            'permissions' => $permissions,
            'userPermissions' => $userPermissions,
        ]);
    }

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
        if ($user->role === 'admin_master') {
            abort(403, 'Não é possível editar um Admin Master');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|min:6',
            'role' => 'required|in:admin,usuario',
        ]);

        if (!$validated['password']) {
            unset($validated['password']);
        } else {
            $validated['password'] = Hash::make($validated['password']);
        }

        DB::transaction(function () use ($user, $validated) {
            $roleChanged = array_key_exists('role', $validated) && $user->role !== $validated['role'];

            $user->update($validated);

            if ($roleChanged) {
                UserPermission::where('user_id', $user->id)->delete();
                $user->update(['permissions_overridden' => false]);
                $user->clearPermissionCache();
            }
        });

        return redirect()->route('usuarios.index')
            ->with('success', 'Usuário atualizado com sucesso!');
    }

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
        if ($user->role === 'admin_master') {
            abort(403, 'Não é possível deletar um Admin Master');
        }

        $user->delete();

        return redirect()->route('usuarios.index')
            ->with('success', 'Usuário deletado com sucesso!');
    }

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
        if ($user->role === 'admin_master') {
            abort(403, 'Admin Master tem acesso a tudo');
        }

        $permissions = Permission::orderBy('modulo')->orderBy('name')->get()->groupBy('modulo');
        $userPermissions = $this->effectivePermissionIds($user);

        return view('usuarios.permissions', [
            'user' => $user,
            'permissions' => $permissions,
            'userPermissions' => $userPermissions,
        ]);
    }

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
        $individual = UserPermission::where('user_id', $user->id)
            ->pluck('permission_id')
            ->toArray();

        if ($user->permissions_overridden) {
            return $individual;
        }

        return RolePermission::where('role', $user->role)
            ->pluck('permission_id')
            ->toArray();
    }
}
