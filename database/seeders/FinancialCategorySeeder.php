<?php

namespace Database\Seeders;

use App\Models\FinancialCategory;
use Illuminate\Database\Seeder;

class FinancialCategorySeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['name' => 'Folha de Pagamento', 'code' => 'FOLHA-PAGAMENTO', 'type' => 'expense'],
            ['name' => 'Encargos Trabalhistas', 'code' => 'ENCARGOS-TRAB', 'type' => 'expense'],
            ['name' => 'Benefícios', 'code' => 'BENEFICIOS', 'type' => 'expense'],
            ['name' => 'Férias', 'code' => 'FERIAS', 'type' => 'expense'],
            ['name' => 'Rescisões', 'code' => 'RESCISOES', 'type' => 'expense'],
            ['name' => '13º Salário', 'code' => 'DECIMO-TERCEIRO', 'type' => 'expense'],
            ['name' => 'Adiantamento Salarial', 'code' => 'ADIANTAMENTO-SALARIAL', 'type' => 'expense'],
            ['name' => 'Saúde Ocupacional', 'code' => 'SAUDE-OCUPACIONAL', 'type' => 'expense'],
            ['name' => 'Uniformes e EPIs', 'code' => 'UNIFORMES-EPI', 'type' => 'expense'],
            ['name' => 'Treinamentos', 'code' => 'TREINAMENTOS', 'type' => 'expense'],
            ['name' => 'Administrativo', 'code' => 'ADMINISTRATIVO', 'type' => 'expense'],
            ['name' => 'Operacional', 'code' => 'OPERACIONAL', 'type' => 'expense'],
        ];

        foreach ($items as $item) {
            FinancialCategory::updateOrCreate(
                ['code' => $item['code']],
                [
                    'name' => $item['name'],
                    'code' => $item['code'],
                    'type' => $item['type'],
                    'description' => null,
                    'is_active' => true,
                ]
            );
        }
    }
}