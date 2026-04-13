<?php

namespace Database\Seeders;

use App\Models\CostCenter;
use Illuminate\Database\Seeder;

class CostCenterSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['name' => 'Administrativo Geral', 'code' => 'CC-ADM'],
            ['name' => 'RH', 'code' => 'CC-RH'],
            ['name' => 'DP', 'code' => 'CC-DP'],
            ['name' => 'Financeiro', 'code' => 'CC-FIN'],
            ['name' => 'Compras', 'code' => 'CC-COMP'],
            ['name' => 'Almoxarifado', 'code' => 'CC-ALMOX'],
            ['name' => 'Oficina', 'code' => 'CC-OFIC'],
            ['name' => 'Produção', 'code' => 'CC-PROD'],
            ['name' => 'Engenharia', 'code' => 'CC-ENG'],
            ['name' => 'Topografia', 'code' => 'CC-TOPO'],
            ['name' => 'Segurança do Trabalho', 'code' => 'CC-SESMT'],
            ['name' => 'Logística', 'code' => 'CC-LOG'],
            ['name' => 'Frota', 'code' => 'CC-FROTA'],
            ['name' => 'Obras Gerais', 'code' => 'CC-OBRAS'],
            ['name' => 'Equipamentos', 'code' => 'CC-EQP'],
            ['name' => 'Manutenção Pesada', 'code' => 'CC-MANP'],
        ];

        foreach ($items as $item) {
            CostCenter::updateOrCreate(
                ['code' => $item['code']],
                [
                    'company_id' => null,
                    'name' => $item['name'],
                    'code' => $item['code'],
                    'description' => null,
                    'is_active' => true,
                ]
            );
        }
    }
}