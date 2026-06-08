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

// Define o namespace deste model dentro da aplicação Laravel
namespace App\Models;

// Importa a trait HasFactory para geração de factories de teste
use Illuminate\Database\Eloquent\Factories\HasFactory;
// Importa a classe base Model do Eloquent ORM
use Illuminate\Database\Eloquent\Model;

// Declara a classe HistoricoManutencao que herda de Model (Eloquent ORM do Laravel)
class HistoricoManutencao extends Model
{
    // Inclui a trait HasFactory para suporte a factories de teste
    use HasFactory;

    // Define o nome da tabela no banco de dados
    protected $table = 'historico_manutencoes';

    // Campos que podem ser preenchidos em massa (mass assignment)
    protected $fillable = [
        'maquina_id',           // ID da máquina que recebeu a manutenção
        'tecnico_id',           // ID do técnico que executou a manutenção
        'ordem_id',             // ID da Ordem de Serviço vinculada a este histórico
        'tipo',                 // Tipo da manutenção: preventiva ou corretiva
        'descricao',            // Descrição detalhada do problema ou atividade realizada
        'solucao',              // Descrição da solução aplicada para resolver o problema
        'pecas_utilizadas',     // Lista de peças ou componentes utilizados na manutenção
        'tempo_parada_horas',   // Quantidade de horas que a máquina ficou parada
        'custo',                // Custo total em reais da manutenção realizada
        'data_inicio',          // Data e hora em que a manutenção foi iniciada
        'data_fim',             // Data e hora em que a manutenção foi finalizada
        'observacoes',          // Observações adicionais sobre a manutenção
    ];

    // Conversão automática de tipos de dados dos atributos
    protected $casts = [
        'data_inicio'        => 'datetime',  // Data de início com hora (objeto Carbon)
        'data_fim'           => 'datetime',  // Data de fim com hora (objeto Carbon)
        'custo'              => 'decimal:2', // Valor monetário com 2 casas decimais
        'tempo_parada_horas' => 'decimal:2', // Valor decimal com 2 casas (ex: 1.5 horas)
    ];

    /**
     * Relacionamento: Um histórico pertence a uma máquina
     * ENTRADA: Nenhuma
     * PROCESSAMENTO: Define relacionamento belongsTo com Maquina
     * SAÍDA: Instância de Maquina ou null
     */
    public function maquina()
    {
        // Um registro de histórico pertence a uma única máquina que foi mantida
        return $this->belongsTo(Maquina::class, 'maquina_id');
    } // fim do método maquina

    /**
     * Relacionamento: Um histórico pertence a um técnico
     * ENTRADA: Nenhuma
     * PROCESSAMENTO: Define relacionamento belongsTo com Tecnico
     * SAÍDA: Instância de Tecnico ou null
     */
    public function tecnico()
    {
        // Um registro de histórico pertence ao técnico que executou a manutenção
        return $this->belongsTo(Tecnico::class, 'tecnico_id');
    } // fim do método tecnico

    /**
     * Relacionamento: Um histórico pertence a uma Ordem de Serviço
     * ENTRADA: Nenhuma
     * PROCESSAMENTO: Define relacionamento belongsTo com OrdemServico
     * SAÍDA: Instância de OrdemServico ou null
     */
    public function ordem()
    {
        // Um registro de histórico está vinculado à Ordem de Serviço que o originou
        return $this->belongsTo(OrdemServico::class, 'ordem_id');
    } // fim do método ordem
} // fim da classe HistoricoManutencao
