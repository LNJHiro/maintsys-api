<?php

namespace Tests\Feature;

use App\Models\Maquina;
use App\Models\OrdemServico;
use App\Models\Tecnico;
use App\Models\User;
use App\Notifications\OrdemServicoAtribuida;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrdemServicoNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_tecnico_receives_notification_when_order_is_created_for_him(): void
    {
        $admin = User::factory()->create(['role' => 'admin_master']);
        $maquina = Maquina::factory()->create();
        $tecnico = Tecnico::factory()->create();

        $this->actingAs($admin)->post(route('ordens.store'), [
            'tipo' => 'corretiva',
            'prioridade' => 'alta',
            'descricao' => 'Verificar falha no motor',
            'maquina_id' => $maquina->id,
            'tecnico_id' => $tecnico->id,
            'data_prevista' => now()->addDay()->toDateString(),
        ])->assertRedirect(route('ordens.index'));

        $notification = $tecnico->user->notifications()->first();

        $this->assertNotNull($notification);
        $this->assertSame(OrdemServicoAtribuida::class, $notification->type);
        $this->assertSame('Nova O.S. atribuida', $notification->data['titulo']);
        $this->assertSame('alta', $notification->data['prioridade']);
        $this->assertNull($notification->read_at);
    }

    public function test_tecnico_receives_notification_when_order_is_reassigned_to_him(): void
    {
        $admin = User::factory()->create(['role' => 'admin_master']);
        $maquina = Maquina::factory()->create();
        $tecnicoAntigo = Tecnico::factory()->create();
        $tecnicoNovo = Tecnico::factory()->create();
        $ordem = OrdemServico::factory()->create([
            'tipo' => 'corretiva',
            'status' => 'aberta',
            'maquina_id' => $maquina->id,
            'tecnico_id' => $tecnicoAntigo->id,
            'data_abertura' => now()->subHour(),
            'data_prevista' => null,
        ]);

        $this->actingAs($admin)->put(route('ordens.update', $ordem), [
            'tipo' => 'corretiva',
            'prioridade' => 'media',
            'status' => 'em_andamento',
            'descricao' => 'Servico reatribuido',
            'solucao' => null,
            'maquina_id' => $maquina->id,
            'tecnico_id' => $tecnicoNovo->id,
            'data_prevista' => null,
        ])->assertRedirect(route('ordens.index'));

        $this->assertSame(0, $tecnicoAntigo->user->notifications()->count());
        $this->assertSame(1, $tecnicoNovo->user->notifications()->count());
    }

    public function test_order_update_without_tecnico_change_does_not_duplicate_notification(): void
    {
        $admin = User::factory()->create(['role' => 'admin_master']);
        $maquina = Maquina::factory()->create();
        $tecnico = Tecnico::factory()->create();

        $this->actingAs($admin)->post(route('ordens.store'), [
            'tipo' => 'corretiva',
            'prioridade' => 'media',
            'descricao' => 'Servico aberto',
            'maquina_id' => $maquina->id,
            'tecnico_id' => $tecnico->id,
            'data_prevista' => null,
        ])->assertRedirect(route('ordens.index'));

        $ordem = OrdemServico::firstOrFail();

        $this->actingAs($admin)->put(route('ordens.update', $ordem), [
            'tipo' => 'corretiva',
            'prioridade' => 'alta',
            'status' => 'em_andamento',
            'descricao' => 'Servico em andamento',
            'solucao' => null,
            'maquina_id' => $maquina->id,
            'tecnico_id' => $tecnico->id,
            'data_prevista' => null,
        ])->assertRedirect(route('ordens.index'));

        $this->assertSame(1, $tecnico->user->notifications()->count());
    }

    public function test_generated_preventive_order_notifies_assigned_tecnico(): void
    {
        $admin = User::factory()->create(['role' => 'admin_master']);
        $maquina = Maquina::factory()->create();
        $tecnico = Tecnico::factory()->create();
        $ordem = OrdemServico::factory()->create([
            'tipo' => 'preventiva',
            'status' => 'aberta',
            'prioridade' => 'media',
            'maquina_id' => $maquina->id,
            'tecnico_id' => $tecnico->id,
            'data_abertura' => now()->subHour(),
            'data_prevista' => now()->toDateString(),
        ]);

        $this->actingAs($admin)->put(route('ordens.update', $ordem), [
            'tipo' => 'preventiva',
            'prioridade' => 'media',
            'status' => 'concluida',
            'descricao' => 'Preventiva concluida',
            'solucao' => 'Checklist executado',
            'maquina_id' => $maquina->id,
            'tecnico_id' => $tecnico->id,
            'data_prevista' => now()->toDateString(),
            'proxima_preventiva' => now()->addMonth()->toDateString(),
            'tempo_parada_horas' => 1,
            'custo' => 0,
            'pecas_utilizadas' => null,
        ])->assertRedirect(route('ordens.index'));

        $proximaOrdem = OrdemServico::where('id', '!=', $ordem->id)->firstOrFail();
        $notification = $tecnico->user->notifications()->first();

        $this->assertSame(1, $tecnico->user->notifications()->count());
        $this->assertSame($proximaOrdem->id, $notification->data['ordem_id']);
        $this->assertSame($proximaOrdem->numero, $notification->data['numero']);
    }

    public function test_tecnico_can_see_and_mark_order_notifications_as_read_on_profile(): void
    {
        $admin = User::factory()->create(['role' => 'admin_master']);
        $maquina = Maquina::factory()->create();
        $tecnico = Tecnico::factory()->create();

        $this->actingAs($admin)->post(route('ordens.store'), [
            'tipo' => 'corretiva',
            'prioridade' => 'critica',
            'descricao' => 'Chamado urgente',
            'maquina_id' => $maquina->id,
            'tecnico_id' => $tecnico->id,
            'data_prevista' => null,
        ]);

        $this->actingAs($tecnico->user)
            ->get(route('profile.edit'))
            ->assertOk()
            ->assertSee('Nova O.S. atribuida')
            ->assertSee('Nova');

        $this->actingAs($tecnico->user)
            ->post(route('profile.notifications.read'))
            ->assertRedirect(route('profile.edit'));

        $this->assertNotNull($tecnico->user->notifications()->first()->read_at);
    }
}
