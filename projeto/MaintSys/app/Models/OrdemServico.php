<?php

/**
 * MODEL: OrdemServico (Ordem de Serviço / Manutenção)
 *
 * Representa uma ordem de serviço de manutenção a ser executada em uma máquina.
 * Pode ser do tipo preventiva (agendada) ou corretiva (emergência).
 *
 * Atributos principais:
 * - numero: ID único da ordem (ex: OS-20260608-0001)
 * - tipo: preventiva ou corretiva
 * - status: aberta, em_andamento, concluida, cancelada
 * - prioridade: baixa, media, alta, critica
 * - descricao: Problema/objetivo da manutenção
 * - solucao: Solução implementada
 * - maquina_id: Máquina que será mantida
 * - tecnico_id: Técnico responsável
 * - data_abertura: Data de criação da OS
 * - data_prevista: Data estimada de conclusão
 * - data_conclusao: Data real de conclusão
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrdemServico extends Model
{
    use HasFactory;

    // Define o nome da tabela no banco de dados
    protected $table = 'ordens_servico';

    // Campos que podem ser preenchidos em massa
    protected $fillable = [
        'numero',           // Número único da OS
        'tipo',             // Tipo: preventiva ou corretiva
        'status',           // Status atual da OS
        'prioridade',       // Nível de prioridade
        'descricao',        // Descrição do problema/serviço
        'solucao',          // Descrição da solução implementada
        'maquina_id',       // ID da máquina
        'tecnico_id',       // ID do técnico responsável
        'data_abertura',    // Data/hora de abertura
        'data_prevista',    // Data prevista para conclusão
        'data_conclusao',   // Data/hora real de conclusão
    ];

    // Conversão automática de tipos de dados
    protected $casts = [
        'data_abertura'  => 'datetime',  // Data com hora
        'data_prevista'  => 'date',      // Apenas data
        'data_conclusao' => 'datetime',  // Data com hora
    ];

    /**
     * Relacionamento: Uma OS pertence a uma máquina
     * ENTRADA: Nenhuma
     * PROCESSAMENTO: Define relacionamento belongsTo com Maquina
     * SAÍDA: Instância de Maquina ou null
     */
    public function maquina()
    {
        return $this->belongsTo(Maquina::class, 'maquina_id');
    }

    /**
     * Relacionamento: Uma OS é atribuída a um técnico
     * ENTRADA: Nenhuma
     * PROCESSAMENTO: Define relacionamento belongsTo com Tecnico
     * SAÍDA: Instância de Tecnico ou null
     */
    public function tecnico()
    {
        return $this->belongsTo(Tecnico::class, 'tecnico_id');
    }

    /**
     * Relacionamento: Uma OS pode ter um registro no histórico de manutenção
     * ENTRADA: Nenhuma
     * PROCESSAMENTO: Define relacionamento hasOne com HistoricoManutencao
     * SAÍDA: Instância de HistoricoManutencao ou null
     */
    public function historico()
    {
        return $this->hasOne(HistoricoManutencao::class, 'ordem_id');
    }

    /**
     * ATRIBUTO ACESSOR: getTipoLabelAttribute()
     * ENTRADA: Usa $this->tipo do modelo
     * PROCESSAMENTO: Converte tipo em código para label legível
     * SAÍDA: String com tipo em português
     * USO: $ordem->tipo_label
     */
    public function getTipoLabelAttribute(): string
    {
        return match($this->tipo) {
            'preventiva' => 'Preventiva',  // Manutenção planejada
            'corretiva'  => 'Corretiva',   // Manutenção de emergência
            default      => 'Desconhecido',
        };
    }

    /**
     * ATRIBUTO ACESSOR: getStatusLabelAttribute()
     * ENTRADA: Usa $this->status do modelo
     * PROCESSAMENTO: Converte status em código para label legível
     * SAÍDA: String com status em português
     * USO: $ordem->status_label
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'aberta'       => 'Aberta',        // Aguardando execução
            'em_andamento' => 'Em Andamento',  // Técnico está executando
            'concluida'    => 'Concluída',     // Manutenção finalizada
            'cancelada'    => 'Cancelada',     // OS cancelada
            default        => 'Desconhecido',
        };
    }

    /**
     * ATRIBUTO ACESSOR: getPrioridadeLabelAttribute()
     * ENTRADA: Usa $this->prioridade do modelo
     * PROCESSAMENTO: Converte prioridade em código para label legível
     * SAÍDA: String com prioridade em português
     * USO: $ordem->prioridade_label
     */
    public function getPrioridadeLabelAttribute(): string
    {
        return match($this->prioridade) {
            'baixa'  => 'Baixa',      // Pode esperar
            'media'  => 'Média',      // Prioridade normal
            'alta'   => 'Alta',       // Deve ser resolvida logo
            'critica'=> 'Crítica',    // Emergência, máquina parada
            default  => 'Normal',
        };
    }

    /**
     * FUNÇÃO ESTÁTICA: gerarNumero()
     * ENTRADA: Nenhuma
     * PROCESSAMENTO:
     *   1. Cria prefixo com data: "OS-20260608-"
     *   2. Busca último número da OS gerado hoje com lockForUpdate (evita duplicação)
     *   3. Incrementa contador de 1
     *   4. Preenche com zeros à esquerda para ter 4 dígitos (0001, 0002, etc)
     * SAÍDA: String com número único da OS (ex: "OS-20260608-0001")
     * OBSERVAÇÃO: lockForUpdate() é crítico para evitar conflito em requisições paralelas
     * USO: $numero = OrdemServico::gerarNumero();
     */
    public static function gerarNumero(): string
    {
        // Cria prefixo da data para organizar OS por dia
        $prefix = 'OS-' . now()->format('Ymd') . '-';

        // lockForUpdate evita colisão do número quando duas O.S.
        // são geradas em paralelo dentro da mesma transação.
        // Ele bloqueia as linhas no banco até a transação terminar.
        $ultimo = self::where('numero', 'like', $prefix . '%')
            ->lockForUpdate()
            ->orderByDesc('numero')
            ->value('numero');

        // Se existe OS hoje, incrementa. Senão, começa com 1
        $proximo = $ultimo ? ((int) substr($ultimo, -4)) + 1 : 1;

        // Formata número com zeros à esquerda (1 vira 0001)
        return $prefix . str_pad($proximo, 4, '0', STR_PAD_LEFT);
    }
}