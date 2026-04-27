<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrdemServico extends Model
{
    use HasFactory;

    protected $table = 'ordens_servico';

    protected $fillable = [
        'numero',
        'tipo',
        'status',
        'prioridade',
        'descricao',
        'solucao',
        'maquina_id',
        'tecnico_id',
        'data_abertura',
        'data_prevista',
        'data_conclusao',
    ];

    protected $casts = [
        'data_abertura'  => 'datetime',
        'data_prevista'  => 'date',
        'data_conclusao' => 'datetime',
    ];

    public function maquina()
    {
        return $this->belongsTo(Maquina::class, 'maquina_id');
    }

    public function tecnico()
    {
        return $this->belongsTo(Tecnico::class, 'tecnico_id');
    }

    public function historico()
    {
        return $this->hasOne(HistoricoManutencao::class, 'ordem_id');
    }

    public function getTipoLabelAttribute(): string
    {
        return match($this->tipo) {
            'preventiva' => 'Preventiva',
            'corretiva'  => 'Corretiva',
            default      => 'Desconhecido',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'aberta'       => 'Aberta',
            'em_andamento' => 'Em Andamento',
            'concluida'    => 'Concluída',
            'cancelada'    => 'Cancelada',
            default        => 'Desconhecido',
        };
    }

    public function getPrioridadeLabelAttribute(): string
    {
        return match($this->prioridade) {
            'baixa'  => 'Baixa',
            'media'  => 'Média',
            'alta'   => 'Alta',
            'critica'=> 'Crítica',
            default  => 'Normal',
        };
    }

    public static function gerarNumero(): string
    {
        $data = now()->format('Ymd');
        $ultimo = self::whereDate('created_at', today())->count() + 1;
        return 'OS-' . $data . '-' . str_pad($ultimo, 4, '0', STR_PAD_LEFT);
    }
}