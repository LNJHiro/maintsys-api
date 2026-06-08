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

// Define o namespace deste model dentro da aplicação Laravel
namespace App\Models;

// Importa a trait HasFactory para geração de factories de teste
use Illuminate\Database\Eloquent\Factories\HasFactory;
// Importa a classe base Model do Eloquent ORM
use Illuminate\Database\Eloquent\Model;

// Declara a classe OrdemServico que herda de Model (Eloquent ORM do Laravel)
class OrdemServico extends Model
{
    // Inclui a trait HasFactory para suporte a factories de teste
    use HasFactory;

    // Define o nome da tabela no banco de dados
    protected $table = 'ordens_servico';

    // Campos que podem ser preenchidos em massa (mass assignment)
    protected $fillable = [
        'numero',         // Número único da OS no formato OS-YYYYMMDD-XXXX
        'tipo',           // Tipo da manutenção: preventiva (planejada) ou corretiva (emergência)
        'status',         // Status atual: aberta, em_andamento, concluida, cancelada
        'prioridade',     // Nível de prioridade: baixa, media, alta, critica
        'descricao',      // Descrição detalhada do problema ou serviço a realizar
        'solucao',        // Solução aplicada após conclusão da manutenção
        'maquina_id',     // ID da máquina que receberá a manutenção
        'tecnico_id',     // ID do técnico responsável pela execução
        'data_abertura',  // Data e hora em que a OS foi criada
        'data_prevista',  // Data prevista para conclusão da manutenção
        'data_conclusao', // Data e hora real em que a manutenção foi concluída
    ];

    // Conversão automática de tipos de dados dos atributos
    protected $casts = [
        'data_abertura'  => 'datetime', // Data de abertura com hora (objeto Carbon)
        'data_prevista'  => 'date',     // Data prevista sem hora (apenas data)
        'data_conclusao' => 'datetime', // Data de conclusão com hora (objeto Carbon)
    ];

    /**
     * Relacionamento: Uma OS pertence a uma máquina
     * ENTRADA: Nenhuma
     * PROCESSAMENTO: Define relacionamento belongsTo com Maquina
     * SAÍDA: Instância de Maquina ou null
     */
    public function maquina()
    {
        // Uma OS sempre está vinculada a uma única máquina que receberá a manutenção
        return $this->belongsTo(Maquina::class, 'maquina_id');
    } // fim do método maquina

    /**
     * Relacionamento: Uma OS é atribuída a um técnico
     * ENTRADA: Nenhuma
     * PROCESSAMENTO: Define relacionamento belongsTo com Tecnico
     * SAÍDA: Instância de Tecnico ou null
     */
    public function tecnico()
    {
        // Uma OS é atribuída a um único técnico responsável pela execução
        return $this->belongsTo(Tecnico::class, 'tecnico_id');
    } // fim do método tecnico

    /**
     * Relacionamento: Uma OS pode ter um registro no histórico de manutenção
     * ENTRADA: Nenhuma
     * PROCESSAMENTO: Define relacionamento hasOne com HistoricoManutencao
     * SAÍDA: Instância de HistoricoManutencao ou null
     */
    public function historico()
    {
        // Uma OS concluída gera exatamente um registro no histórico de manutenção
        return $this->hasOne(HistoricoManutencao::class, 'ordem_id');
    } // fim do método historico

    /**
     * ATRIBUTO ACESSOR: getTipoLabelAttribute()
     * ENTRADA: Usa $this->tipo do modelo
     * PROCESSAMENTO: Converte tipo em código para label legível
     * SAÍDA: String com tipo em português
     * USO: $ordem->tipo_label
     */
    public function getTipoLabelAttribute(): string
    {
        // Usa match para mapear o código do tipo para um rótulo legível em português
        return match($this->tipo) {
            'preventiva' => 'Preventiva', // Manutenção planejada para evitar falhas
            'corretiva'  => 'Corretiva',  // Manutenção de emergência para corrigir falha
            default      => 'Desconhecido', // Tipo não reconhecido pelo sistema
        };
    } // fim do método getTipoLabelAttribute

    /**
     * ATRIBUTO ACESSOR: getStatusLabelAttribute()
     * ENTRADA: Usa $this->status do modelo
     * PROCESSAMENTO: Converte status em código para label legível
     * SAÍDA: String com status em português
     * USO: $ordem->status_label
     */
    public function getStatusLabelAttribute(): string
    {
        // Usa match para mapear o código de status para um rótulo legível em português
        return match($this->status) {
            'aberta'       => 'Aberta',        // OS criada, aguardando início da execução
            'em_andamento' => 'Em Andamento',  // Técnico está executando a manutenção
            'concluida'    => 'Concluída',     // Manutenção finalizada com sucesso
            'cancelada'    => 'Cancelada',     // OS cancelada antes da execução
            default        => 'Desconhecido',  // Status não reconhecido pelo sistema
        };
    } // fim do método getStatusLabelAttribute

    /**
     * ATRIBUTO ACESSOR: getPrioridadeLabelAttribute()
     * ENTRADA: Usa $this->prioridade do modelo
     * PROCESSAMENTO: Converte prioridade em código para label legível
     * SAÍDA: String com prioridade em português
     * USO: $ordem->prioridade_label
     */
    public function getPrioridadeLabelAttribute(): string
    {
        // Usa match para mapear o código de prioridade para um rótulo legível em português
        return match($this->prioridade) {
            'baixa'  => 'Baixa',   // Manutenção pode ser agendada sem urgência
            'media'  => 'Média',   // Prioridade normal, dentro do prazo padrão
            'alta'   => 'Alta',    // Deve ser resolvida com brevidade
            'critica'=> 'Crítica', // Emergência, máquina parada afetando produção
            default  => 'Normal',  // Prioridade padrão para casos não mapeados
        };
    } // fim do método getPrioridadeLabelAttribute

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
        // Gera o prefixo da OS com a data atual no formato OS-YYYYMMDD-
        $prefix = 'OS-' . now()->format('Ymd') . '-';

        // Busca o número mais recente de OS criado hoje usando LIKE com o prefixo
        // lockForUpdate() bloqueia as linhas no banco até a transação terminar,
        // evitando colisão de números quando duas OS são geradas simultaneamente
        $ultimo = self::where('numero', 'like', $prefix . '%') // Filtra apenas OS de hoje
            ->lockForUpdate()  // Bloqueia as linhas para evitar condição de corrida (race condition)
            ->orderByDesc('numero') // Ordena em ordem decrescente para pegar o mais recente
            ->value('numero');  // Retorna apenas o valor do campo 'numero' (sem instância)

        // Se existir OS hoje, extrai os últimos 4 dígitos e incrementa; senão começa do 1
        $proximo = $ultimo ? ((int) substr($ultimo, -4)) + 1 : 1;

        // Formata o número com zeros à esquerda para garantir 4 dígitos (ex: 1 vira 0001)
        return $prefix . str_pad($proximo, 4, '0', STR_PAD_LEFT);
    } // fim do método gerarNumero
} // fim da classe OrdemServico
