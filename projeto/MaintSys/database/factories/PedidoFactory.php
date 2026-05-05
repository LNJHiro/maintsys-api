<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Pedido;

class PedidoFactory extends Factory
{
    protected $model = Pedido::class;

    public function definition(): array
    {
        return [
            'numero' => 'PED-' . fake()->unique()->numberBetween(1000,9999),
            'data' => fake()->date(),
            'status' => fake()->randomElement([
                'aberto',
                'em_producao',
                'entregue',
                'cancelado'
            ]),
            'observacao' => fake()->sentence(),
        ];
    }
}