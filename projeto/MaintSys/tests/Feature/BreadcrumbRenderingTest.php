<?php

namespace Tests\Feature;

use App\Models\Maquina;
use App\Models\OrdemServico;
use App\Models\Tecnico;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BreadcrumbRenderingTest extends TestCase
{
    use RefreshDatabase;

    public function test_breadcrumb_markup_is_rendered_on_create_pages(): void
    {
        $admin = User::factory()->create(['role' => 'admin_master']);

        $this->actingAs($admin)
            ->get(route('maquinas.create'))
            ->assertOk()
            ->assertSee('>máquinas</a>', false)
            ->assertDontSee('&lt;a href=', false);

        $this->actingAs($admin)
            ->get(route('usuarios.create'))
            ->assertOk()
            ->assertSee('>usuários</a>', false)
            ->assertDontSee('&lt;a href=', false);
    }

    public function test_breadcrumb_markup_is_rendered_on_order_show_page(): void
    {
        $admin = User::factory()->create(['role' => 'admin_master']);
        $maquina = Maquina::factory()->create();
        $tecnico = Tecnico::factory()->create();
        $ordem = OrdemServico::factory()->create([
            'maquina_id' => $maquina->id,
            'tecnico_id' => $tecnico->id,
        ]);

        $this->actingAs($admin)
            ->get(route('ordens.show', $ordem))
            ->assertOk()
            ->assertSee('>ordens</a>', false)
            ->assertSee($ordem->numero)
            ->assertDontSee('&lt;a href=', false);
    }
}
