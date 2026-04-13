<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['name' => 'Administrativo', 'code' => 'ADM'],
            ['name' => 'Recursos Humanos', 'code' => 'RH'],
            ['name' => 'Departamento Pessoal', 'code' => 'DP'],
            ['name' => 'Financeiro', 'code' => 'FIN'],
            ['name' => 'Compras', 'code' => 'COMPRAS'],
            ['name' => 'Almoxarifado', 'code' => 'ALMOX'],
            ['name' => 'Oficina', 'code' => 'OFICINA'],
            ['name' => 'Produção', 'code' => 'PROD'],
            ['name' => 'Engenharia', 'code' => 'ENG'],
            ['name' => 'Topografia', 'code' => 'TOPO'],
            ['name' => 'Segurança do Trabalho', 'code' => 'SESMT'],
            ['name' => 'Qualidade', 'code' => 'QUAL'],
            ['name' => 'Logística', 'code' => 'LOG'],
            ['name' => 'Operacional de Obra', 'code' => 'OPERACAO'],
            ['name' => 'Manutenção', 'code' => 'MANUT'],
            ['name' => 'Suprimentos', 'code' => 'SUPR'],
            ['name' => 'Frota', 'code' => 'FROTA'],
            ['name' => 'Planejamento', 'code' => 'PLAN'],
        ];

        foreach ($items as $item) {
            Department::updateOrCreate(
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