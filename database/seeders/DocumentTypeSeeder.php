<?php

namespace Database\Seeders;

use App\Models\DocumentType;
use Illuminate\Database\Seeder;

class DocumentTypeSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['name' => 'RG', 'code' => 'RG', 'requires_expiration' => false],
            ['name' => 'CPF', 'code' => 'CPF', 'requires_expiration' => false],
            ['name' => 'CNH', 'code' => 'CNH', 'requires_expiration' => true],
            ['name' => 'CTPS', 'code' => 'CTPS', 'requires_expiration' => false],
            ['name' => 'PIS', 'code' => 'PIS', 'requires_expiration' => false],
            ['name' => 'Título de Eleitor', 'code' => 'TITULO-ELEITOR', 'requires_expiration' => false],
            ['name' => 'Certidão de Nascimento', 'code' => 'CERT-NASC', 'requires_expiration' => false],
            ['name' => 'Certidão de Casamento', 'code' => 'CERT-CASAMENTO', 'requires_expiration' => false],
            ['name' => 'Comprovante de Residência', 'code' => 'COMP-RESIDENCIA', 'requires_expiration' => false],
            ['name' => 'Cartão SUS', 'code' => 'CARTAO-SUS', 'requires_expiration' => false],
            ['name' => 'Reservista', 'code' => 'RESERVISTA', 'requires_expiration' => false],
            ['name' => 'ASO Admissional', 'code' => 'ASO-ADM', 'requires_expiration' => false],
            ['name' => 'ASO Periódico', 'code' => 'ASO-PER', 'requires_expiration' => true],
            ['name' => 'ASO Demissional', 'code' => 'ASO-DEM', 'requires_expiration' => false],
            ['name' => 'Contrato de Trabalho', 'code' => 'CONTRATO-TRABALHO', 'requires_expiration' => false],
            ['name' => 'Ficha de Registro', 'code' => 'FICHA-REGISTRO', 'requires_expiration' => false],
            ['name' => 'Comprovante Bancário', 'code' => 'COMP-BANCARIO', 'requires_expiration' => false],
            ['name' => 'Atestado Médico', 'code' => 'ATESTADO-MEDICO', 'requires_expiration' => false],
            ['name' => 'Advertência', 'code' => 'ADVERTENCIA', 'requires_expiration' => false],
            ['name' => 'Suspensão', 'code' => 'SUSPENSAO', 'requires_expiration' => false],
            ['name' => 'Termo de Entrega de EPI', 'code' => 'TERMO-EPI', 'requires_expiration' => false],
        ];

        foreach ($items as $item) {
            DocumentType::updateOrCreate(
                ['code' => $item['code']],
                [
                    'name' => $item['name'],
                    'code' => $item['code'],
                    'requires_expiration' => $item['requires_expiration'],
                    'is_active' => true,
                ]
            );
        }
    }
}