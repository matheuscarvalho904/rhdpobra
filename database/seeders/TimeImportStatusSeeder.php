<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;

class TimeImportStatusSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['name' => 'Pendente', 'code' => 'pending'],
            ['name' => 'Processando', 'code' => 'processing'],
            ['name' => 'Concluído', 'code' => 'completed'],
            ['name' => 'Concluído com Erros', 'code' => 'completed_with_errors'],
            ['name' => 'Falhou', 'code' => 'failed'],
        ];

        Cache::forever('seed_reference_time_import_statuses', $items);
    }
}