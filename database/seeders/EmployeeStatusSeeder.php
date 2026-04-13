<?php

namespace Database\Seeders;

use App\Models\EmployeeStatus;
use Illuminate\Database\Seeder;

class EmployeeStatusSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['name' => 'Ativo', 'code' => 'active'],
            ['name' => 'Inativo', 'code' => 'inactive'],
            ['name' => 'Desligado', 'code' => 'terminated'],
            ['name' => 'Afastado', 'code' => 'on_leave'],
            ['name' => 'Em Experiência', 'code' => 'probation'],
            ['name' => 'Férias', 'code' => 'vacation'],
        ];

        foreach ($items as $item) {
            EmployeeStatus::updateOrCreate(
                ['code' => $item['code']],
                [
                    'name' => $item['name'],
                    'code' => $item['code'],
                    'description' => null,
                    'is_active' => true,
                ]
            );
        }
    }
}