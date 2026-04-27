<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Tecnico extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'tecnicos';

    protected $fillable = [
        'nome',
        'matricula',
        'email',
        'password',
        'especialidade',
        'telefone',
        'ativo',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'ativo'    => 'boolean',
        'password' => 'hashed',
    ];

    public function ordens()
    {
        return $this->hasMany(OrdemServico::class, 'tecnico_id');
    }

    public function historicos()
    {
        return $this->hasMany(HistoricoManutencao::class, 'tecnico_id');
    }
}