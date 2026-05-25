<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tecnico extends Model
{
    use HasFactory;

    protected $table = 'tecnicos';

    protected $fillable = [
        'user_id',
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

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function historicos()
    {
        return $this->hasMany(HistoricoManutencao::class, 'tecnico_id');
    }
}
