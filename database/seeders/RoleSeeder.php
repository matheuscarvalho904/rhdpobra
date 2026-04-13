<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            'Administrador',
            'RH',
            'DP',
            'Financeiro',
            'Encarregado',
            'Gestor de Obra',
            'Consulta',
        ];

        foreach ($roles as $role) {
            Role::findOrCreate($role, 'web');
        }
    }
}