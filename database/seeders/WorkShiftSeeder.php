<?php

namespace Database\Seeders;

use App\Models\WorkShift;
use Illuminate\Database\Seeder;

class WorkShiftSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            [
                'name' => 'Administrativo 44h',
                'code' => 'ADM-44H',
                'weekly_workload' => 44,
                'monthly_workload' => 220,
                'description' => 'Jornada administrativa padrão de segunda a sexta e sábado parcial.',
            ],
            [
                'name' => 'Obra 44h',
                'code' => 'OBRA-44H',
                'weekly_workload' => 44,
                'monthly_workload' => 220,
                'description' => 'Jornada operacional padrão de obra.',
            ],
            [
                'name' => '12x36 Diurno',
                'code' => '12X36-DIA',
                'weekly_workload' => 36,
                'monthly_workload' => 180,
                'description' => 'Escala 12x36 no período diurno.',
            ],
            [
                'name' => '12x36 Noturno',
                'code' => '12X36-NOITE',
                'weekly_workload' => 36,
                'monthly_workload' => 180,
                'description' => 'Escala 12x36 no período noturno.',
            ],
            [
                'name' => '6x1 Operacional',
                'code' => '6X1-OPER',
                'weekly_workload' => 44,
                'monthly_workload' => 220,
                'description' => 'Escala 6x1 para operação.',
            ],
            [
                'name' => '5x2 Administrativo',
                'code' => '5X2-ADM',
                'weekly_workload' => 40,
                'monthly_workload' => 200,
                'description' => 'Escala 5x2 administrativa.',
            ],
            [
                'name' => 'Turno Noturno',
                'code' => 'NOTURNO',
                'weekly_workload' => 44,
                'monthly_workload' => 220,
                'description' => 'Jornada com predominância noturna.',
            ],
        ];

        foreach ($items as $item) {
            WorkShift::updateOrCreate(
                ['code' => $item['code']],
                [
                    'name' => $item['name'],
                    'code' => $item['code'],
                    'weekly_workload' => $item['weekly_workload'],
                    'monthly_workload' => $item['monthly_workload'],
                    'description' => $item['description'],
                    'is_active' => true,
                ]
            );
        }
    }
}