<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::updateOrCreate(
            ['email' => 'admin@obrapeople.com.br'],
            [
                'name' => 'Administrador do Sistema',
                'password' => '12345678',
                'is_active' => true,
            ]
        );

        $user->syncRoles(['Administrador']);
    }
}