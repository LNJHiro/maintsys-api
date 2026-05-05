<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Clientes;

class ClientesFactory extends Factory
{
    protected $model = Clientes::class;

    public function definition(): array
    {
        return [
            'nome' => fake()->name(),
            'cpf' => fake()->numerify('###.###.###-##'),
            'telefone' => fake()->numerify('(##) 9####-####'),
        ];
    }
}