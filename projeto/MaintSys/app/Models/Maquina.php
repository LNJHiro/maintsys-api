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

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Maquina extends Model
{
    use HasFactory;

    // Define o nome da tabela no banco de dados
    protected $table = 'maquinas';

    // Campos que podem ser preenchidos em massa
    protected $fillable = [
        'numero_serie',   // Número/série único da máquina
        'modelo',         // Modelo da máquina
        'fabricante',     // Fabricante
        'localizacao',    // Local de instalação
        'data_cadastro',  // Data de cadastro no sistema
        'status',         // Status atual da máquina
        'descricao',      // Descrição ou observações
    ];

    // Conversão automática de tipos de dados
    protected $casts = [
        'data_cadastro' => 'date', // Converte data para formato Carbon
    ];

    /**
     * Relacionamento: Uma máquina pode ter múltiplas ordens de serviço
     * ENTRADA: Nenhuma
     * PROCESSAMENTO: Define relacionamento hasMany com OrdemServico
     * SAÍDA: Coleção de ordens de serviço da máquina
     */
    public function ordens()
    {
        return $this->hasMany(OrdemServico::class, 'maquina_id');
    }

    /**
     * Relacionamento: Uma máquina pode ter múltiplos históricos de manutenção
     * ENTRADA: Nenhuma
     * PROCESSAMENTO: Define relacionamento hasMany com HistoricoManutencao
     * SAÍDA: Coleção de históricos de manutenção da máquina
     */
    public function historicos()
    {
        return $this->hasMany(HistoricoManutencao::class, 'maquina_id');
    }

    /**
     * QUERY SCOPE: scopeOperacional($query)
     * ENTRADA: $query (Builder) - query builder do Laravel
     * PROCESSAMENTO: Filtra máquinas com status 'operacional'
     * SAÍDA: Query builder para chaining (ex: Maquina::operacional()->get())
     * USO: Maquina::operacional()->get()
     */
    public function scopeOperacional($query)
    {
        return $query->where('status', 'operacional');
    }

    /**
     * QUERY SCOPE: scopeEmManutencao($query)
     * ENTRADA: $query (Builder) - query builder do Laravel
     * PROCESSAMENTO: Filtra máquinas com status 'em_manutencao'
     * SAÍDA: Query builder para chaining
     * USO: Maquina::emManutencao()->get()
     */
    public function scopeEmManutencao($query)
    {
        return $query->where('status', 'em_manutencao');
    }

    /**
     * QUERY SCOPE: scopeParadaCritica($query)
     * ENTRADA: $query (Builder) - query builder do Laravel
     * PROCESSAMENTO: Filtra máquinas com status 'parada_critica'
     * SAÍDA: Query builder para chaining
     * USO: Maquina::paradaCritica()->get()
     */
    public function scopeParadaCritica($query)
    {
        return $query->where('status', 'parada_critica');
    }

    /**
     * ATRIBUTO ACESSOR: getStatusLabelAttribute()
     * ENTRADA: Usa $this->status do modelo
     * PROCESSAMENTO: Converte status em código (operacional) para rótulo legível (Operacional)
     * SAÍDA: String com label do status em português
     * USO: $maquina->status_label (acesso como atributo)
     */
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

    /**
     * ATRIBUTO ACESSOR: getStatusColorAttribute()
     * ENTRADA: Usa $this->status do modelo
     * PROCESSAMENTO: Mapeia status para cor visual para exibição em interface
     * SAÍDA: String com cor (verde, amarela, vermelha, cinza)
     * USO: $maquina->status_color para usar em CSS/HTML
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'operacional'    => 'green',     // Verde = funcionando normalmente
            'em_manutencao'  => 'yellow',    // Amarelo = em manutenção
            'parada_critica' => 'red',       // Vermelho = máquina parada
            'inativa'        => 'gray',      // Cinza = máquina inativa
            default          => 'gray',
        };
    }
}