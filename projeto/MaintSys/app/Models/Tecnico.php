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

// Define o namespace deste model dentro da aplicação Laravel
namespace App\Models;

// Importa a trait HasFactory para geração de factories de teste
use Illuminate\Database\Eloquent\Factories\HasFactory;
// Importa a classe base Model do Eloquent ORM
use Illuminate\Database\Eloquent\Model;

// Declara a classe Tecnico que herda de Model (Eloquent ORM do Laravel)
class Tecnico extends Model
{
    // Inclui a trait HasFactory para suporte a factories de teste
    use HasFactory;

    // Define o nome da tabela no banco de dados (evita pluralização automática errada)
    protected $table = 'tecnicos';

    // Campos que podem ser preenchidos em massa (mass assignment)
    protected $fillable = [
        'user_id',        // ID do usuário associado ao técnico para login
        'nome',           // Nome completo do técnico
        'matricula',      // Número de matrícula único do técnico na empresa
        'email',          // Email de contato do técnico
        'password',       // Senha (raramente usada, pois login é via model User)
        'especialidade',  // Área de especialização (ex: elétrica, mecânica, hidráulica)
        'telefone',       // Número de telefone de contato
        'ativo',          // Indica se o técnico está ativo no sistema (true/false)
    ];

    // Campos que não devem ser retornados em respostas (segurança)
    protected $hidden = [
        'password',       // Oculta a senha nas respostas JSON
        'remember_token', // Oculta o token de "lembrar sessão"
    ];

    // Conversão automática de tipos de dados dos atributos
    protected $casts = [
        'ativo'    => 'boolean', // Converte o valor 0/1 do banco para true/false
        'password' => 'hashed',  // Aplica hash automático na senha ao salvar
    ];

    /**
     * Relacionamento: Um técnico pode ter múltiplas ordens de serviço
     * ENTRADA: Nenhuma
     * PROCESSAMENTO: Define relacionamento hasMany com OrdemServico
     * SAÍDA: Coleção de ordens de serviço atribuídas ao técnico
     */
    public function ordens()
    {
        // Um técnico pode ser responsável por várias ordens de serviço
        return $this->hasMany(OrdemServico::class, 'tecnico_id');
    } // fim do método ordens

    /**
     * Relacionamento: Um técnico pertence a um usuário do sistema
     * ENTRADA: Nenhuma
     * PROCESSAMENTO: Define relacionamento belongsTo com User
     * SAÍDA: Instância de User ou null
     */
    public function user()
    {
        // Um técnico pertence a um único usuário do sistema (para login)
        return $this->belongsTo(User::class);
    } // fim do método user

    /**
     * Relacionamento: Um técnico pode ter múltiplos registros no histórico
     * ENTRADA: Nenhuma
     * PROCESSAMENTO: Define relacionamento hasMany com HistoricoManutencao
     * SAÍDA: Coleção de históricos de manutenção do técnico
     */
    public function historicos()
    {
        // Um técnico pode ter realizado várias manutenções ao longo do tempo
        return $this->hasMany(HistoricoManutencao::class, 'tecnico_id');
    } // fim do método historicos
} // fim da classe Tecnico
