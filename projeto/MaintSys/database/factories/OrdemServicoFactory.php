<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\OrdemServico;
use App\Models\Maquina;
use App\Models\Tecnico;

class OrdemServicoFactory extends Factory
{
    protected $model = OrdemServico::class;

    public function definition(): array
    {
        $dataAbertura = fake()->dateTimeBetween('-60 days', 'now');
        $dataPrevista = fake()->dateTimeBetween($dataAbertura, '+30 days');

        return [
            'numero' => 'OS-' . now()->format('Ymd') . '-' . fake()->unique()->numberBetween(1000, 9999),
            'tipo' => fake()->randomElement([
                'preventiva',
                'corretiva',
            ]),
            'status' => fake()->randomElement([
                'aberta',
                'em_andamento',
                'concluida',
                'cancelada',
            ]),
            'prioridade' => fake()->randomElement([
                'baixa',
                'media',
                'alta',
                'critica',
            ]),
            'descricao' => fake()->paragraph(),
            'solucao' => fake()->optional()->paragraph(),
            'maquina_id' => Maquina::factory(),
            'tecnico_id' => Tecnico::factory(),
            'data_abertura' => $dataAbertura,
            'data_prevista' => $dataPrevista,
            'data_conclusao' => fake()->optional()->dateTimeBetween($dataAbertura, 'now'),
        ];
    }
}