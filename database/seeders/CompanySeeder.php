<?php

namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    public function run(): void
    {
        $companies = [

            [
                'name' => 'Construtora Dardanellos Ltda',
                'trade_name' => 'Dardanellos',
                'code' => 'DARD',
                'document' => '04400802000140',
                'state_registration' => 'ISENTO',
                'phone' => '(66) 99999-0000',
                'email' => 'contato@dardanellos.com.br',
                'zip_code' => '78325-000',
                'address' => 'Avenida Principal',
                'number' => '1000',
                'complement' => null,
                'district' => 'Centro',
                'city' => 'Aripuanã',
                'state' => 'MT',
                'is_active' => true,
            ],

            [
                'name' => 'Ampla Construtora e Pré-Moldados Ltda',
                'trade_name' => 'Ampla Construtora e Pré-Moldados',
                'code' => 'AMPLA',
                'document' => '12345678000172',
                'state_registration' => 'ISENTO',
                'phone' => '(66) 99999-0001',
                'email' => 'contato@amplaconstrutora.com.br',
                'zip_code' => '78320-000',
                'address' => 'Rua Industrial',
                'number' => '500',
                'complement' => null,
                'district' => 'Distrito Industrial',
                'city' => 'Juína',
                'state' => 'MT',
                'is_active' => true,
            ],

            // 👇 IMPORTANTE PRO SEU SISTEMA DE REFEIÇÃO
            [
                'name' => 'NutriObra Gestão de Alimentação Ltda',
                'trade_name' => 'NutriObra',
                'code' => 'NUTRIOBRA',
                'document' => '22345678000190',
                'state_registration' => 'ISENTO',
                'phone' => '(66) 99999-0002',
                'email' => 'contato@nutriobra.com.br',
                'zip_code' => '78325-000',
                'address' => 'Rua das Obras',
                'number' => '250',
                'complement' => null,
                'district' => 'Centro',
                'city' => 'Aripuanã',
                'state' => 'MT',
                'is_active' => true,
            ],

            // 👇 EMPRESA DE APOIO (ÚTIL PRA TESTE)
            [
                'name' => 'Dardanellos Serviços e Transportes Ltda',
                'trade_name' => 'Dardanellos Transportes',
                'code' => 'DARDTRANS',
                'document' => '33456789000110',
                'state_registration' => 'ISENTO',
                'phone' => '(66) 99999-0003',
                'email' => 'transportes@dardanellos.com.br',
                'zip_code' => '78325-000',
                'address' => 'Rodovia MT 208',
                'number' => 'S/N',
                'complement' => 'Km 10',
                'district' => 'Zona Rural',
                'city' => 'Aripuanã',
                'state' => 'MT',
                'is_active' => true,
            ],
        ];

        foreach ($companies as $company) {
            Company::updateOrCreate(
                ['code' => $company['code']],
                [
                    'name' => $company['name'],
                    'trade_name' => $company['trade_name'],
                    'code' => $company['code'],
                    'document' => $company['document'],
                    'state_registration' => $company['state_registration'],
                    'phone' => $company['phone'],
                    'email' => $company['email'],
                    'zip_code' => $company['zip_code'],
                    'address' => $company['address'],
                    'number' => $company['number'],
                    'complement' => $company['complement'],
                    'district' => $company['district'],
                    'city' => $company['city'],
                    'state' => $company['state'],
                    'is_active' => $company['is_active'],
                ]
            );
        }
    }
}