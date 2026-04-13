<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Company;
use Illuminate\Database\Seeder;

class BranchSeeder extends Seeder
{
    public function run(): void
    {
        $dard = Company::where('code', 'DARD')->first();
        $ampla = Company::where('code', 'AMPLA')->first();
        $nutri = Company::where('code', 'NUTRIOBRA')->first();

        if (! $dard && ! $ampla && ! $nutri) {
            return;
        }

        $branches = [];

        if ($dard) {
            $branches = array_merge($branches, [
                [
                    'company_id' => $dard->id,
                    'name' => 'Matriz Dardanellos',
                    'code' => 'DARD_MATRIZ',
                    'document' => '00000000000191',
                    'phone' => '(66) 00000-1000',
                    'email' => 'matriz@dardanellos.com.br',
                    'city' => 'Aripuanã',
                    'state' => 'MT',
                    'is_active' => true,
                ],
                [
                    'company_id' => $dard->id,
                    'name' => 'Filial Operacional Juína',
                    'code' => 'DARD_JUINA',
                    'document' => '00000000000192',
                    'phone' => '(66) 00000-1001',
                    'email' => 'juina@dardanellos.com.br',
                    'city' => 'Juína',
                    'state' => 'MT',
                    'is_active' => true,
                ],
                [
                    'company_id' => $dard->id,
                    'name' => 'Filial Cuiabá',
                    'code' => 'DARD_CBA',
                    'document' => '00000000000193',
                    'phone' => '(65) 00000-1002',
                    'email' => 'cuiaba@dardanellos.com.br',
                    'city' => 'Cuiabá',
                    'state' => 'MT',
                    'is_active' => true,
                ],
                [
                    'company_id' => $dard->id,
                    'name' => 'Base de Apoio Obras',
                    'code' => 'DARD_APOIO',
                    'document' => '00000000000194',
                    'phone' => '(66) 00000-1003',
                    'email' => 'apoio@dardanellos.com.br',
                    'city' => 'Aripuanã',
                    'state' => 'MT',
                    'is_active' => true,
                ],
            ]);
        }

        if ($ampla) {
            $branches = array_merge($branches, [
                [
                    'company_id' => $ampla->id,
                    'name' => 'Matriz Ampla',
                    'code' => 'AMPLA_MATRIZ',
                    'document' => '00000000000272',
                    'phone' => '(66) 00000-2000',
                    'email' => 'matriz@amplaconstrutora.com.br',
                    'city' => 'Juína',
                    'state' => 'MT',
                    'is_active' => true,
                ],
                [
                    'company_id' => $ampla->id,
                    'name' => 'Filial Pré-Moldados',
                    'code' => 'AMPLA_PRE',
                    'document' => '00000000000273',
                    'phone' => '(66) 00000-2001',
                    'email' => 'premoldados@amplaconstrutora.com.br',
                    'city' => 'Juína',
                    'state' => 'MT',
                    'is_active' => true,
                ],
                [
                    'company_id' => $ampla->id,
                    'name' => 'Filial Obras Urbanas',
                    'code' => 'AMPLA_URB',
                    'document' => '00000000000274',
                    'phone' => '(65) 00000-2002',
                    'email' => 'urbanas@amplaconstrutora.com.br',
                    'city' => 'Cuiabá',
                    'state' => 'MT',
                    'is_active' => true,
                ],
            ]);
        }

        if ($nutri) {
            $branches = array_merge($branches, [
                [
                    'company_id' => $nutri->id,
                    'name' => 'Matriz NutriObra',
                    'code' => 'NUTRI_MATRIZ',
                    'document' => '00000000000355',
                    'phone' => '(66) 00000-3000',
                    'email' => 'matriz@nutriobra.com.br',
                    'city' => 'Aripuanã',
                    'state' => 'MT',
                    'is_active' => true,
                ],
                [
                    'company_id' => $nutri->id,
                    'name' => 'Unidade Refeitório Obras',
                    'code' => 'NUTRI_REF',
                    'document' => '00000000000356',
                    'phone' => '(66) 00000-3001',
                    'email' => 'refeitorio@nutriobra.com.br',
                    'city' => 'Juína',
                    'state' => 'MT',
                    'is_active' => true,
                ],
            ]);
        }

        foreach ($branches as $branch) {
            Branch::updateOrCreate(
                [
                    'company_id' => $branch['company_id'],
                    'code' => $branch['code'],
                ],
                [
                    'name' => $branch['name'],
                    'code' => $branch['code'],
                    'document' => $branch['document'],
                    'phone' => $branch['phone'],
                    'email' => $branch['email'],
                    'city' => $branch['city'],
                    'state' => $branch['state'],
                    'is_active' => $branch['is_active'],
                ]
            );
        }
    }
}