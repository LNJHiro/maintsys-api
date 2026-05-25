<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use App\Models\Tecnico;
use App\Models\User;

class TecnicoFactory extends Factory
{
    protected $model = Tecnico::class;

    public function definition(): array
    {
        $nome = fake()->name();
        $email = fake()->unique()->safeEmail();
        $password = Hash::make('password');

        return [
            'user_id' => User::factory()->state([
                'name' => $nome,
                'email' => $email,
                'password' => $password,
                'role' => 'usuario',
            ]),
            'nome' => $nome,
            'matricula' => fake()->unique()->numerify('TEC-####'),
            'email' => $email,
            'password' => $password,
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
