<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\RolePermission;
use App\Models\User;
use App\Models\UserPermission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PermissionManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_empty_individual_override_denies_role_permissions(): void
    {
        $master = User::factory()->create(['role' => 'admin_master']);
        $user = User::factory()->create(['role' => 'usuario']);
        $permission = Permission::create([
            'name' => 'maquinas.visualizar',
            'descricao' => 'Ver maquinas',
            'modulo' => 'maquinas',
        ]);

        RolePermission::create([
            'role' => 'usuario',
            'permission_id' => $permission->id,
        ]);

        $this->assertTrue($user->fresh()->hasPermission('maquinas.visualizar'));

        $this->actingAs($master)
            ->postJson(route('acesso.updateUserPermissions', $user), [
                'permissions' => [],
            ])
            ->assertOk();

        $user->refresh()->clearPermissionCache();

        $this->assertTrue($user->permissions_overridden);
        $this->assertFalse($user->hasPermission('maquinas.visualizar'));
    }

    public function test_user_permissions_can_be_reset_to_inherit_role(): void
    {
        $master = User::factory()->create(['role' => 'admin_master']);
        $user = User::factory()->create([
            'role' => 'usuario',
            'permissions_overridden' => true,
        ]);
        $permission = Permission::create([
            'name' => 'maquinas.visualizar',
            'descricao' => 'Ver maquinas',
            'modulo' => 'maquinas',
        ]);

        RolePermission::create([
            'role' => 'usuario',
            'permission_id' => $permission->id,
        ]);

        $this->assertFalse($user->fresh()->hasPermission('maquinas.visualizar'));

        $this->actingAs($master)
            ->postJson(route('acesso.updateUserPermissions', $user), [
                'inherit' => true,
            ])
            ->assertOk();

        $user->refresh()->clearPermissionCache();

        $this->assertFalse($user->permissions_overridden);
        $this->assertTrue($user->hasPermission('maquinas.visualizar'));
    }

    public function test_role_change_clears_individual_permissions(): void
    {
        $master = User::factory()->create(['role' => 'admin_master']);
        $user = User::factory()->create([
            'role' => 'usuario',
            'permissions_overridden' => true,
        ]);
        $permission = Permission::create([
            'name' => 'maquinas.visualizar',
            'descricao' => 'Ver maquinas',
            'modulo' => 'maquinas',
        ]);

        UserPermission::create([
            'user_id' => $user->id,
            'permission_id' => $permission->id,
        ]);

        $this->actingAs($master)
            ->patchJson(route('acesso.updateUsuario', $user), [
                'role' => 'admin',
            ])
            ->assertOk();

        $user->refresh();

        $this->assertSame('admin', $user->role);
        $this->assertFalse($user->permissions_overridden);
        $this->assertDatabaseMissing('user_permissions', [
            'user_id' => $user->id,
            'permission_id' => $permission->id,
        ]);
    }

    public function test_acesso_routes_use_granular_permission(): void
    {
        $user = User::factory()->create([
            'role' => 'usuario',
            'permissions_overridden' => true,
        ]);
        $permission = Permission::create([
            'name' => 'acesso.gerenciar',
            'descricao' => 'Gerenciar permissoes',
            'modulo' => 'acesso',
        ]);

        UserPermission::create([
            'user_id' => $user->id,
            'permission_id' => $permission->id,
        ]);

        $this->actingAs($user)
            ->get(route('acesso.index'))
            ->assertOk();
    }

    public function test_user_management_routes_require_user_permissions(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $this->actingAs($user)
            ->get(route('usuarios.index'))
            ->assertForbidden();

        $permission = Permission::create([
            'name' => 'usuarios.visualizar',
            'descricao' => 'Ver usuarios',
            'modulo' => 'usuarios',
        ]);

        RolePermission::create([
            'role' => 'admin',
            'permission_id' => $permission->id,
        ]);

        $user->refresh()->clearPermissionCache();

        $this->actingAs($user)
            ->get(route('usuarios.index'))
            ->assertOk();
    }
}
