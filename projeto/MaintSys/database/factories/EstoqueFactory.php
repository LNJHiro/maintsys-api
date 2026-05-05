<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Estoque;

class EstoqueFactory extends Factory
{
    protected $model = Estoque::class;

    public function definition(): array
    {
        return [
            'nome' => fake()->randomElement([
                'Tecido',
                'Linha',
                'Botão',
                'Zíper',
                'Elástico'
            ]),

            'produto' => fake()->randomElement([
                'Algodão',
                'Jeans',
                'Branca',
                'Preto',
                '20cm',
                '5mm'
            ]),

            'quantidade' => fake()->numberBetween(0, 200),

            'custo' => fake()->randomFloat(2, 5, 500),
        ];
    }
}