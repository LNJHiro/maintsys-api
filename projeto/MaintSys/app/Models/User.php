<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected ?array $permissionNamesCache = null;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'permissions_overridden',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'permissions_overridden' => 'boolean',
        ];
    }

    public function isAdmin(): bool
    {
        return in_array($this->role, ['admin_master', 'admin']);
    }

    public function isMaster(): bool
    {
        return $this->role === 'admin_master';
    }

    public function canManageUsers(): bool
    {
        return $this->hasPermission('usuarios.visualizar')
            || $this->hasPermission('acesso.gerenciar');
    }

    public function tecnico()
    {
        return $this->hasOne(Tecnico::class);
    }

    public function userPermissions()
    {
        return $this->hasMany(UserPermission::class);
    }

    public function hasPermission(string $perm): bool
    {
        if ($this->isMaster()) {
            return true;
        }

        return in_array($perm, $this->permissionNames(), true);
    }

    public function permissionNames(): array
    {
        if ($this->permissionNamesCache !== null) {
            return $this->permissionNamesCache;
        }

        if ($this->permissions_overridden) {
            $names = $this->userPermissions()
                ->with('permission')
                ->get()
                ->pluck('permission.name');
        } else {
            $names = RolePermission::with('permission')
                ->where('role', $this->role)
                ->get()
                ->pluck('permission.name');
        }

        $names = $names
            ->filter()
            ->unique()
            ->values()
            ->all();

        return $this->permissionNamesCache = $names;
    }

    public function clearPermissionCache(): void
    {
        $this->permissionNamesCache = null;
    }
}
