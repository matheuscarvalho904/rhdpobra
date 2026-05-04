<?php

namespace App\Filament\Resources\TimeClosings\Tables;

use App\Models\PayrollRun;
use App\Models\TimeClosing;
use App\Services\TimeBankService;
use App\Services\TimeClosingFullReprocessService;
use App\Services\TimeClosingProcessingService;
use App\Services\TimeClosingToPayrollService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Throwable;

class TimeClosingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('company.name')
                    ->label('Empresa')
                    ->searchable()
                    ->sortable()
                    ->placeholder('-'),

                TextColumn::make('payrollCompetency')
                    ->label('Competência')
                    ->formatStateUsing(function (TimeClosing $record): string {
                        $competency = $record->payrollCompetency;

                        if (! $competency) {
                            return '-';
                        }

                        $month = str_pad((string) $competency->month, 2, '0', STR_PAD_LEFT);

                        return "{$month}/{$competency->year}";
                    }),

                TextColumn::make('name')
                    ->label('Fechamento')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('start_date')
                    ->label('Início')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('end_date')
                    ->label('Fim')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'draft' => 'Rascunho',
                        'processed' => 'Processado',
                        'closed' => 'Fechado',
                        'canceled' => 'Cancelado',
                        default => $state ?: '-',
                    })
                    ->color(fn (?string $state): string => match ($state) {
                        'draft' => 'gray',
                        'processed' => 'success',
                        'closed' => 'info',
                        'canceled' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('employee_count')
                    ->label('Colaboradores')
                    ->sortable(),

                TextColumn::make('total_worked_hours')
                    ->label('Horas Trab.')
                    ->numeric(decimalPlaces: 2)
                    ->sortable(),

                TextColumn::make('total_overtime_hours')
                    ->label('Extras')
                    ->numeric(decimalPlaces: 2)
                    ->sortable(),

                TextColumn::make('total_delay_hours')
                    ->label('Atrasos')
                    ->numeric(decimalPlaces: 2)
                    ->sortable(),

                TextColumn::make('total_absence_days')
                    ->label('Faltas')
                    ->numeric(decimalPlaces: 2)
                    ->sortable(),

                TextColumn::make('processed_at')
                    ->label('Processado em')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('-')
                    ->toggleable(),

                TextColumn::make('closed_at')
                    ->label('Fechado em')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                Action::make('processarFechamento')
                    ->label('Processar Fechamento')
                    ->icon('heroicon-o-play-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Processar fechamento de ponto')
                    ->modalDescription('O sistema irá recalcular o fechamento e gerar eventos variáveis para a folha.')
                    ->modalSubmitActionLabel('Processar')
                    ->visible(fn (TimeClosing $record): bool => ! in_array($record->status, ['closed', 'canceled'], true))
                    ->action(function (TimeClosing $record): void {
                        try {
                            if (! $record->payroll_competency_id) {
                                Notification::make()
                                    ->title('Competência da folha obrigatória')
                                    ->body('Edite o fechamento e selecione uma competência da folha antes de processar.')
                                    ->warning()
                                    ->send();

                                return;
                            }

                            $closing = app(TimeClosingProcessingService::class)->process($record);

                            app(TimeClosingToPayrollService::class)->generate(
                                $closing,
                                $closing->payroll_competency_id
                            );

                            Notification::make()
                                ->title('Fechamento processado com sucesso')
                                ->body(
                                    "Colaboradores: {$closing->employee_count} | " .
                                    "Horas: {$closing->total_worked_hours} | " .
                                    "Extras: {$closing->total_overtime_hours} | " .
                                    "Atrasos: {$closing->total_delay_hours} | " .
                                    "Faltas: {$closing->total_absence_days}"
                                )
                                ->success()
                                ->send();
                        } catch (Throwable $e) {
                            Notification::make()
                                ->title('Erro ao processar fechamento')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                Action::make('reprocessarTudo')
                    ->label('Reprocessar Tudo')
                    ->icon('heroicon-o-arrow-path-rounded-square')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Reprocessar tudo')
                    ->modalDescription('Isso irá limpar marcações do período, reimportar da Sólides, processar o fechamento, gerar eventos e reprocessar a folha.')
                    ->modalSubmitActionLabel('Reprocessar')
                    ->visible(fn (TimeClosing $record): bool => ! in_array($record->status, ['closed', 'canceled'], true))
                    ->action(function (TimeClosing $record): void {
                        if (! $record->payroll_competency_id) {
                            Notification::make()
                                ->title('Competência da folha obrigatória')
                                ->body('Edite o fechamento e selecione uma competência da folha antes de reprocessar tudo.')
                                ->warning()
                                ->send();

                            return;
                        }

                        $payrollRun = PayrollRun::query()
                            ->where('company_id', $record->company_id)
                            ->where('payroll_competency_id', $record->payroll_competency_id)
                            ->latest()
                            ->first();

                        if (! $payrollRun) {
                            Notification::make()
                                ->title('Folha não encontrada')
                                ->body('Nenhuma folha foi encontrada para a competência deste fechamento.')
                                ->danger()
                                ->send();

                            return;
                        }

                        try {
                            $closing = app(TimeClosingFullReprocessService::class)
                                ->run($record, $payrollRun);

                            Notification::make()
                                ->title('Reprocessamento concluído')
                                ->body(
                                    "Horas: {$closing->total_worked_hours} | " .
                                    "Extras: {$closing->total_overtime_hours} | " .
                                    "Atrasos: {$closing->total_delay_hours} | " .
                                    "Faltas: {$closing->total_absence_days}"
                                )
                                ->success()
                                ->send();
                        } catch (Throwable $e) {
                            Notification::make()
                                ->title('Erro ao reprocessar tudo')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                Action::make('fechar')
                    ->label('Fechar')
                    ->icon('heroicon-o-lock-closed')
                    ->color('info')
                    ->requiresConfirmation()
                    ->modalHeading('Fechar competência de ponto')
                    ->modalDescription('Após fechar, este fechamento ficará bloqueado para novo processamento.')
                    ->modalSubmitActionLabel('Fechar')
                    ->visible(fn (TimeClosing $record): bool => $record->status === 'processed')
                    ->action(function (TimeClosing $record): void {
                        $record->update([
                            'status' => 'closed',
                            'closed_at' => now(),
                        ]);

                        Notification::make()
                            ->title('Fechamento bloqueado')
                            ->body('O fechamento foi marcado como fechado.')
                            ->success()
                            ->send();
                    }),

                Action::make('verResumo')
                    ->label('Ver Resumo')
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->modalHeading('Resumo do Fechamento')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Fechar')
                    ->modalContent(fn (TimeClosing $record) => view(
                        'filament.resources.time-closings.view',
                        ['record' => $record]
                    )),
                    Action::make('gerarBancoHoras')
    ->label('Banco de Horas')
    ->icon('heroicon-o-scale')
    ->color('warning')
    ->requiresConfirmation()
    ->modalHeading('Gerar banco de horas')
    ->modalDescription('Irá gerar créditos/débitos com base no fechamento.')
    ->modalSubmitActionLabel('Gerar')
    ->visible(fn (TimeClosing $record): bool => $record->status === 'processed')
    ->action(function (TimeClosing $record): void {
        try {

            app(TimeBankService::class)->applyFromClosing($record);

            Notification::make()
                ->title('Banco de horas atualizado')
                ->success()
                ->send();

        } catch (Throwable $e) {

            Notification::make()
                ->title('Erro no banco de horas')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }),
            ]);
            
    }
}