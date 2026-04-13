<?php

namespace App\Filament\Resources\Payslips\Pages;

use App\Filament\Resources\Payslips\PayslipResource;
use App\Services\PayslipService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class EditPayslip extends EditRecord
{
    protected static string $resource = PayslipResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('generatePdf')
                ->label('Gerar PDF')
                ->icon('heroicon-o-document')
                ->color('primary')
                ->action(function () {
                    try {
                        $this->record->loadMissing([
                            'payrollRun.payrollCompetency',
                            'employee',
                        ]);

                        if (! $this->record->payrollRun) {
                            throw new \RuntimeException('Processamento da folha não encontrado para este holerite.');
                        }

                        if (! $this->record->employee) {
                            throw new \RuntimeException('Colaborador não encontrado para este holerite.');
                        }

                        $pdf = app(PayslipService::class)->generate(
                            $this->record->payrollRun,
                            $this->record->employee
                        );

                        $employeeName = Str::slug($this->record->employee->name ?? 'colaborador');
                        $runId = $this->record->payrollRun->id;
                        $fileName = "holerite-{$employeeName}-folha-{$runId}.pdf";
                        $relativePath = 'payslips/' . $fileName;

                        Storage::disk('public')->put($relativePath, $pdf->output());

                        $this->record->update([
                            'file_path' => $relativePath,
                            'printed_at' => now(),
                        ]);

                        Notification::make()
                            ->title('PDF do holerite gerado com sucesso.')
                            ->success()
                            ->send();

                        $this->redirect($this->getResource()::getUrl('edit', [
                            'record' => $this->record,
                        ]));
                    } catch (Throwable $e) {
                        Notification::make()
                            ->title('Erro ao gerar PDF do holerite.')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            Action::make('downloadPdf')
                ->label('Baixar PDF')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->visible(fn () => filled($this->record->file_path))
                ->url(fn () => asset('storage/' . ltrim((string) $this->record->file_path, '/')))
                ->openUrlInNewTab(),

            DeleteAction::make(),
        ];
    }
}