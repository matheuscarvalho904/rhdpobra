<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Company;
use App\Models\Work;
use Illuminate\Database\Seeder;

class WorkSeeder extends Seeder
{
    public function run(): void
    {
        $dard = Company::where('code', 'DARD')->first();
        $ampla = Company::where('code', 'AMPLA')->first();

        $dardMatriz = Branch::where('company_id', $dard?->id)->where('code', 'MATRIZ')->first();
        $dardOperacional = Branch::where('company_id', $dard?->id)->where('code', 'OPERACIONAL')->first();
        $amplaMatriz = Branch::where('company_id', $ampla?->id)->where('code', 'MATRIZ')->first();

        $works = [
            [
                'company_id' => $dard?->id,
                'branch_id' => $dardMatriz?->id,
                'name' => 'Obra Escola',
                'code' => 'ESCOLA',
                'client_name' => 'Prefeitura Municipal',
                'city' => 'Aripuanã',
                'state' => 'MT',
                'start_date' => '2026-01-01',
                'expected_end_date' => '2026-12-31',
                'is_active' => true,
            ],
            [
                'company_id' => $dard?->id,
                'branch_id' => $dardOperacional?->id,
                'name' => 'Loteamento Reserva dos Ipês',
                'code' => 'RESERVA-IPES',
                'client_name' => 'Cliente Privado',
                'city' => 'Juína',
                'state' => 'MT',
                'start_date' => '2026-02-01',
                'expected_end_date' => '2026-10-31',
                'is_active' => true,
            ],
            [
                'company_id' => $ampla?->id,
                'branch_id' => $amplaMatriz?->id,
                'name' => 'Pré-Moldados Unidade 01',
                'code' => 'PREMOLD-01',
                'client_name' => 'Interno',
                'city' => 'Juína',
                'state' => 'MT',
                'start_date' => '2026-01-15',
                'expected_end_date' => null,
                'is_active' => true,
            ],
        ];

        foreach ($works as $work) {
            if (! $work['company_id']) {
                continue;
            }

            Work::updateOrCreate(
                [
                    'company_id' => $work['company_id'],
                    'code' => $work['code'],
                ],
                $work
            );
        }
    }
}