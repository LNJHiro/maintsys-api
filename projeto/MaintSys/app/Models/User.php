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

// Define o namespace deste model dentro da aplicação Laravel
namespace App\Models;

// Importa a trait HasFactory para geração de factories de teste
use Illuminate\Database\Eloquent\Factories\HasFactory;
// Importa a classe base Authenticatable que fornece autenticação ao model User
use Illuminate\Foundation\Auth\User as Authenticatable;
// Importa a trait Notifiable para envio de notificações ao usuário
use Illuminate\Notifications\Notifiable;

// Declara a classe User que herda de Authenticatable (autenticação do Laravel)
class User extends Authenticatable
{
    // Inclui as traits HasFactory (factories de teste) e Notifiable (notificações)
    use HasFactory, Notifiable;

    // Cache em memória para as permissões, evita múltiplas consultas ao banco
    protected ?array $permissionNamesCache = null;

    // Campos que podem ser preenchidos em massa (mass assignment)
    protected $fillable = [
        'name',                  // Nome completo do usuário
        'email',                 // Email de login
        'password',              // Senha (será armazenada como hash)
        'role',                  // Papel do usuário no sistema
        'permissions_overridden', // Se o usuário tem permissões customizadas
    ];

    // Campos que não devem ser retornados em respostas (segurança)
    protected $hidden = [
        'password',       // Oculta a senha nas respostas JSON
        'remember_token', // Oculta o token de "lembrar sessão"
    ];

    /**
     * FUNÇÃO: casts()
     * ENTRADA: Nenhuma
     * PROCESSAMENTO: Define conversão automática de tipos de dados dos atributos
     * SAÍDA: Array com as conversões de tipo para cada atributo
     */
    protected function casts(): array
    {
        // Retorna o mapeamento de atributos para seus tipos convertidos
        return [
            'email_verified_at'      => 'datetime', // Data de verificação de email como Carbon
            'password'               => 'hashed',   // Hash automático de senha ao salvar
            'permissions_overridden' => 'boolean',  // Converte 0/1 para true/false
        ];
    } // fim do método casts

    /**
     * FUNÇÃO: isAdmin()
     * ENTRADA: Nenhuma (usa $this->role do usuário)
     * PROCESSAMENTO: Verifica se o role do usuário é admin_master ou admin
     * SAÍDA: true se é admin, false caso contrário
     */
    public function isAdmin(): bool
    {
        // Retorna true se o role do usuário for admin_master ou admin
        return in_array($this->role, ['admin_master', 'admin']);
    } // fim do método isAdmin

    /**
     * FUNÇÃO: isMaster()
     * ENTRADA: Nenhuma (usa $this->role do usuário)
     * PROCESSAMENTO: Verifica se o role é exatamente admin_master
     * SAÍDA: true se é master, false caso contrário
     */
    public function isMaster(): bool
    {
        // Retorna true somente se o role for exatamente admin_master
        return $this->role === 'admin_master';
    } // fim do método isMaster

    /**
     * FUNÇÃO: canManageUsers()
     * ENTRADA: Nenhuma (usa permissões do usuário)
     * PROCESSAMENTO: Verifica se o usuário tem permissão para visualizar ou gerenciar usuários
     * SAÍDA: true se pode gerenciar, false caso contrário
     */
    public function canManageUsers(): bool
    {
        // Retorna true se o usuário tiver pelo menos uma das permissões necessárias
        return $this->hasPermission('usuarios.visualizar')  // Pode visualizar usuários
            || $this->hasPermission('acesso.gerenciar');    // Pode gerenciar acessos
    } // fim do método canManageUsers

    /**
     * Relacionamento: Um usuário pode ter um técnico
     * ENTRADA: Nenhuma
     * PROCESSAMENTO: Define relacionamento hasOne com modelo Tecnico
     * SAÍDA: Instância de Tecnico ou null
     */
    public function tecnico()
    {
        // Define que um usuário possui no máximo um técnico associado
        return $this->hasOne(Tecnico::class);
    } // fim do método tecnico

    /**
     * Relacionamento: Um usuário pode ter múltiplas permissões customizadas
     * ENTRADA: Nenhuma
     * PROCESSAMENTO: Define relacionamento hasMany com UserPermission
     * SAÍDA: Coleção de permissões do usuário
     */
    public function userPermissions()
    {
        // Define que um usuário pode ter vários registros de permissões individuais
        return $this->hasMany(UserPermission::class);
    } // fim do método userPermissions

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
        // Admin Master tem acesso irrestrito a tudo no sistema
        if ($this->isMaster()) {
            return true;
        } // fim do if de admin_master

        // Verifica se o nome da permissão está na lista de permissões efetivas do usuário
        return in_array($perm, $this->permissionNames(), true);
    } // fim do método hasPermission

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
        // Retorna do cache se já foi consultado anteriormente (evita N+1 queries)
        if ($this->permissionNamesCache !== null) {
            return $this->permissionNamesCache;
        } // fim do if de cache

        // Se o usuário tem permissões customizadas (individuais), busca da tabela user_permissions
        if ($this->permissions_overridden) {
            // Carrega as permissões individuais com seus relacionamentos e extrai os nomes
            $names = $this->userPermissions()
                ->with('permission')         // Carrega o relacionamento com Permission (eager loading)
                ->get()                      // Executa a consulta ao banco
                ->pluck('permission.name');  // Extrai apenas o campo 'name' de cada permissão
        } else {
            // Caso contrário, busca as permissões do role do usuário
            $names = RolePermission::with('permission')     // Carrega o relacionamento com Permission
                ->where('role', $this->role)                // Filtra pelo role do usuário
                ->get()                                     // Executa a consulta ao banco
                ->pluck('permission.name');                 // Extrai apenas o campo 'name'
        } // fim do if/else de permissões

        // Limpa o resultado removendo nulos, duplicatas e reordenando os índices
        $names = $names
            ->filter()  // Remove valores nulos ou vazios
            ->unique()  // Remove entradas duplicadas
            ->values()  // Reordena os índices (0, 1, 2...)
            ->all();    // Converte a coleção para array PHP

        // Armazena no cache em memória e retorna o resultado
        return $this->permissionNamesCache = $names;
    } // fim do método permissionNames

    /**
     * FUNÇÃO: clearPermissionCache()
     * ENTRADA: Nenhuma
     * PROCESSAMENTO: Limpa o cache de permissões armazenado em memória
     * SAÍDA: Nenhuma (void)
     * OBSERVAÇÃO: Use quando permissões forem alteradas para forçar recarga
     */
    public function clearPermissionCache(): void
    {
        // Reseta o cache para null, forçando nova consulta ao banco na próxima vez
        $this->permissionNamesCache = null;
    } // fim do método clearPermissionCache
} // fim da classe User
