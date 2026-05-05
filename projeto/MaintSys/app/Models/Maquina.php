<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Maquina extends Model
{
    use HasFactory;

    protected $table = 'maquinas';

    protected $fillable = [
        'numero_serie',
        'modelo',
        'fabricante',
        'localizacao',
        'data_instalacao',
        'status',
        'descricao',
    ];

    protected $casts = [
        'data_instalacao' => 'date',
    ];

    public function ordens()
    {
        return $this->hasMany(OrdemServico::class, 'maquina_id');
    }

    public function historicos()
    {
        return $this->hasMany(HistoricoManutencao::class, 'maquina_id');
    }

    public function scopeOperacional($query)
    {
        return $query->where('status', 'operacional');
    }

    public function scopeEmManutencao($query)
    {
        return $query->where('status', 'em_manutencao');
    }

    public function scopeParadaCritica($query)
    {
        return $query->where('status', 'parada_critica');
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'operacional'    => 'Operacional',
            'em_manutencao'  => 'Em Manutenção',
            'parada_critica' => 'Parada Crítica',
            'inativa'        => 'Inativa',
            default          => 'Desconhecido',
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'operacional'    => 'green',
            'em_manutencao'  => 'yellow',
            'parada_critica' => 'red',
            'inativa'        => 'gray',
            default          => 'gray',
        };
    }
}