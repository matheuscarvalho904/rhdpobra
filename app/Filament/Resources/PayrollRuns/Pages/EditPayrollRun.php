<?php

namespace App\Filament\Resources\PayrollRuns\Pages;

use App\Filament\Resources\PayrollRuns\PayrollRunResource;
use App\Services\PayrollAccountsPayableService;
use App\Services\PayrollRunProcessingService;
use App\Services\PayslipService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;
use Throwable;

class EditPayrollRun extends EditRecord
{
    protected static string $resource = PayrollRunResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('calculatePayroll')
                ->label('Calcular Folha')
                ->icon('heroicon-o-play')
                ->color('primary')
                ->requiresConfirmation()
                ->visible(fn () => in_array($this->record->status, ['open', 'processing', 'error'], true))
                ->action(function () {
                    try {
                        $this->record->update([
                            'processed_by' => Auth::id(),
                        ]);

                        app(PayrollRunProcessingService::class)
                            ->reprocess($this->record->fresh());

                        Notification::make()
                            ->title('Folha processada com sucesso.')
                            ->success()
                            ->send();

                        $this->redirect($this->getResource()::getUrl('edit', [
                            'record' => $this->record,
                        ]));
                    } catch (Throwable $e) {
                        Notification::make()
                            ->title('Erro ao calcular a folha.')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            Action::make('generatePayslips')
                ->label('Gerar Holerites')
                ->icon('heroicon-o-document-duplicate')
                ->color('info')
                ->requiresConfirmation()
                ->visible(fn () => in_array($this->record->status, ['processed', 'closed'], true))
                ->action(function () {
                    try {
                        $this->record->load('items.employee');

                        if ($this->record->items->isEmpty()) {
                            throw new \Exception('Nenhum item de folha encontrado.');
                        }

                        $count = 0;

                        $employees = $this->record->items
                            ->pluck('employee')
                            ->filter()
                            ->unique('id');

                        foreach ($employees as $employee) {
                            app(PayslipService::class)
                                ->generate($this->record, $employee);

                            $count++;
                        }

                        Notification::make()
                            ->title("{$count} holerite(s) gerado(s) com sucesso.")
                            ->success()
                            ->send();
                    } catch (Throwable $e) {
                        Notification::make()
                            ->title('Erro ao gerar holerites.')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            Action::make('integrateFinancial')
                ->label('Integrar ao Financeiro')
                ->icon('heroicon-o-banknotes')
                ->color('gray')
                ->requiresConfirmation()
                ->visible(fn () => $this->record->status === 'closed')
                ->action(function () {
                    try {
                        app(PayrollAccountsPayableService::class)
                            ->integrate($this->record->fresh());

                        Notification::make()
                            ->title('Conta a pagar gerada com sucesso.')
                            ->success()
                            ->send();
                    } catch (Throwable $e) {
                        Notification::make()
                            ->title('Erro ao integrar financeiro.')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            DeleteAction::make(),
        ];
    }
}