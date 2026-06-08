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

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    // Campos que podem ser preenchidos em massa
    protected $fillable = [
        'name',      // Nome único da permissão (ex: 'usuarios.visualizar')
        'descricao', // Descrição legível em português
        'modulo',    // Módulo do sistema (organização)
    ];

    /**
     * Relacionamento: Uma permissão pode estar associada a múltiplos roles
     * ENTRADA: Nenhuma
     * PROCESSAMENTO: Define relacionamento hasMany com RolePermission
     * SAÍDA: Coleção de RolePermission associadas a esta permissão
     */
    public function roles()
    {
        return $this->hasMany(RolePermission::class);
    }
}
