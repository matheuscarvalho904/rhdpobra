<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Epi;
use App\Models\Company;

class EpiSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::query()->first();

        if (! $company) {
            $this->command->warn('Nenhuma empresa encontrada para vincular EPIs.');
            return;
        }

        $epis = [

            // 🔰 PROTEÇÃO DA CABEÇA
            [
                'name' => 'Capacete de Segurança',
                'code' => 'EPI001',
                'ca_number' => '12345',
                'validity_days' => 365,
                'requires_return' => true,
            ],

            // 👁 PROTEÇÃO VISUAL
            [
                'name' => 'Óculos de Proteção Incolor',
                'code' => 'EPI002',
                'ca_number' => '23456',
                'validity_days' => 180,
                'requires_return' => false,
            ],

            // 👂 PROTEÇÃO AUDITIVA
            [
                'name' => 'Protetor Auricular Tipo Plug',
                'code' => 'EPI003',
                'ca_number' => '34567',
                'validity_days' => 90,
                'requires_return' => false,
            ],

            // 😷 PROTEÇÃO RESPIRATÓRIA
            [
                'name' => 'Máscara PFF2',
                'code' => 'EPI004',
                'ca_number' => '45678',
                'validity_days' => 30,
                'requires_return' => false,
            ],

            // 🧤 PROTEÇÃO DAS MÃOS
            [
                'name' => 'Luva de Raspa',
                'code' => 'EPI005',
                'ca_number' => '56789',
                'validity_days' => 120,
                'requires_return' => false,
            ],

            [
                'name' => 'Luva Nitrílica',
                'code' => 'EPI006',
                'ca_number' => '67890',
                'validity_days' => 60,
                'requires_return' => false,
            ],

            // 👢 PROTEÇÃO DOS PÉS
            [
                'name' => 'Botina de Segurança com Bico de Aço',
                'code' => 'EPI007',
                'ca_number' => '78901',
                'validity_days' => 180,
                'requires_return' => true,
            ],

            [
                'name' => 'Bota PVC',
                'code' => 'EPI008',
                'ca_number' => '89012',
                'validity_days' => 180,
                'requires_return' => true,
            ],

            // 🦺 PROTEÇÃO DO CORPO
            [
                'name' => 'Colete Refletivo',
                'code' => 'EPI009',
                'ca_number' => '90123',
                'validity_days' => 180,
                'requires_return' => true,
            ],

            [
                'name' => 'Cinto de Segurança Tipo Paraquedista',
                'code' => 'EPI010',
                'ca_number' => '11223',
                'validity_days' => 365,
                'requires_return' => true,
            ],

        ];

        foreach ($epis as $epi) {
            Epi::updateOrCreate(
                ['code' => $epi['code']],
                [
                    'company_id' => $company->id,
                    'name' => $epi['name'],
                    'ca_number' => $epi['ca_number'],
                    'validity_days' => $epi['validity_days'],
                    'requires_return' => $epi['requires_return'],
                    'is_active' => true,
                    'notes' => null,
                ]
            );
        }

        $this->command->info('EPIs cadastrados com sucesso.');
    }
}