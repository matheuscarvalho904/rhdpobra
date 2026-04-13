<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;

class HourBankMovementTypeSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['name' => 'Crédito', 'code' => 'credit'],
            ['name' => 'Débito', 'code' => 'debit'],
            ['name' => 'Ajuste', 'code' => 'adjustment'],
            ['name' => 'Expiração', 'code' => 'expiration'],
        ];

        Cache::forever('seed_reference_hour_bank_movement_types', $items);
    }
}