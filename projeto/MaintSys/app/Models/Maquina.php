<?php

/**
 * MODEL: Maquina (Máquina de Produção)
 *
 * Representa uma máquina que necessita de manutenção no sistema.
 * Cada máquina pode ter múltiplas ordens de serviço e histórico de manutenção.
 *
 * Atributos principais:
 * - numero_serie: Identificação única da máquina
 * - modelo: Modelo da máquina (ex: Impressora, Compressor)
 * - fabricante: Fabricante da máquina
 * - localizacao: Local onde máquina está instalada
 * - data_cadastro: Data de entrada no sistema
 * - status: Estado atual (operacional, em_manutencao, parada_critica, inativa)
 * - descricao: Descrição adicional ou observações
 */

// Define o namespace deste model dentro da aplicação Laravel
namespace App\Models;

// Importa a trait HasFactory para geração de factories de teste
use Illuminate\Database\Eloquent\Factories\HasFactory;
// Importa a classe base Model do Eloquent ORM
use Illuminate\Database\Eloquent\Model;

// Declara a classe Maquina que herda de Model (Eloquent ORM do Laravel)
class Maquina extends Model
{
    // Inclui a trait HasFactory para suporte a factories de teste
    use HasFactory;

    // Define o nome da tabela no banco de dados
    protected $table = 'maquinas';

    // Campos que podem ser preenchidos em massa (mass assignment)
    protected $fillable = [
        'numero_serie',  // Número de série único que identifica fisicamente a máquina
        'modelo',        // Modelo ou tipo da máquina (ex: Compressor, Torno, Prensa)
        'fabricante',    // Nome do fabricante da máquina
        'localizacao',   // Localização física onde a máquina está instalada
        'data_cadastro', // Data em que a máquina foi registrada no sistema
        'status',        // Status atual: operacional, em_manutencao, parada_critica, inativa
        'descricao',     // Descrição detalhada ou observações sobre a máquina
    ];

    // Conversão automática de tipos de dados dos atributos
    protected $casts = [
        'data_cadastro' => 'date', // Converte a data para objeto Carbon (sem hora)
    ];

    /**
     * Relacionamento: Uma máquina pode ter múltiplas ordens de serviço
     * ENTRADA: Nenhuma
     * PROCESSAMENTO: Define relacionamento hasMany com OrdemServico
     * SAÍDA: Coleção de ordens de serviço da máquina
     */
    public function ordens()
    {
        // Uma máquina pode ser objeto de várias ordens de serviço ao longo do tempo
        return $this->hasMany(OrdemServico::class, 'maquina_id');
    } // fim do método ordens

    /**
     * Relacionamento: Uma máquina pode ter múltiplos históricos de manutenção
     * ENTRADA: Nenhuma
     * PROCESSAMENTO: Define relacionamento hasMany com HistoricoManutencao
     * SAÍDA: Coleção de históricos de manutenção da máquina
     */
    public function historicos()
    {
        // Uma máquina pode ter várias manutenções registradas em seu histórico
        return $this->hasMany(HistoricoManutencao::class, 'maquina_id');
    } // fim do método historicos

    /**
     * QUERY SCOPE: scopeOperacional($query)
     * ENTRADA: $query (Builder) - query builder do Laravel
     * PROCESSAMENTO: Filtra máquinas com status 'operacional'
     * SAÍDA: Query builder para chaining (ex: Maquina::operacional()->get())
     * USO: Maquina::operacional()->get()
     */
    public function scopeOperacional($query)
    {
        // Adiciona filtro WHERE status = 'operacional' à query atual
        return $query->where('status', 'operacional');
    } // fim do scope scopeOperacional

    /**
     * QUERY SCOPE: scopeEmManutencao($query)
     * ENTRADA: $query (Builder) - query builder do Laravel
     * PROCESSAMENTO: Filtra máquinas com status 'em_manutencao'
     * SAÍDA: Query builder para chaining
     * USO: Maquina::emManutencao()->get()
     */
    public function scopeEmManutencao($query)
    {
        // Adiciona filtro WHERE status = 'em_manutencao' à query atual
        return $query->where('status', 'em_manutencao');
    } // fim do scope scopeEmManutencao

    /**
     * QUERY SCOPE: scopeParadaCritica($query)
     * ENTRADA: $query (Builder) - query builder do Laravel
     * PROCESSAMENTO: Filtra máquinas com status 'parada_critica'
     * SAÍDA: Query builder para chaining
     * USO: Maquina::paradaCritica()->get()
     */
    public function scopeParadaCritica($query)
    {
        // Adiciona filtro WHERE status = 'parada_critica' à query atual
        return $query->where('status', 'parada_critica');
    } // fim do scope scopeParadaCritica

    /**
     * ATRIBUTO ACESSOR: getStatusLabelAttribute()
     * ENTRADA: Usa $this->status do modelo
     * PROCESSAMENTO: Converte status em código (operacional) para rótulo legível (Operacional)
     * SAÍDA: String com label do status em português
     * USO: $maquina->status_label (acesso como atributo)
     */
    public function getStatusLabelAttribute(): string
    {
        // Usa match para mapear o código de status para um rótulo legível em português
        return match($this->status) {
            'operacional'    => 'Operacional',    // Máquina funcionando normalmente
            'em_manutencao'  => 'Em Manutenção',  // Máquina em processo de manutenção
            'parada_critica' => 'Parada Crítica', // Máquina parada por falha grave
            'inativa'        => 'Inativa',        // Máquina desativada ou fora de uso
            default          => 'Desconhecido',   // Status não reconhecido pelo sistema
        };
    } // fim do método getStatusLabelAttribute

    /**
     * ATRIBUTO ACESSOR: getStatusColorAttribute()
     * ENTRADA: Usa $this->status do modelo
     * PROCESSAMENTO: Mapeia status para cor visual para exibição em interface
     * SAÍDA: String com cor (verde, amarela, vermelha, cinza)
     * USO: $maquina->status_color para usar em CSS/HTML
     */
    public function getStatusColorAttribute(): string
    {
        // Usa match para mapear o código de status para uma cor de indicação visual
        return match($this->status) {
            'operacional'    => 'green',  // Verde = máquina funcionando normalmente
            'em_manutencao'  => 'yellow', // Amarelo = máquina em processo de manutenção
            'parada_critica' => 'red',    // Vermelho = máquina parada por falha crítica
            'inativa'        => 'gray',   // Cinza = máquina inativa ou desativada
            default          => 'gray',   // Cinza para status não reconhecidos
        };
    } // fim do método getStatusColorAttribute
} // fim da classe Maquina
