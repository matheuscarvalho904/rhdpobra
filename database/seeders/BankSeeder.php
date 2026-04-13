<?php

namespace Database\Seeders;

use App\Models\Bank;
use Illuminate\Database\Seeder;

class BankSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            // Bancos tradicionais
            ['code' => '001', 'name' => 'Banco do Brasil', 'full_name' => 'Banco do Brasil S.A.'],
            ['code' => '104', 'name' => 'Caixa', 'full_name' => 'Caixa Econômica Federal'],
            ['code' => '237', 'name' => 'Bradesco', 'full_name' => 'Banco Bradesco S.A.'],
            ['code' => '341', 'name' => 'Itaú', 'full_name' => 'Itaú Unibanco S.A.'],
            ['code' => '033', 'name' => 'Santander', 'full_name' => 'Banco Santander (Brasil) S.A.'],
            ['code' => '422', 'name' => 'Safra', 'full_name' => 'Banco Safra S.A.'],

            // Cooperativas
            ['code' => '748', 'name' => 'Sicredi', 'full_name' => 'Banco Cooperativo Sicredi S.A.'],
            ['code' => '756', 'name' => 'Sicoob', 'full_name' => 'Banco Cooperativo do Brasil S.A.'],
            ['code' => '085', 'name' => 'Cecred', 'full_name' => 'Cooperativa Central de Crédito Ailos'],

            // Digitais / fintechs (importante pro seu sistema)
            ['code' => '260', 'name' => 'Nubank', 'full_name' => 'Nu Pagamentos S.A.'],
            ['code' => '077', 'name' => 'Inter', 'full_name' => 'Banco Inter S.A.'],
            ['code' => '212', 'name' => 'Original', 'full_name' => 'Banco Original S.A.'],
            ['code' => '208', 'name' => 'BTG Pactual', 'full_name' => 'Banco BTG Pactual S.A.'],
            ['code' => '336', 'name' => 'C6 Bank', 'full_name' => 'Banco C6 S.A.'],
            ['code' => '655', 'name' => 'Votorantim', 'full_name' => 'Banco Votorantim S.A.'],
            ['code' => '389', 'name' => 'Mercado Pago', 'full_name' => 'Mercado Pago Instituição de Pagamento Ltda.'],
            ['code' => '323', 'name' => 'Mercado Pago (Conta)', 'full_name' => 'Mercado Pago - Conta de Pagamento'],
            ['code' => '403', 'name' => 'Cora', 'full_name' => 'Cora Sociedade de Crédito Direto S.A.'],
            ['code' => '290', 'name' => 'PagBank', 'full_name' => 'PagSeguro Internet S.A.'],
            ['code' => '380', 'name' => 'PicPay', 'full_name' => 'PicPay Instituição de Pagamento S.A.'],
            ['code' => '197', 'name' => 'Stone', 'full_name' => 'Stone Pagamentos S.A.'],

            // Outros relevantes
            ['code' => '399', 'name' => 'HSBC', 'full_name' => 'HSBC Bank Brasil S.A.'],
            ['code' => '745', 'name' => 'Citibank', 'full_name' => 'Banco Citibank S.A.'],
            ['code' => '041', 'name' => 'Banrisul', 'full_name' => 'Banco do Estado do Rio Grande do Sul S.A.'],
            ['code' => '004', 'name' => 'Banco do Nordeste', 'full_name' => 'Banco do Nordeste do Brasil S.A.'],
        ];

        foreach ($items as $item) {
            Bank::updateOrCreate(
                ['code' => $item['code']],
                [
                    'code' => $item['code'],
                    'name' => $item['name'],
                    'full_name' => $item['full_name'],
                    'is_active' => true,
                ]
            );
        }
    }
}