<?php

/**
 * MODEL: Permission (Permissão do Sistema)
 *
 * Representa uma permissão ou ação que um usuário pode realizar no sistema.
 * As permissões são identificadas por nomes padronizados:
 * - recurso.acao (ex: usuarios.visualizar, maquinas.criar)
 *
 * Cada permissão pode estar associada a múltiplos roles (papéis) via RolePermission,
 * ou estar customizada para usuários específicos via UserPermission.
 *
 * Atributos principais:
 * - name: Identificador único (ex: 'maquinas.editar')
 * - descricao: Descrição legível (ex: 'Editar máquinas')
 * - modulo: Módulo do sistema (ex: 'maquinas', 'usuarios')
 */

// Define o namespace deste model dentro da aplicação Laravel
namespace App\Models;

// Importa a classe base Model do Eloquent ORM
use Illuminate\Database\Eloquent\Model;

// Declara a classe Permission que herda de Model (Eloquent ORM do Laravel)
class Permission extends Model
{
    // Campos que podem ser preenchidos em massa (mass assignment)
    protected $fillable = [
        'name',      // Nome único da permissão no formato recurso.acao (ex: 'usuarios.visualizar')
        'descricao', // Descrição legível em português para exibição na interface
        'modulo',    // Módulo do sistema ao qual esta permissão pertence (ex: 'maquinas')
    ];

    /**
     * Relacionamento: Uma permissão pode estar associada a múltiplos roles
     * ENTRADA: Nenhuma
     * PROCESSAMENTO: Define relacionamento hasMany com RolePermission
     * SAÍDA: Coleção de RolePermission associadas a esta permissão
     */
    public function roles()
    {
        // Uma permissão pode estar atribuída a vários roles diferentes ao mesmo tempo
        return $this->hasMany(RolePermission::class);
    } // fim do método roles
} // fim da classe Permission
