<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\RolePermission;
use App\Models\UserPermission;
use App\Models\User;
use Illuminate\Http\Request;

class AccessController extends Controller
{
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

            $userHasIndividual[$user->id] = !empty($individual);

            // Se não tiver configuração individual, usa as permissões do role como ponto de partida
            if (empty($individual)) {
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

    public function updateUserPermissions(Request $request, User $user)
    {
        if ($user->role === 'admin_master') {
            return response()->json(['error' => 'Não é possível alterar um Admin Master'], 403);
        }

        $permissionIds = $request->input('permissions', []);

        UserPermission::where('user_id', $user->id)->delete();

        foreach ($permissionIds as $permissionId) {
            UserPermission::create([
                'user_id' => $user->id,
                'permission_id' => $permissionId,
            ]);
        }

        return response()->json(['message' => "Permissões de '{$user->name}' atualizadas com sucesso"]);
    }

    public function updateRole(Request $request, string $role)
    {
        if (!in_array($role, ['admin', 'usuario'])) {
            return response()->json(['error' => 'Role inválido'], 400);
        }

        $permissionIds = $request->input('permissions', []);

        RolePermission::where('role', $role)->delete();

        foreach ($permissionIds as $permissionId) {
            RolePermission::create([
                'role' => $role,
                'permission_id' => $permissionId,
            ]);
        }

        return response()->json(['message' => "Permissões de '$role' atualizadas com sucesso"]);
    }

    public function usuarios()
    {
        $users = User::where('role', '!=', 'admin_master')->get();
        $roles = ['admin', 'usuario'];

        return view('acesso.usuarios', [
            'users' => $users,
            'roles' => $roles,
        ]);
    }

    public function updateUsuario(Request $request, User $user)
    {
        if ($user->role === 'admin_master') {
            return response()->json(['error' => 'Não é possível alterar um Admin Master'], 403);
        }

        $newRole = $request->input('role');

        if (!in_array($newRole, ['admin', 'usuario'])) {
            return response()->json(['error' => 'Role inválido'], 400);
        }

        $user->update(['role' => $newRole]);

        return response()->json(['message' => "Role de '{$user->name}' alterado para '$newRole'"]);
    }
}
