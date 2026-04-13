<?php

namespace Database\Seeders;

use App\Models\LaborUnion;
use Illuminate\Database\Seeder;

class LaborUnionSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['name' => 'Sindicato da Construção Civil', 'code' => 'SIND-CONST'],
            ['name' => 'Sindicato dos Motoristas', 'code' => 'SIND-MOTOR'],
            ['name' => 'Sindicato dos Operadores de Máquinas', 'code' => 'SIND-OPER'],
            ['name' => 'Sindicato dos Trabalhadores Rurais', 'code' => 'SIND-RURAL'],
            ['name' => 'Sindicato Administrativo', 'code' => 'SIND-ADM'],
        ];

        foreach ($items as $item) {
            LaborUnion::updateOrCreate(
                ['code' => $item['code']],
                [
                    'name' => $item['name'],
                    'code' => $item['code'],
                    'document' => null,
                    'phone' => null,
                    'email' => null,
                    'is_active' => true,
                ]
            );
        }
    }
}