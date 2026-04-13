<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;

class TimeClosingStatusSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['name' => 'Aberto', 'code' => 'open'],
            ['name' => 'Processando', 'code' => 'processing'],
            ['name' => 'Conferido', 'code' => 'reviewed'],
            ['name' => 'Aprovado', 'code' => 'approved'],
            ['name' => 'Fechado', 'code' => 'closed'],
            ['name' => 'Integrado à Folha', 'code' => 'integrated_to_payroll'],
        ];

        Cache::forever('seed_reference_time_closing_statuses', $items);
    }
}