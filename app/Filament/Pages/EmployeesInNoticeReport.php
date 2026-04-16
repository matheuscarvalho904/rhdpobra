<?php

namespace App\Filament\Pages;

use App\Models\Branch;
use App\Models\Company;
use App\Models\ContractType;
use App\Models\JobRole;
use App\Models\Work;
use App\Services\EmployeeReportService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use UnitEnum;

class EmployeesInNoticeReport extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-exclamation-triangle';
    protected static string|UnitEnum|null $navigationGroup = 'RH e DP';
    protected static ?string $navigationLabel = 'Colaboradores em Aviso';
    protected static ?string $title = 'Relatório de Colaboradores em Aviso Prévio';
    protected static ?int $navigationSort = 32;

    protected string $view = 'filament.pages.employees-in-notice-report';

    public ?int $company_id = null;
    public ?int $branch_id = null;
    public ?int $work_id = null;
    public ?int $job_role_id = null;
    public ?int $contract_type_id = null;

    public array $reportData = [];

    public function mount(): void
    {
        $this->loadReport();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('filter')
                ->label('Atualizar Relatório')
                ->icon('heroicon-o-funnel')
                ->action(function () {
                    $this->loadReport();

                    Notification::make()
                        ->title('Relatório atualizado.')
                        ->success()
                        ->send();
                }),

            Action::make('pdf')
                ->label('Gerar PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->action(function () {
                    $service = app(EmployeeReportService::class);

                    $pdf = $service->generateEmployeesInNoticePdf($this->getFilters());

                    return response()->streamDownload(
                        fn () => print($pdf->output()),
                        'colaboradores-em-aviso.pdf'
                    );
                }),
        ];
    }

    public function loadReport(): void
    {
        $service = app(EmployeeReportService::class);

        $data = $service->getEmployeesInNotice($this->getFilters())
            ->map(function ($employee) {
                return [
                    'code' => (string) ($employee->code ?? ''),
                    'name' => (string) ($employee->name ?? ''),
                    'company_name' => (string) ($employee->company?->name ?? ''),
                    'branch_name' => (string) ($employee->branch?->name ?? ''),
                    'work_name' => (string) ($employee->work?->name ?? ''),
                    'job_role_name' => (string) ($employee->jobRole?->name ?? ''),
                    'contract_type_name' => (string) ($employee->contractType?->name ?? ''),
                    'registration_number' => (string) ($employee->currentContract?->registration_number ?? ''),
                    'admission_date' => optional($employee->admission_date)?->format('d/m/Y'),
                    'salary' => (float) ($employee->salary ?? 0),
                ];
            })
            ->values()
            ->toArray();

        $this->reportData = json_decode(
            json_encode($data, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_IGNORE),
            true
        ) ?? [];
    }

    protected function getFilters(): array
    {
        return [
            'company_id' => $this->company_id,
            'branch_id' => $this->branch_id,
            'work_id' => $this->work_id,
            'job_role_id' => $this->job_role_id,
            'contract_type_id' => $this->contract_type_id,
        ];
    }

    protected function getFormSchema(): array
    {
        return [
            Select::make('company_id')
                ->label('Empresa')
                ->options(Company::query()->orderBy('name')->pluck('name', 'id')->toArray())
                ->searchable(),

            Select::make('branch_id')
                ->label('Filial')
                ->options(Branch::query()->orderBy('name')->pluck('name', 'id')->toArray())
                ->searchable(),

            Select::make('work_id')
                ->label('Obra')
                ->options(Work::query()->orderBy('name')->pluck('name', 'id')->toArray())
                ->searchable(),

            Select::make('job_role_id')
                ->label('Cargo')
                ->options(JobRole::query()->orderBy('name')->pluck('name', 'id')->toArray())
                ->searchable(),

            Select::make('contract_type_id')
                ->label('Tipo de Contrato')
                ->options(ContractType::query()->orderBy('name')->pluck('name', 'id')->toArray())
                ->searchable(),
        ];
    }
}