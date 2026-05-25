<?php

namespace Tests\Feature;

use App\Models\Maquina;
use App\Models\Permission;
use App\Models\RolePermission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PermissionRoutesTest extends TestCase
{
    use RefreshDatabase;

    public function test_maquinas_index_requires_visualizar_permission(): void
    {
        $user = User::factory()->create(['role' => 'usuario']);

        $this->actingAs($user)
            ->get(route('maquinas.index'))
            ->assertForbidden();
    }

    public function test_maquinas_show_requires_visualizar_permission(): void
    {
        $user = User::factory()->create(['role' => 'usuario']);
        $maquina = Maquina::factory()->create();

        $this->actingAs($user)
            ->get(route('maquinas.show', $maquina))
            ->assertForbidden();
    }

    public function test_maquinas_routes_allow_users_with_visualizar_permission(): void
    {
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

        $maquina = Maquina::factory()->create();

        $this->actingAs($user)
            ->get(route('maquinas.index'))
            ->assertOk();

        $this->actingAs($user)
            ->get(route('maquinas.show', $maquina))
            ->assertOk();
    }
}
