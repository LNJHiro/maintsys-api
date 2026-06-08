<?php

/**
 * MODEL: User (Usuário do Sistema)
 *
 * Responsável por representar um usuário no sistema MaintSys.
 * Herda de Authenticatable para gerenciar autenticação.
 *
 * Atributos principais:
 * - name: Nome do usuário
 * - email: Email para login e notificações
 * - password: Senha (armazenada com hash)
 * - role: Papel do usuário (admin_master, admin, supervisor, tecnico)
 * - permissions_overridden: Flag indicando se permissões customizadas foram aplicadas
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    // Cache em memória para as permissões, evita múltiplas consultas ao banco
    protected ?array $permissionNamesCache = null;

    // Campos que podem ser preenchidos em massa (mass assignment)
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'permissions_overridden',
    ];

    // Campos que não devem ser retornados em respostas (segurança)
    protected $hidden = [
        'password',
        'remember_token',
    ];

    // Conversão automática de tipos de dados
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',   // Data de verificação de email
            'password' => 'hashed',              // Hash automático de senha
            'permissions_overridden' => 'boolean', // Converte para booleano
        ];
    }

    /**
     * FUNÇÃO: isAdmin()
     * ENTRADA: Nenhuma (usa $this->role do usuário)
     * PROCESSAMENTO: Verifica se o role do usuário é admin_master ou admin
     * SAÍDA: true se é admin, false caso contrário
     */
    public function isAdmin(): bool
    {
        return in_array($this->role, ['admin_master', 'admin']);
    }

    /**
     * FUNÇÃO: isMaster()
     * ENTRADA: Nenhuma (usa $this->role do usuário)
     * PROCESSAMENTO: Verifica se o role é exatamente admin_master
     * SAÍDA: true se é master, false caso contrário
     */
    public function isMaster(): bool
    {
        return $this->role === 'admin_master';
    }

    /**
     * FUNÇÃO: canManageUsers()
     * ENTRADA: Nenhuma (usa permissões do usuário)
     * PROCESSAMENTO: Verifica se o usuário tem permissão para visualizar ou gerenciar usuários
     * SAÍDA: true se pode gerenciar, false caso contrário
     */
    public function canManageUsers(): bool
    {
        return $this->hasPermission('usuarios.visualizar')
            || $this->hasPermission('acesso.gerenciar');
    }

    /**
     * Relacionamento: Um usuário pode ter um técnico
     * ENTRADA: Nenhuma
     * PROCESSAMENTO: Define relacionamento hasOne com modelo Tecnico
     * SAÍDA: Instância de Tecnico ou null
     */
    public function tecnico()
    {
        return $this->hasOne(Tecnico::class);
    }

    /**
     * Relacionamento: Um usuário pode ter múltiplas permissões customizadas
     * ENTRADA: Nenhuma
     * PROCESSAMENTO: Define relacionamento hasMany com UserPermission
     * SAÍDA: Coleção de permissões do usuário
     */
    public function userPermissions()
    {
        return $this->hasMany(UserPermission::class);
    }

    /**
     * FUNÇÃO: hasPermission($perm)
     * ENTRADA: $perm (string) - Nome da permissão a verificar, ex: 'usuarios.visualizar'
     * PROCESSAMENTO:
     *   1. Se usuário é master, retorna true (acesso irrestrito)
     *   2. Caso contrário, verifica se permissão está na lista de permissões do usuário
     * SAÍDA: true se tem permissão, false caso contrário
     */
    public function hasPermission(string $perm): bool
    {
        // Master tem acesso a tudo
        if ($this->isMaster()) {
            return true;
        }

        // Verifica se a permissão está na lista de nomes de permissões
        return in_array($perm, $this->permissionNames(), true);
    }

    /**
     * FUNÇÃO: permissionNames()
     * ENTRADA: Nenhuma
     * PROCESSAMENTO:
     *   1. Verifica cache em memória para evitar múltiplas consultas
     *   2. Se permissions_overridden = true:
     *      - Busca permissões específicas do usuário na tabela user_permissions
     *   3. Se permissions_overridden = false:
     *      - Busca permissões do role do usuário na tabela role_permissions
     *   4. Remove nulos, duplicatas e retorna array único
     *   5. Armazena no cache para próximas consultas
     * SAÍDA: Array com nomes das permissões do usuário
     */
    public function permissionNames(): array
    {
        // Retorna do cache se já foi consultado (performance)
        if ($this->permissionNamesCache !== null) {
            return $this->permissionNamesCache;
        }

        // Se usuário tem permissões customizadas, busca especificamente dele
        if ($this->permissions_overridden) {
            $names = $this->userPermissions()
                ->with('permission')
                ->get()
                ->pluck('permission.name');
        } else {
            // Caso contrário, busca permissões do seu role
            $names = RolePermission::with('permission')
                ->where('role', $this->role)
                ->get()
                ->pluck('permission.name');
        }

        // Limpa o resultado: remove nulos, duplicatas, reordena índices
        $names = $names
            ->filter()           // Remove valores nulos
            ->unique()           // Remove duplicatas
            ->values()           // Reordena índices (0, 1, 2...)
            ->all();             // Converte para array

        // Armazena no cache e retorna
        return $this->permissionNamesCache = $names;
    }

    /**
     * FUNÇÃO: clearPermissionCache()
     * ENTRADA: Nenhuma
     * PROCESSAMENTO: Limpa o cache de permissões armazenado em memória
     * SAÍDA: Nenhuma (void)
     * OBSERVAÇÃO: Use quando permissões forem alteradas para forçar recarga
     */
    public function clearPermissionCache(): void
    {
        $this->permissionNamesCache = null;
    }
}
