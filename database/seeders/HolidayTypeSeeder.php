<?php

namespace Database\Seeders;

use App\Models\HolidayType;
use Illuminate\Database\Seeder;

class HolidayTypeSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['name' => 'Nacional', 'code' => 'NACIONAL'],
            ['name' => 'Estadual', 'code' => 'ESTADUAL'],
            ['name' => 'Municipal', 'code' => 'MUNICIPAL'],
            ['name' => 'Ponto Facultativo', 'code' => 'PONTO-FACULTATIVO'],
            ['name' => 'Interno da Empresa', 'code' => 'INTERNO'],
        ];

        foreach ($items as $item) {
            HolidayType::updateOrCreate(
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