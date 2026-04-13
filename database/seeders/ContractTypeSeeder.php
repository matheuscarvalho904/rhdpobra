<?php

namespace Database\Seeders;

use App\Models\ContractType;
use Illuminate\Database\Seeder;

class ContractTypeSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            [
                'name' => 'CLT',
                'code' => 'CLT',
                'description' => 'Contrato celetista padrão.',
                'sort_order' => 1,
            ],
            [
                'name' => 'Experiência',
                'code' => 'EXPERIENCIA',
                'description' => 'Contrato de experiência.',
                'sort_order' => 2,
            ],
            [
                'name' => 'Temporário',
                'code' => 'TEMPORARIO',
                'description' => 'Contrato temporário.',
                'sort_order' => 3,
            ],
            [
                'name' => 'Intermitente',
                'code' => 'INTERMITENTE',
                'description' => 'Contrato intermitente.',
                'sort_order' => 4,
            ],
            [
                'name' => 'Aprendiz',
                'code' => 'APRENDIZ',
                'description' => 'Contrato de aprendizagem.',
                'sort_order' => 5,
            ],
            [
                'name' => 'Estágio',
                'code' => 'ESTAGIO',
                'description' => 'Contrato de estágio.',
                'sort_order' => 6,
            ],
            [
                'name' => 'Pessoa Física',
                'code' => 'PF',
                'description' => 'Prestador pessoa física / RPA.',
                'sort_order' => 7,
            ],
            [
                'name' => 'Pessoa Jurídica',
                'code' => 'PJ',
                'description' => 'Prestador pessoa jurídica.',
                'sort_order' => 8,
            ],
            [
                'name' => 'Autônomo',
                'code' => 'AUTONOMO',
                'description' => 'Prestador autônomo.',
                'sort_order' => 9,
            ],
        ];

        foreach ($items as $item) {
            ContractType::updateOrCreate(
                ['code' => $item['code']],
                [
                    'name' => $item['name'],
                    'code' => $item['code'],
                    'description' => $item['description'],
                    'sort_order' => $item['sort_order'],
                    'is_active' => true,
                ]
            );
        }
    }
}