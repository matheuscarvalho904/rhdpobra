<?php

namespace Database\Seeders;

use App\Models\SalaryType;
use Illuminate\Database\Seeder;

class SalaryTypeSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['name' => 'Mensalista', 'code' => 'monthly'],
            ['name' => 'Horista', 'code' => 'hourly'],
            ['name' => 'Diarista', 'code' => 'daily'],
        ];

        foreach ($items as $item) {
            SalaryType::updateOrCreate(
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