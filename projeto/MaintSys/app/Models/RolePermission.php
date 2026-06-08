<?php

/**
 * MODEL: RolePermission (Associação de Permissões com Roles)
 *
 * Tabela de junção (pivot table) que associa permissões com roles (papéis/grupos).
 * Define quais permissões cada role tem acesso.
 *
 * Exemplo:
 * - Role 'admin' tem todas as permissões
 * - Role 'tecnico' tem permissões limitadas (apenas pode ver ordens dele)
 * - Role 'supervisor' tem permissões intermediárias
 *
 * Atributos principais:
 * - role: Nome do papel (string: 'admin', 'tecnico', 'supervisor', etc)
 * - permission_id: ID da permissão associada
 */

// Define o namespace deste model dentro da aplicação Laravel
namespace App\Models;

// Importa a classe base Model do Eloquent ORM
use Illuminate\Database\Eloquent\Model;

// Declara a classe RolePermission que herda de Model (Eloquent ORM do Laravel)
class RolePermission extends Model
{
    // Campos que podem ser preenchidos em massa (mass assignment)
    protected $fillable = [
        'role',           // Nome do papel/grupo ao qual a permissão é atribuída (ex: 'admin')
        'permission_id',  // ID da permissão que está sendo associada ao role
    ];

    /**
     * Relacionamento: Uma associação role-permissão pertence a uma permissão
     * ENTRADA: Nenhuma
     * PROCESSAMENTO: Define relacionamento belongsTo com Permission
     * SAÍDA: Instância de Permission ou null
     */
    public function permission()
    {
        // Um registro de RolePermission aponta para uma única permissão do sistema
        return $this->belongsTo(Permission::class);
    } // fim do método permission
} // fim da classe RolePermission
