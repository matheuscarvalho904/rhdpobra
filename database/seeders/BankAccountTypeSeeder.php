<?php

namespace Database\Seeders;

use App\Models\BankAccountType;
use Illuminate\Database\Seeder;

class BankAccountTypeSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            [
                'name' => 'Conta Corrente',
                'code' => 'checking',
                'description' => 'Conta corrente padrão para movimentações bancárias.',
            ],
            [
                'name' => 'Conta Poupança',
                'code' => 'savings',
                'description' => 'Conta poupança para recebimentos e aplicações.',
            ],
            [
                'name' => 'Conta Salário',
                'code' => 'salary',
                'description' => 'Conta vinculada exclusivamente ao pagamento de salário.',
            ],
            [
                'name' => 'PIX',
                'code' => 'pix',
                'description' => 'Pagamento via chave PIX (CPF, CNPJ, e-mail, telefone ou aleatória).',
            ],
            [
                'name' => 'Carteira Digital',
                'code' => 'digital_wallet',
                'description' => 'Recebimento via carteira digital (PicPay, Mercado Pago, etc).',
            ],
            [
                'name' => 'Conta Internacional',
                'code' => 'international',
                'description' => 'Conta bancária internacional para pagamentos no exterior.',
            ],
            [
                'name' => 'Conta Conjunta',
                'code' => 'joint_account',
                'description' => 'Conta compartilhada com mais de um titular.',
            ],
            [
                'name' => 'Conta de Pagamento',
                'code' => 'payment_account',
                'description' => 'Conta de instituições de pagamento (fintechs).',
            ],
            [
                'name' => 'Conta Pré-Paga',
                'code' => 'prepaid',
                'description' => 'Conta com saldo previamente carregado.',
            ],
        ];

        foreach ($items as $item) {
            BankAccountType::updateOrCreate(
                ['code' => $item['code']],
                [
                    'name' => $item['name'],
                    'code' => $item['code'],
                    'description' => $item['description'],
                    'is_active' => true,
                ]
            );
        }
    }
}