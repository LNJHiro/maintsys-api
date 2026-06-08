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

// Define o namespace deste model dentro da aplicação Laravel
namespace App\Models;

// Importa a classe base Model do Eloquent ORM
use Illuminate\Database\Eloquent\Model;

// Declara a classe UserPermission que herda de Model (Eloquent ORM do Laravel)
class UserPermission extends Model
{
    // Campos que podem ser preenchidos em massa (mass assignment)
    protected $fillable = [
        'user_id',       // ID do usuário que recebe a permissão customizada
        'permission_id', // ID da permissão que está sendo atribuída individualmente
    ];

    /**
     * Relacionamento: Uma permissão customizada pertence a um usuário
     * ENTRADA: Nenhuma
     * PROCESSAMENTO: Define relacionamento belongsTo com User
     * SAÍDA: Instância de User ou null
     */
    public function user()
    {
        // Um registro de UserPermission pertence a um único usuário do sistema
        return $this->belongsTo(User::class);
    } // fim do método user

    /**
     * Relacionamento: Uma permissão customizada referencia uma permissão
     * ENTRADA: Nenhuma
     * PROCESSAMENTO: Define relacionamento belongsTo com Permission
     * SAÍDA: Instância de Permission ou null
     */
    public function permission()
    {
        // Um registro de UserPermission aponta para uma única permissão do sistema
        return $this->belongsTo(Permission::class);
    } // fim do método permission
} // fim da classe UserPermission
