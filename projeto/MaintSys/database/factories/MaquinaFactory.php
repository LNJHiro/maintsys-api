<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Maquina;

class MaquinaFactory extends Factory
{
    protected $model = Maquina::class;

    public function definition(): array
    {
        return [
            'numero_serie' => fake()->unique()->bothify('MQ-####-????'),
            'modelo' => fake()->randomElement([
                'Centrífuga Mark 3',
                'Compressor A5',
                'Bomba Centrífuga',
                'Esteira Transportadora',
                'Evaporador Industrial',
            ]),
            'fabricante' => fake()->company(),
            'localizacao' => fake()->randomElement([
                'Linha A',
                'Linha B',
                'Fermentação',
                'Isolamento',
                'Utilidades',
                'Envase',
            ]),
            'data_cadastro' => fake()->date(),
            'status' => fake()->randomElement([
                'operacional',
                'em_manutencao',
                'parada_critica',
                'inativa',
            ]),
            'descricao' => fake()->sentence(),
        ];
    }
}