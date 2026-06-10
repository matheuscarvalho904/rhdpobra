<?php

namespace App\Filament\Resources\TimeEntries\Pages;

use App\Filament\Resources\TimeEntries\TimeEntryResource;
use App\Models\PayrollRun;
use App\Models\TimeClosing;
use App\Services\PayrollRunProcessingService;
use App\Services\TimeClosingProcessingService;
use App\Services\TimeClosingToPayrollService;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Throwable;

class ListTimeEntries extends ListRecords
{
    protected static string $resource = TimeEntryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Nova Marcação'),

            Action::make('reprocessarFechamento')
                ->label('Reprocessar Fechamento')
                ->icon('heroicon-o-arrow-path')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Reprocessar fechamento')
                ->modalDescription('O sistema irá recalcular o fechamento, gerar eventos variáveis e reprocessar a folha vinculada.')
                ->modalSubmitActionLabel('Reprocessar')
                ->action(function (): void {
                    try {
                        $startDate = request()->get('start_date');
                        $endDate = request()->get('end_date');

                        $closing = TimeClosing::query()
                            ->when($startDate, fn ($query) => $query->whereDate('start_date', $startDate))
                            ->when($endDate, fn ($query) => $query->whereDate('end_date', $endDate))
                            ->latest()
                            ->first();

                        if (! $closing) {
                            Notification::make()
                                ->title('Fechamento não encontrado')
                                ->body('Não foi localizado fechamento para o período filtrado.')
                                ->warning()
                                ->send();

                            return;
                        }

                        if (! $closing->payroll_competency_id) {
                            Notification::make()
                                ->title('Competência obrigatória')
                                ->body('O fechamento precisa ter uma competência da folha vinculada.')
                                ->warning()
                                ->send();

                            return;
                        }

                        $closing = app(TimeClosingProcessingService::class)->process($closing);

                        app(TimeClosingToPayrollService::class)->generate(
                            $closing,
                            $closing->payroll_competency_id
                        );

                        $runs = PayrollRun::query()
                            ->where('company_id', $closing->company_id)
                            ->where('payroll_competency_id', $closing->payroll_competency_id)
                            ->whereIn('run_type', [
                                'payroll_clt',
                                'payroll_apprentice',
                            ])
                            ->get();

                        foreach ($runs as $run) {
                            app(PayrollRunProcessingService::class)->reprocess($run);
                        }

                        Notification::make()
                            ->title('Fechamento reprocessado')
                            ->body('As marcações foram recalculadas, os eventos foram gerados e a folha foi reprocessada.')
                            ->success()
                            ->send();

                    } catch (Throwable $e) {
                        Notification::make()
                            ->title('Erro ao reprocessar')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }

    public function mount(): void
    {
        parent::mount();

        $tableFilters = [];

        if (request()->filled('start_date') || request()->filled('end_date')) {
    $tableFilters['periodo'] = [
        'start_date' => request()->get('start_date'),
        'end_date' => request()->get('end_date'),
    ];
}

if (request()->filled('employee_id')) {
    $tableFilters['employee_id'] = [
        'value' => request()->get('employee_id'),
    ];
}
    }
}