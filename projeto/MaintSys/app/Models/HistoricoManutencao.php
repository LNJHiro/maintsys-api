<?php

/**
 * MODEL: HistoricoManutencao (Histórico de Manutenções Realizadas)
 *
 * Armazena o registro completo de manutenções realizadas em máquinas.
 * Cada registro contém detalhes técnicos, custos e tempo de parada.
 * Serve como auditoria e análise histórica de manutenções.
 *
 * Atributos principais:
 * - maquina_id: Máquina que foi mantida
 * - tecnico_id: Técnico que realizou a manutenção
 * - ordem_id: Ordem de serviço associada
 * - tipo: preventiva ou corretiva
 * - descricao: Descrição do problema/atividade
 * - solucao: Solução implementada
 * - pecas_utilizadas: Peças/componentes usados
 * - tempo_parada_horas: Tempo que máquina ficou parada
 * - custo: Custo total da manutenção
 * - data_inicio: Data/hora de início
 * - data_fim: Data/hora de término
 * - observacoes: Observações adicionais
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HistoricoManutencao extends Model
{
    use HasFactory;

    // Define o nome da tabela no banco de dados
    protected $table = 'historico_manutencoes';

    // Campos que podem ser preenchidos em massa
    protected $fillable = [
        'maquina_id',           // ID da máquina mantida
        'tecnico_id',           // ID do técnico responsável
        'ordem_id',             // ID da OS relacionada
        'tipo',                 // Tipo: preventiva ou corretiva
        'descricao',            // Descrição do serviço
        'solucao',              // Solução aplicada
        'pecas_utilizadas',     // Peças/componentes usados
        'tempo_parada_horas',   // Horas que máquina ficou parada
        'custo',                // Custo em R$ da manutenção
        'data_inicio',          // Data/hora de início
        'data_fim',             // Data/hora de término
        'observacoes',          // Observações adicionais
    ];

    // Conversão automática de tipos de dados
    protected $casts = [
        'data_inicio'        => 'datetime',     // Data com hora
        'data_fim'           => 'datetime',     // Data com hora
        'custo'              => 'decimal:2',    // Valor decimal com 2 casas
        'tempo_parada_horas' => 'decimal:2',    // Decimal com 2 casas (pode ter minutos)
    ];

    /**
     * Relacionamento: Um histórico pertence a uma máquina
     * ENTRADA: Nenhuma
     * PROCESSAMENTO: Define relacionamento belongsTo com Maquina
     * SAÍDA: Instância de Maquina ou null
     */
    public function maquina()
    {
        return $this->belongsTo(Maquina::class, 'maquina_id');
    }

    /**
     * Relacionamento: Um histórico pertence a um técnico
     * ENTRADA: Nenhuma
     * PROCESSAMENTO: Define relacionamento belongsTo com Tecnico
     * SAÍDA: Instância de Tecnico ou null
     */
    public function tecnico()
    {
        return $this->belongsTo(Tecnico::class, 'tecnico_id');
    }

    /**
     * Relacionamento: Um histórico pertence a uma Ordem de Serviço
     * ENTRADA: Nenhuma
     * PROCESSAMENTO: Define relacionamento belongsTo com OrdemServico
     * SAÍDA: Instância de OrdemServico ou null
     */
    public function ordem()
    {
        return $this->belongsTo(OrdemServico::class, 'ordem_id');
    }
}
