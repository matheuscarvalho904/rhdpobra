<?php

namespace App\Services;

use App\Models\ContractType;

class ContractProcessingRuleService
{
    public static function getByContractTypeCode(?string $contractTypeCode): array
    {
        $code = self::normalizeCode($contractTypeCode);

        return match ($code) {
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

            'PF', 'AUTONOMO', 'RPA' => self::makeRules(
                processingType: 'payroll_rpa',
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

            default => self::getDefaultRules(),
        };
    }

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

    public static function applyToArray(array $data, ?int $contractTypeId): array
    {
        $rules = self::getByContractTypeId($contractTypeId);

        $data['processing_type'] = $rules['processing_type'];
        $data['generates_payroll'] = $rules['generates_payroll'];
        $data['generates_accounts_payable'] = $rules['generates_accounts_payable'];
        $data['allows_payslip'] = $rules['allows_payslip'];

        $data['has_fgts'] = (bool) $rules['has_fgts'];
        $data['fgts_rate'] = (bool) $rules['has_fgts']
            ? (float) ($rules['fgts_rate'] ?? 8.00)
            : 0.00;

        $data['has_inss'] = (bool) $rules['has_inss'];
        $data['inss_optional'] = (bool) $rules['inss_optional'];
        $data['with_inss'] = (bool) $rules['has_inss']
            ? (bool) $rules['with_inss']
            : false;

        $data['has_irrf'] = (bool) $rules['has_irrf'];

        return $data;
    }

    public static function processingTypeOptions(): array
    {
        return [
            'payroll_clt' => 'Folha CLT',
            'payroll_rpa' => 'Folha RPA / PF',
            'internship_payment' => 'Pagamento de Estágio',
            'accounts_payable' => 'Contas a Pagar / PJ',
        ];
    }

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
            'inss_optional' => $hasInss ? $inssOptional : false,
            'with_inss' => $hasInss ? $withInss : false,

            'has_irrf' => $hasIrrf,
        ];
    }

    protected static function normalizeCode(?string $code): string
    {
        $code = strtoupper(trim((string) $code));

        $converted = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $code);

        if ($converted !== false) {
            $code = $converted;
        }

        return preg_replace('/[^A-Z0-9_]/', '', $code) ?? '';
    }
}