<?php

namespace App\Filament\Resources\Employees\Pages;

use App\Filament\Resources\Employees\EmployeeResource;
use App\Services\ContractProcessingRuleService;
use App\Services\EmployeeContractDocumentService;
use App\Services\EmployeeEpiReportService;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;

class EditEmployee extends EditRecord
{
    protected static string $resource = EmployeeResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
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

    protected function getHeaderActions(): array
    {
        return [
            Action::make('generate_contract')
                ->label('Gerar Contrato')
                ->icon('heroicon-o-document-text')
                ->color('success')
                ->action(function () {
                    $service = app(EmployeeContractDocumentService::class);

                    return response()->streamDownload(
                        fn () => print($service->output($this->record)),
                        'contrato-' . $this->record->id . '.pdf'
                    );
                }),

            Action::make('employee_epi_report')
                ->label('Relatório de EPI')
                ->icon('heroicon-o-shield-check')
                ->color('primary')
                ->action(function () {
                    $service = app(EmployeeEpiReportService::class);

                    return response()->streamDownload(
                        fn () => print($service->generate($this->record)->output()),
                        'relatorio-epi-colaborador-' . $this->record->id . '.pdf'
                    );
                }),
        ];
    }
}