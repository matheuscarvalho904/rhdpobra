<?php

namespace Database\Seeders;

use App\Models\Holiday;
use App\Models\HolidayType;
use Illuminate\Database\Seeder;

class HolidaySeeder extends Seeder
{
    public function run(): void
    {
        $nacional = HolidayType::where('code', 'NACIONAL')->first();
        $estadual = HolidayType::where('code', 'ESTADUAL')->first();
        $municipal = HolidayType::where('code', 'MUNICIPAL')->first();

        $items = [
            ['holiday_type_id' => $nacional?->id, 'name' => 'Confraternização Universal', 'holiday_date' => '2026-01-01', 'state' => null, 'city' => null],
            ['holiday_type_id' => $nacional?->id, 'name' => 'Tiradentes', 'holiday_date' => '2026-04-21', 'state' => null, 'city' => null],
            ['holiday_type_id' => $nacional?->id, 'name' => 'Dia do Trabalho', 'holiday_date' => '2026-05-01', 'state' => null, 'city' => null],
            ['holiday_type_id' => $nacional?->id, 'name' => 'Independência do Brasil', 'holiday_date' => '2026-09-07', 'state' => null, 'city' => null],
            ['holiday_type_id' => $nacional?->id, 'name' => 'Nossa Senhora Aparecida', 'holiday_date' => '2026-10-12', 'state' => null, 'city' => null],
            ['holiday_type_id' => $nacional?->id, 'name' => 'Finados', 'holiday_date' => '2026-11-02', 'state' => null, 'city' => null],
            ['holiday_type_id' => $nacional?->id, 'name' => 'Proclamação da República', 'holiday_date' => '2026-11-15', 'state' => null, 'city' => null],
            ['holiday_type_id' => $nacional?->id, 'name' => 'Natal', 'holiday_date' => '2026-12-25', 'state' => null, 'city' => null],

            ['holiday_type_id' => $estadual?->id, 'name' => 'Consciência Negra - MT', 'holiday_date' => '2026-11-20', 'state' => 'MT', 'city' => null],

            ['holiday_type_id' => $municipal?->id, 'name' => 'Aniversário de Aripuanã', 'holiday_date' => '2026-12-11', 'state' => 'MT', 'city' => 'Aripuanã'],
            ['holiday_type_id' => $municipal?->id, 'name' => 'Aniversário de Juína', 'holiday_date' => '2026-05-09', 'state' => 'MT', 'city' => 'Juína'],
        ];

        foreach ($items as $item) {
            if (! $item['holiday_type_id']) {
                continue;
            }

            Holiday::updateOrCreate(
                [
                    'name' => $item['name'],
                    'holiday_date' => $item['holiday_date'],
                ],
                [
                    'holiday_type_id' => $item['holiday_type_id'],
                    'name' => $item['name'],
                    'holiday_date' => $item['holiday_date'],
                    'state' => $item['state'],
                    'city' => $item['city'],
                    'is_active' => true,
                ]
            );
        }
    }
}