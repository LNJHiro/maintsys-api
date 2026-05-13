<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate([
            'email' => 'admin@senai.br',
        ], [
            'name' => 'Administrador SENAI',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
        ]);

        User::firstOrCreate([
            'email' => 'usuario@senai.br',
        ], [
            'name' => 'Usuário SENAI',
            'password' => Hash::make('usuario123'),
            'role' => 'usuario',
        ]);
    }
}
