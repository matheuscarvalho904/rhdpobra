<?php

namespace App\Filament\Resources\Employees\Pages;

use App\Filament\Resources\Employees\EmployeeResource;
use App\Services\ContractProcessingRuleService;
use Filament\Resources\Pages\CreateRecord;

class CreateEmployee extends CreateRecord
{
    protected static string $resource = EmployeeResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $rules = ContractProcessingRuleService::getByContractTypeId(
            isset($data['contract_type_id']) ? (int) $data['contract_type_id'] : null
        );

        $hasFgts = (bool) ($rules['has_fgts'] ?? false);
        $hasInss = (bool) ($rules['has_inss'] ?? true);

        $data['processing_type'] = $rules['processing_type'] ?? 'payroll';
        $data['generates_payroll'] = (bool) ($rules['generates_payroll'] ?? true);
        $data['generates_accounts_payable'] = (bool) ($rules['generates_accounts_payable'] ?? false);
        $data['allows_payslip'] = (bool) ($rules['allows_payslip'] ?? true);

        $data['has_fgts'] = $hasFgts;
        $data['fgts_rate'] = $hasFgts ? (float) ($rules['fgts_rate'] ?? 8) : 0;

        $data['has_inss'] = $hasInss;
        $data['inss_optional'] = (bool) ($rules['inss_optional'] ?? false);
        $data['with_inss'] = $hasInss ? (bool) ($rules['with_inss'] ?? true) : false;

        $data['has_irrf'] = (bool) ($rules['has_irrf'] ?? true);

        return $data;
    }
}