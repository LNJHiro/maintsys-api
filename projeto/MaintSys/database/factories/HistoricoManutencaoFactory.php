<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\HistoricoManutencao;
use App\Models\Maquina;
use App\Models\Tecnico;
use App\Models\OrdemServico;

class HistoricoManutencaoFactory extends Factory
{
    protected $model = HistoricoManutencao::class;

    public function definition(): array
    {
        $dataInicio = fake()->dateTimeBetween('-90 days', 'now');
        $dataFim = fake()->dateTimeBetween($dataInicio, 'now');

        return [
            'maquina_id' => Maquina::factory(),
            'tecnico_id' => Tecnico::factory(),
            'ordem_id' => OrdemServico::factory(),
            'tipo' => fake()->randomElement([
                'preventiva',
                'corretiva',
                'preditiva',
                'inspecao',
            ]),
            'descricao' => fake()->paragraph(),
            'solucao' => fake()->paragraph(),
            'pecas_utilizadas' => fake()->optional()->sentence(),
            'tempo_parada_horas' => fake()->randomFloat(2, 0.5, 48),
            'custo' => fake()->randomFloat(2, 50, 10000),
            'data_inicio' => $dataInicio,
            'data_fim' => $dataFim,
            'observacoes' => fake()->optional()->sentence(),
        ];
    }
}