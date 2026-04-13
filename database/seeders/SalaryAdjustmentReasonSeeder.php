<?php

namespace Database\Seeders;

use App\Models\SalaryAdjustmentReason;
use Illuminate\Database\Seeder;

class SalaryAdjustmentReasonSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['name' => 'Admissão', 'code' => 'admission'],
            ['name' => 'Dissídio', 'code' => 'dissidio'],
            ['name' => 'Promoção', 'code' => 'promotion'],
            ['name' => 'Reenquadramento', 'code' => 'reclassification'],
            ['name' => 'Ajuste Interno', 'code' => 'internal_adjustment'],
            ['name' => 'Mudança de Função', 'code' => 'role_change'],
            ['name' => 'Acordo Coletivo', 'code' => 'collective_agreement'],
        ];

        foreach ($items as $item) {
            SalaryAdjustmentReason::updateOrCreate(
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