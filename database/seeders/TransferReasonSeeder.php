<?php

namespace Database\Seeders;

use App\Models\TransferReason;
use Illuminate\Database\Seeder;

class TransferReasonSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['name' => 'Mudança de Obra', 'code' => 'work_change'],
            ['name' => 'Mudança de Setor', 'code' => 'department_change'],
            ['name' => 'Promoção', 'code' => 'promotion'],
            ['name' => 'Reestruturação', 'code' => 'restructuring'],
            ['name' => 'Apoio Operacional', 'code' => 'operational_support'],
            ['name' => 'Remanejamento', 'code' => 'reallocation'],
            ['name' => 'Solicitação Administrativa', 'code' => 'administrative_request'],
        ];

        foreach ($items as $item) {
            TransferReason::updateOrCreate(
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