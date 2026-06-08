<?php

/**
 * MODEL: Tecnico (Técnico Responsável por Manutenção)
 *
 * Representa um técnico do sistema que realiza manutenções em máquinas.
 * Cada técnico está associado a um usuário do sistema para login.
 *
 * Atributos principais:
 * - user_id: Referência ao usuário para autenticação
 * - nome: Nome completo do técnico
 * - matricula: Identificador único do técnico na empresa
 * - email: Email de contato
 * - especialidade: Área de especialidade (ex: elétrica, mecânica)
 * - telefone: Número de contato
 * - ativo: Flag indicando se técnico está ativo no sistema
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tecnico extends Model
{
    use HasFactory;

    // Define o nome da tabela no banco de dados
    protected $table = 'tecnicos';

    // Campos que podem ser preenchidos em massa
    protected $fillable = [
        'user_id',          // ID do usuário associado
        'nome',             // Nome do técnico
        'matricula',        // Matrícula única do técnico
        'email',            // Email de contato
        'password',         // Senha (raramente usada, pois login é via User)
        'especialidade',    // Especialização (elétrica, mecânica, etc)
        'telefone',         // Telefone de contato
        'ativo',            // Se técnico está ativo
    ];

    // Campos que não devem ser retornados em respostas (segurança)
    protected $hidden = [
        'password',
        'remember_token',
    ];

    // Conversão automática de tipos de dados
    protected $casts = [
        'ativo'    => 'boolean',  // Converte ativo para booleano (0/1 → true/false)
        'password' => 'hashed',   // Hash automático de senha
    ];

    /**
     * Relacionamento: Um técnico pode ter múltiplas ordens de serviço
     * ENTRADA: Nenhuma
     * PROCESSAMENTO: Define relacionamento hasMany com OrdemServico
     * SAÍDA: Coleção de ordens de serviço atribuídas ao técnico
     */
    public function ordens()
    {
        return $this->hasMany(OrdemServico::class, 'tecnico_id');
    }

    /**
     * Relacionamento: Um técnico pertence a um usuário do sistema
     * ENTRADA: Nenhuma
     * PROCESSAMENTO: Define relacionamento belongsTo com User
     * SAÍDA: Instância de User ou null
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relacionamento: Um técnico pode ter múltiplos registros no histórico
     * ENTRADA: Nenhuma
     * PROCESSAMENTO: Define relacionamento hasMany com HistoricoManutencao
     * SAÍDA: Coleção de históricos de manutenção do técnico
     */
    public function historicos()
    {
        return $this->hasMany(HistoricoManutencao::class, 'tecnico_id');
    }
}
