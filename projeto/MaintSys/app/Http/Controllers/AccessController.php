<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\RolePermission;
use App\Models\User;
use App\Models\UserPermission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
