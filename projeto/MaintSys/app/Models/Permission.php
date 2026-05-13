<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    protected $fillable = ['name', 'descricao', 'modulo'];

    public function roles()
    {
        return $this->hasMany(RolePermission::class);
    }
}
