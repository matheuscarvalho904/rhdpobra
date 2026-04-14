<?php

namespace App\Filament\Pages;

use App\Models\PayrollCompetency;
use App\Models\PayrollRun;
use App\Services\PayrollAccountingExportService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use UnitEnum;

class PayrollAccountingExport extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationLabel = 'Exportação Contábil da Folha';
    protected static ?string $title = 'Exportação Contábil da Folha';
    protected static ?int $navigationSort = 35;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-arrow-down';
    protected static string|UnitEnum|null $navigationGroup = 'Folha';

    protected string $view = 'filament.pages.payroll-accounting-export';

    public ?int $payroll_competency_id = null;
    public ?int $payroll_run_id = null;

    public array $competencies = [];
    public array $runs = [];

    public function mount(): void
    {
        $this->competencies = PayrollCompetency::query()
            ->orderByDesc('year')
            ->orderByDesc('month')
            ->get()
            ->mapWithKeys(fn ($item) => [
                $item->id => str_pad((string) $item->month, 2, '0', STR_PAD_LEFT) . '/' . $item->year,
            ])
            ->toArray();

        $this->loadRuns();
    }

    public function updatedPayrollCompetencyId(): void
    {
        $this->payroll_run_id = null;
        $this->loadRuns();
    }

    protected function loadRuns(): void
    {
        $this->runs = PayrollRun::query()
            ->with(['company', 'work'])
            ->when($this->payroll_competency_id, fn ($q) => $q->where('payroll_competency_id', $this->payroll_competency_id))
            ->orderByDesc('id')
            ->get()
            ->mapWithKeys(function ($run) {
                $company = $run->company->name ?? 'Sem empresa';
                $work = $run->work->name ?? 'Sem obra';

                return [
                    $run->id => "Folha #{$run->id} - {$company} - {$work}",
                ];
            })
            ->toArray();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('exportCsv')
                ->label('Exportar CSV')
                ->icon('heroicon-o-arrow-down-tray')
                ->action(function (PayrollAccountingExportService $service) {
                    if (! $this->payroll_run_id) {
                        Notification::make()
                            ->title('Selecione uma folha para exportação.')
                            ->warning()
                            ->send();

                        return;
                    }

                    $run = PayrollRun::find($this->payroll_run_id);

                    if (! $run) {
                        Notification::make()
                            ->title('Folha não encontrada.')
                            ->danger()
                            ->send();

                        return;
                    }

                    $filePath = $service->exportDetailedCsv($run);

                    return response()->download($filePath)->deleteFileAfterSend(false);
                }),
        ];
    }

    protected function getFormSchema(): array
    {
        return [
            Select::make('payroll_competency_id')
                ->label('Competência')
                ->options($this->competencies)
                ->searchable()
                ->live(),

            Select::make('payroll_run_id')
                ->label('Folha')
                ->options($this->runs)
                ->searchable()
                ->required(),
        ];
    }
}