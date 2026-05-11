<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tecnico;
use App\Models\Maquina;
use App\Models\OrdemServico;
use App\Models\HistoricoManutencao;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Técnicos
        $tecnicos = Tecnico::factory()->count(10)->create();

        // Máquinas
        $maquinas = Maquina::factory()->count(15)->create();

        // Ordens de Serviço
        $ordens = OrdemServico::factory()
            ->count(30)
            ->make()
            ->each(function ($ordem) use ($maquinas, $tecnicos) {
                $ordem->maquina_id = $maquinas->random()->id;
                $ordem->tecnico_id = $tecnicos->random()->id;
                $ordem->save();
            });

        // Históricos de Manutenção
        HistoricoManutencao::factory()
            ->count(25)
            ->make()
            ->each(function ($historico) use ($ordens) {
                $ordem = $ordens->random();

                $historico->ordem_id = $ordem->id;
                $historico->maquina_id = $ordem->maquina_id;
                $historico->tecnico_id = $ordem->tecnico_id;
                $historico->save();
            });
    }
}