<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Fornecedor;

class FornecedorFactory extends Factory
{
    protected $model = Fornecedor::class;

    public function definition(): array
    {
        return [
            'nome' => fake()->company(),
            'cnpj' => fake()->numerify('##.###.###/####-##'),
            'telefone' => fake()->phoneNumber(),
            'email' => fake()->safeEmail(),
        ];
    }
}