<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use App\Models\Tecnico;

class TecnicoFactory extends Factory
{
    protected $model = Tecnico::class;

    public function definition(): array
    {
        return [
            'nome' => fake()->name(),
            'matricula' => fake()->unique()->numerify('TEC-####'),
            'email' => fake()->unique()->safeEmail(),
            'password' => Hash::make('password'),
            'especialidade' => fake()->randomElement([
                'Mecânica',
                'Elétrica',
                'Automação',
                'Instrumentação',
                'Manutenção Geral',
            ]),
            'telefone' => fake()->numerify('(##) 9####-####'),
            'ativo' => fake()->boolean(90),
        ];
    }
}