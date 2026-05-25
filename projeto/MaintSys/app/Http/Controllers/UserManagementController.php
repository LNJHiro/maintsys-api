<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Permission;
use App\Models\RolePermission;
use App\Models\UserPermission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserManagementController extends Controller
{
    public function index()
    {
        $users = User::where('role', '!=', 'admin_master')->get();

        return view('usuarios.index', [
            'users' => $users,
        ]);
    }

    public function create()
    {
        return view('usuarios.create', [
            'roles' => ['admin', 'usuario'],
        ]);
    }

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

    public function destroy(User $user)
    {
        if ($user->role === 'admin_master') {
            abort(403, 'Não é possível deletar um Admin Master');
        }

        $user->delete();

        return redirect()->route('usuarios.index')
            ->with('success', 'Usuário deletado com sucesso!');
    }

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
