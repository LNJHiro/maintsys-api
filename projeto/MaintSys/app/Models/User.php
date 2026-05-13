<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
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
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isMaster(): bool
    {
        return $this->role === 'admin_master';
    }

    public function canManageUsers(): bool
    {
        return in_array($this->role, ['admin_master', 'admin']);
    }

    public function hasPermission(string $perm): bool
    {
        if ($this->isMaster()) {
            return true;
        }

        // Se o usuário tem permissões individuais configuradas,
        // elas prevalecem completamente sobre o role.
        $hasIndividualConfig = UserPermission::where('user_id', $this->id)->exists();

        if ($hasIndividualConfig) {
            return UserPermission::where('user_id', $this->id)
                ->whereHas('permission', fn($q) => $q->where('name', $perm))
                ->exists();
        }

        // Sem configuração individual — usa as permissões do role.
        return RolePermission::where('role', $this->role)
            ->whereHas('permission', fn($q) => $q->where('name', $perm))
            ->exists();
    }
}