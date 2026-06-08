<?php

/**
 * MODEL: UserPermission (Permissões Customizadas de Usuário)
 *
 * Tabela de junção para permissões específicas de um usuário.
 * Usada quando um usuário precisa de permissões diferentes do seu role.
 *
 * Fluxo de uso:
 * 1. Se User.permissions_overridden = false: usa permissões do role
 * 2. Se User.permissions_overridden = true: usa permissões desta tabela
 *
 * Exemplo de uso:
 * - Usuário 'João' é 'tecnico', mas precisa de acesso extra a 'usuarios.editar'
 * - Marca permissions_overridden = true
 * - Adiciona entrada em UserPermission com permissão customizada
 *
 * Atributos principais:
 * - user_id: ID do usuário
 * - permission_id: ID da permissão customizada
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserPermission extends Model
{
    // Campos que podem ser preenchidos em massa
    protected $fillable = [
        'user_id',       // ID do usuário
        'permission_id', // ID da permissão customizada
    ];

    /**
     * Relacionamento: Uma permissão customizada pertence a um usuário
     * ENTRADA: Nenhuma
     * PROCESSAMENTO: Define relacionamento belongsTo com User
     * SAÍDA: Instância de User ou null
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relacionamento: Uma permissão customizada referencia uma permissão
     * ENTRADA: Nenhuma
     * PROCESSAMENTO: Define relacionamento belongsTo com Permission
     * SAÍDA: Instância de Permission ou null
     */
    public function permission()
    {
        return $this->belongsTo(Permission::class);
    }
}
