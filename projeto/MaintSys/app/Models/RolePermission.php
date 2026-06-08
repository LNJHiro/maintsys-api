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

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RolePermission extends Model
{
    // Campos que podem ser preenchidos em massa
    protected $fillable = [
        'role',           // Nome do papel/grupo (ex: 'admin', 'tecnico')
        'permission_id',  // ID da permissão associada
    ];

    /**
     * Relacionamento: Uma associação role-permissão pertence a uma permissão
     * ENTRADA: Nenhuma
     * PROCESSAMENTO: Define relacionamento belongsTo com Permission
     * SAÍDA: Instância de Permission ou null
     */
    public function permission()
    {
        return $this->belongsTo(Permission::class);
    }
}
