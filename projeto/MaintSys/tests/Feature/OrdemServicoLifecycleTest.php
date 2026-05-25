<?php

namespace Tests\Feature;

use App\Models\HistoricoManutencao;
use App\Models\Maquina;
use App\Models\OrdemServico;
use App\Models\Tecnico;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrdemServicoLifecycleTest extends TestCase
{
    use RefreshDatabase;

    public function test_concluding_order_creates_history_with_updated_machine_and_tecnico(): void
    {
        $admin = User::factory()->create(['role' => 'admin_master']);
        $maquinaAntiga = Maquina::factory()->create(['status' => 'em_manutencao']);
        $maquinaNova = Maquina::factory()->create(['status' => 'operacional']);
        $tecnicoAntigo = Tecnico::factory()->create();
        $tecnicoNovo = Tecnico::factory()->create();

        $ordem = OrdemServico::create([
            'numero' => 'OS-TEST-0001',
            'tipo' => 'corretiva',
            'status' => 'aberta',
            'prioridade' => 'media',
            'descricao' => 'Descricao antiga',
            'maquina_id' => $maquinaAntiga->id,
            'tecnico_id' => $tecnicoAntigo->id,
            'data_abertura' => now()->subHour(),
        ]);

        $response = $this->actingAs($admin)->put(route('ordens.update', $ordem), [
            'tipo' => 'corretiva',
            'prioridade' => 'alta',
            'status' => 'concluida',
            'descricao' => 'Descricao atualizada',
            'solucao' => 'Solucao aplicada',
            'maquina_id' => $maquinaNova->id,
            'tecnico_id' => $tecnicoNovo->id,
            'data_prevista' => null,
            'tempo_parada_horas' => 2.5,
            'custo' => 150.75,
            'pecas_utilizadas' => 'Correia',
        ]);

        $response->assertRedirect(route('ordens.index'));

        $historico = HistoricoManutencao::where('ordem_id', $ordem->id)->firstOrFail();

        $this->assertSame($maquinaNova->id, $historico->maquina_id);
        $this->assertSame($tecnicoNovo->id, $historico->tecnico_id);
        $this->assertSame('Descricao atualizada', $historico->descricao);
        $this->assertSame('Solucao aplicada', $historico->solucao);
        $this->assertSame('operacional', $maquinaAntiga->fresh()->status);
        $this->assertSame('operacional', $maquinaNova->fresh()->status);
    }

    public function test_canceling_last_active_order_returns_machine_to_operational(): void
    {
        $admin = User::factory()->create(['role' => 'admin_master']);
        $maquina = Maquina::factory()->create(['status' => 'em_manutencao']);
        $tecnico = Tecnico::factory()->create();

        $ordem = OrdemServico::create([
            'numero' => 'OS-TEST-0002',
            'tipo' => 'corretiva',
            'status' => 'aberta',
            'prioridade' => 'media',
            'descricao' => 'Servico aberto',
            'maquina_id' => $maquina->id,
            'tecnico_id' => $tecnico->id,
            'data_abertura' => now()->subHour(),
        ]);

        $this->actingAs($admin)->put(route('ordens.update', $ordem), [
            'tipo' => 'corretiva',
            'prioridade' => 'media',
            'status' => 'cancelada',
            'descricao' => 'Servico cancelado',
            'solucao' => null,
            'maquina_id' => $maquina->id,
            'tecnico_id' => $tecnico->id,
            'data_prevista' => null,
        ])->assertRedirect(route('ordens.index'));

        $this->assertSame('operacional', $maquina->fresh()->status);
    }

    public function test_deleting_last_active_order_returns_machine_to_operational(): void
    {
        $admin = User::factory()->create(['role' => 'admin_master']);
        $maquina = Maquina::factory()->create(['status' => 'em_manutencao']);
        $tecnico = Tecnico::factory()->create();

        $ordem = OrdemServico::create([
            'numero' => 'OS-TEST-0003',
            'tipo' => 'corretiva',
            'status' => 'aberta',
            'prioridade' => 'media',
            'descricao' => 'Servico aberto',
            'maquina_id' => $maquina->id,
            'tecnico_id' => $tecnico->id,
            'data_abertura' => now()->subHour(),
        ]);

        $this->actingAs($admin)
            ->delete(route('ordens.destroy', $ordem))
            ->assertRedirect(route('ordens.index'));

        $this->assertSame('operacional', $maquina->fresh()->status);
    }

    public function test_future_preventive_order_does_not_put_machine_in_maintenance(): void
    {
        $admin = User::factory()->create(['role' => 'admin_master']);
        $maquina = Maquina::factory()->create(['status' => 'operacional']);
        $tecnico = Tecnico::factory()->create();

        $this->actingAs($admin)->post(route('ordens.store'), [
            'tipo' => 'preventiva',
            'prioridade' => 'media',
            'descricao' => 'Preventiva futura',
            'maquina_id' => $maquina->id,
            'tecnico_id' => $tecnico->id,
            'data_prevista' => now()->addWeek()->toDateString(),
        ])->assertRedirect(route('ordens.index'));

        $this->assertSame('operacional', $maquina->fresh()->status);
    }
}
