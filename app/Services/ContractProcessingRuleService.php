<?php

namespace App\Services;

use App\Models\ContractType;

class ContractProcessingRuleService
{
    /**
     * Regras por código do tipo de contrato.
     */
    public static function getByContractTypeCode(?string $contractTypeCode): array
    {
        $code = strtoupper(trim((string) $contractTypeCode));

        return match ($code) {
            /*
            |--------------------------------------------------------------------------
            | CLT / VÍNCULOS TRABALHISTAS
            |--------------------------------------------------------------------------
            */
            'CLT', 'EXPERIENCIA', 'TEMPORARIO', 'INTERMITENTE' => self::makeRules(
                processingType: 'payroll_clt',
                generatesPayroll: true,
                generatesAccountsPayable: false,
                allowsPayslip: true,
                hasFgts: true,
                fgtsRate: 8.00,
                hasInss: true,
                inssOptional: false,
                withInss: true,
                hasIrrf: true,
            ),

            /*
            |--------------------------------------------------------------------------
            | APRENDIZ
            |--------------------------------------------------------------------------
            */
            'APRENDIZ' => self::makeRules(
                processingType: 'payroll_clt',
                generatesPayroll: true,
                generatesAccountsPayable: false,
                allowsPayslip: true,
                hasFgts: true,
                fgtsRate: 2.00,
                hasInss: true,
                inssOptional: false,
                withInss: true,
                hasIrrf: true,
            ),

            /*
            |--------------------------------------------------------------------------
            | ESTÁGIO
            |--------------------------------------------------------------------------
            */
            'ESTAGIO' => self::makeRules(
                processingType: 'internship_payment',
                generatesPayroll: true,
                generatesAccountsPayable: false,
                allowsPayslip: true,
                hasFgts: false,
                fgtsRate: 0.00,
                hasInss: false,
                inssOptional: false,
                withInss: false,
                hasIrrf: false,
            ),

            /*
            |--------------------------------------------------------------------------
            | PESSOA FÍSICA / AUTÔNOMO / RPA
            |--------------------------------------------------------------------------
            */
            'PF', 'AUTONOMO', 'RPA' => self::makeRules(
                processingType: 'payroll_rpa',
                generatesPayroll: true,
                generatesAccountsPayable: false,
                allowsPayslip: true,
                hasFgts: false,
                fgtsRate: 0.00,
                hasInss: true,
                inssOptional: true,
                withInss: true,
                hasIrrf: true,
            ),

            /*
            |--------------------------------------------------------------------------
            | PESSOA JURÍDICA
            |--------------------------------------------------------------------------
            */
            'PJ' => self::makeRules(
                processingType: 'accounts_payable',
                generatesPayroll: false,
                generatesAccountsPayable: true,
                allowsPayslip: false,
                hasFgts: false,
                fgtsRate: 0.00,
                hasInss: false,
                inssOptional: false,
                withInss: false,
                hasIrrf: false,
            ),

            /*
            |--------------------------------------------------------------------------
            | PADRÃO
            |--------------------------------------------------------------------------
            */
            default => self::getDefaultRules(),
        };
    }

    /**
     * Busca as regras pelo ID do tipo de contrato.
     */
    public static function getByContractTypeId(?int $contractTypeId): array
    {
        if (! $contractTypeId) {
            return self::getDefaultRules();
        }

        $contractType = ContractType::query()->find($contractTypeId);

        if (! $contractType) {
            return self::getDefaultRules();
        }

        return self::getByContractTypeCode($contractType->code);
    }

    /**
     * Regras padrão do sistema.
     */
    public static function getDefaultRules(): array
    {
        return self::makeRules(
            processingType: 'payroll_clt',
            generatesPayroll: true,
            generatesAccountsPayable: false,
            allowsPayslip: true,
            hasFgts: true,
            fgtsRate: 8.00,
            hasInss: true,
            inssOptional: false,
            withInss: true,
            hasIrrf: true,
        );
    }

    /**
     * Aplica regras automáticas a um array de dados.
     */
    public static function applyToArray(array $data, ?int $contractTypeId): array
    {
        $rules = self::getByContractTypeId($contractTypeId);

        $data['processing_type'] = $rules['processing_type'];
        $data['generates_payroll'] = $rules['generates_payroll'];
        $data['generates_accounts_payable'] = $rules['generates_accounts_payable'];
        $data['allows_payslip'] = $rules['allows_payslip'];

        $data['has_fgts'] = $rules['has_fgts'];
        $data['has_inss'] = $rules['has_inss'];
        $data['has_irrf'] = $rules['has_irrf'];

        $data['fgts_rate'] = ($rules['has_fgts'] ?? false)
            ? (float) ($rules['fgts_rate'] ?? 8.00)
            : 0.00;

        $data['inss_optional'] = $rules['inss_optional'];

        if (! array_key_exists('with_inss', $data) || ! $rules['inss_optional']) {
            $data['with_inss'] = $rules['with_inss'];
        }

        return $data;
    }

    /**
     * Opções amigáveis para formulários.
     */
    public static function processingTypeOptions(): array
    {
        return [
            'payroll_clt' => 'Folha CLT',
            'payroll_rpa' => 'Folha RPA / PF',
            'internship_payment' => 'Pagamento de Estágio',
            'accounts_payable' => 'Contas a Pagar / PJ',
        ];
    }

    /**
     * Monta um array padronizado de regras.
     */
    protected static function makeRules(
        string $processingType,
        bool $generatesPayroll,
        bool $generatesAccountsPayable,
        bool $allowsPayslip,
        bool $hasFgts,
        float $fgtsRate,
        bool $hasInss,
        bool $inssOptional,
        bool $withInss,
        bool $hasIrrf,
    ): array {
        return [
            'processing_type' => $processingType,
            'generates_payroll' => $generatesPayroll,
            'generates_accounts_payable' => $generatesAccountsPayable,
            'allows_payslip' => $allowsPayslip,

            'has_fgts' => $hasFgts,
            'fgts_rate' => $hasFgts ? round($fgtsRate, 2) : 0.00,

            'has_inss' => $hasInss,
            'inss_optional' => $inssOptional,
            'with_inss' => $hasInss ? $withInss : false,

            'has_irrf' => $hasIrrf,
        ];
    }
}