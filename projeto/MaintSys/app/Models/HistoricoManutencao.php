<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HistoricoManutencao extends Model
{
    use HasFactory;

    protected $table = 'historico_manutencoes';

    protected $fillable = [
        'maquina_id',
        'tecnico_id',
        'ordem_id',
        'tipo',
        'descricao',
        'solucao',
        'pecas_utilizadas',
        'tempo_parada_horas',
        'custo',
        'data_inicio',
        'data_fim',
        'observacoes',
    ];

    protected $casts = [
        'data_inicio'        => 'datetime',
        'data_fim'           => 'datetime',
        'custo'              => 'decimal:2',
        'tempo_parada_horas' => 'decimal:2',
    ];

    public function maquina()
    {
        return $this->belongsTo(Maquina::class, 'maquina_id');
    }

    public function tecnico()
    {
        return $this->belongsTo(Tecnico::class, 'tecnico_id');
    }

    public function ordem()
    {
        return $this->belongsTo(OrdemServico::class, 'ordem_id');
    }
}
