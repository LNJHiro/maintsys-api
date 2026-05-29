<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;
use App\Models\RolePermission;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            // Máquinas
            ['name' => 'maquinas.visualizar', 'descricao' => 'Ver máquinas', 'modulo' => 'maquinas'],
            ['name' => 'maquinas.criar', 'descricao' => 'Criar máquina', 'modulo' => 'maquinas'],
            ['name' => 'maquinas.editar', 'descricao' => 'Editar máquina', 'modulo' => 'maquinas'],
            ['name' => 'maquinas.deletar', 'descricao' => 'Deletar máquina', 'modulo' => 'maquinas'],

            // Técnicos
            ['name' => 'tecnicos.visualizar', 'descricao' => 'Ver técnicos', 'modulo' => 'tecnicos'],
            ['name' => 'tecnicos.criar', 'descricao' => 'Criar técnico', 'modulo' => 'tecnicos'],
            ['name' => 'tecnicos.editar', 'descricao' => 'Editar técnico', 'modulo' => 'tecnicos'],
            ['name' => 'tecnicos.deletar', 'descricao' => 'Deletar técnico', 'modulo' => 'tecnicos'],

            // Ordens de Serviço
            ['name' => 'ordens.visualizar', 'descricao' => 'Ver ordens', 'modulo' => 'ordens'],
            ['name' => 'ordens.criar', 'descricao' => 'Criar ordem', 'modulo' => 'ordens'],
            ['name' => 'ordens.editar', 'descricao' => 'Editar ordem', 'modulo' => 'ordens'],
            ['name' => 'ordens.deletar', 'descricao' => 'Deletar ordem', 'modulo' => 'ordens'],

            // Histórico
            ['name' => 'historico.visualizar', 'descricao' => 'Ver histórico', 'modulo' => 'historico'],
            ['name' => 'historico.criar', 'descricao' => 'Registrar manutenção', 'modulo' => 'historico'],
            ['name' => 'historico.deletar', 'descricao' => 'Deletar registro', 'modulo' => 'historico'],

            // Dashboard
            ['name' => 'dashboard.maquinas', 'descricao' => 'Ver cards de máquinas', 'modulo' => 'dashboard'],
            ['name' => 'dashboard.tecnicos', 'descricao' => 'Ver card de técnicos', 'modulo' => 'dashboard'],
            ['name' => 'dashboard.ordens', 'descricao' => 'Ver cards e tabela de ordens', 'modulo' => 'dashboard'],
            ['name' => 'dashboard.alertas', 'descricao' => 'Ver alertas de parada crítica', 'modulo' => 'dashboard'],
            ['name' => 'dashboard.historico', 'descricao' => 'Ver últimas manutenções', 'modulo' => 'dashboard'],
            // Usuarios
            ['name' => 'usuarios.visualizar', 'descricao' => 'Ver usuarios', 'modulo' => 'usuarios'],
            ['name' => 'usuarios.criar', 'descricao' => 'Criar usuario', 'modulo' => 'usuarios'],
            ['name' => 'usuarios.editar', 'descricao' => 'Editar usuario', 'modulo' => 'usuarios'],
            ['name' => 'usuarios.deletar', 'descricao' => 'Deletar usuario', 'modulo' => 'usuarios'],
            ['name' => 'usuarios.permissoes', 'descricao' => 'Ver permissoes de usuarios', 'modulo' => 'usuarios'],

            // Acesso
            ['name' => 'acesso.gerenciar', 'descricao' => 'Gerenciar permissoes', 'modulo' => 'acesso'],
        ];

        foreach ($permissions as $perm) {
            Permission::updateOrCreate(['name' => $perm['name']], $perm);
        }

        // Configurar permissões padrão do role 'admin'
        $adminPerms = Permission::all()->pluck('id')->toArray();
        foreach ($adminPerms as $permId) {
            RolePermission::firstOrCreate(['role' => 'admin', 'permission_id' => $permId]);
        }

        // Configurar permissões padrão do role 'usuario'
        $usuarioPerms = Permission::whereIn('name', [
                'maquinas.visualizar',
                'tecnicos.visualizar',
                'ordens.visualizar',
                'ordens.criar',
                'ordens.editar',
                'historico.visualizar',
                'historico.criar',
                'dashboard.maquinas',
                'dashboard.tecnicos',
                'dashboard.ordens',
                'dashboard.alertas',
                'dashboard.historico',
            ])
            ->pluck('id')->toArray();
        foreach ($usuarioPerms as $permId) {
            RolePermission::firstOrCreate(['role' => 'usuario', 'permission_id' => $permId]);
        }
    }
}
