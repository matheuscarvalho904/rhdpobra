<?php

namespace App\Filament\Resources\SalaryAdvances\Tables;

use App\Services\SalaryAdvanceBatchReportService;
use App\Services\SalaryAdvanceReportService;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class SalaryAdvancesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('employee.name')
                    ->label('Colaborador')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('company.name')
                    ->label('Empresa')
                    ->placeholder('-')
                    ->toggleable(),

                TextColumn::make('work.name')
                    ->label('Obra')
                    ->placeholder('-')
                    ->toggleable(),

                TextColumn::make('advance_date')
                    ->label('Data')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('amount')
                    ->label('Valor')
                    ->formatStateUsing(fn ($state) => 'R$ ' . number_format((float) $state, 2, ',', '.'))
                    ->sortable(),

                TextColumn::make('payment_method')
                    ->label('Pagamento')
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => match ($state) {
                        'pix' => 'PIX',
                        'bank_transfer' => 'Transferência',
                        'cash' => 'Dinheiro',
                        default => $state ?: '-',
                    }),

                TextColumn::make('pix_key_type')
                    ->label('Tipo PIX')
                    ->formatStateUsing(fn (?string $state) => match ($state) {
                        'cpf' => 'CPF',
                        'cnpj' => 'CNPJ',
                        'email' => 'E-mail',
                        'phone' => 'Telefone',
                        'random' => 'Aleatória',
                        default => $state ?? '-',
                    })
                    ->toggleable(),

                TextColumn::make('pix_key')
                    ->label('Chave PIX')
                    ->placeholder('-')
                    ->toggleable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (?string $state) => match ($state) {
                        'draft' => 'gray',
                        'paid' => 'success',
                        'canceled' => 'danger',
                        'integrated_payroll' => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (?string $state) => match ($state) {
                        'draft' => 'Rascunho',
                        'paid' => 'Pago',
                        'canceled' => 'Cancelado',
                        'integrated_payroll' => 'Integrado na Folha',
                        default => $state ?: '-',
                    }),

                TextColumn::make('paid_at')
                    ->label('Pago em')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('-')
                    ->toggleable(),

                TextColumn::make('paidBy.name')
                    ->label('Pago por')
                    ->placeholder('-')
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y H:i')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([
                EditAction::make(),

                Action::make('mark_as_paid')
                    ->label('Marcar como Pago')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Confirmar pagamento')
                    ->modalDescription('Deseja marcar este adiantamento como pago? Essa ação registrará data, hora e usuário responsável.')
                    ->action(function ($record) {
                        try {
                            self::validateBeforePayment($record);

                            DB::transaction(function () use ($record) {
                                $record->update([
                                    'status' => 'paid',
                                    'paid_at' => now(),
                                    'paid_by' => Auth::id(),
                                ]);
                            });

                            Notification::make()
                                ->title('Adiantamento marcado como pago.')
                                ->success()
                                ->send();
                        } catch (\Throwable $e) {
                            Notification::make()
                                ->title('Não foi possível marcar como pago.')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    })
                    ->visible(fn ($record) => in_array($record->status, ['draft'], true)),

                Action::make('print_pix_payment')
                    ->label('Relatório PIX')
                    ->icon('heroicon-o-qr-code')
                    ->color('success')
                    ->action(function ($record) {
                        try {
                            $service = app(SalaryAdvanceReportService::class);
                            $pdfContent = $service->output($record);

                            return response()->streamDownload(
                                fn () => print($pdfContent),
                                'adiantamento-pix-' . ($record->id ?? 'arquivo') . '.pdf'
                            );
                        } catch (\Throwable $e) {
                            Notification::make()
                                ->title('Erro ao gerar relatório PIX.')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();

                            return null;
                        }
                    }),

                Action::make('cancel_payment')
                    ->label('Cancelar')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        try {
                            if ($record->status === 'paid') {
                                throw new RuntimeException('Este adiantamento já foi pago. Cancele apenas com rotina administrativa controlada.');
                            }

                            if ($record->status === 'integrated_payroll') {
                                throw new RuntimeException('Este adiantamento já foi integrado na folha e não pode ser cancelado diretamente.');
                            }

                            $record->update([
                                'status' => 'canceled',
                            ]);

                            Notification::make()
                                ->title('Adiantamento cancelado com sucesso.')
                                ->success()
                                ->send();
                        } catch (\Throwable $e) {
                            Notification::make()
                                ->title('Erro ao cancelar adiantamento.')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    })
                    ->visible(fn ($record) => in_array($record->status, ['draft'], true)),
            ])
            ->bulkActions([
                BulkAction::make('print_pix_batch')
                    ->label('Lote PIX em PDF')
                    ->icon('heroicon-o-document-duplicate')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->action(function (Collection $records) {
                        try {
                            $service = app(SalaryAdvanceBatchReportService::class);
                            $pdfContent = $service->output($records);

                            return response()->streamDownload(
                                fn () => print($pdfContent),
                                'lote-adiantamentos-pix.pdf'
                            );
                        } catch (\Throwable $e) {
                            Notification::make()
                                ->title('Erro ao gerar lote PIX.')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();

                            return null;
                        }
                    }),

                BulkAction::make('mark_as_paid_batch')
                    ->label('Marcar selecionados como pagos')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (Collection $records) {
                        $paidCount = 0;
                        $errors = [];

                        foreach ($records as $record) {
                            try {
                                self::validateBeforePayment($record);

                                DB::transaction(function () use ($record) {
                                    $record->update([
                                        'status' => 'paid',
                                        'paid_at' => now(),
                                        'paid_by' => Auth::id(),
                                    ]);
                                });

                                $paidCount++;
                            } catch (\Throwable $e) {
                                $errors[] = 'ID ' . $record->id . ': ' . $e->getMessage();
                            }
                        }

                        if ($paidCount > 0) {
                            Notification::make()
                                ->title("{$paidCount} adiantamento(s) marcado(s) como pago.")
                                ->success()
                                ->send();
                        }

                        if (! empty($errors)) {
                            Notification::make()
                                ->title('Alguns registros não puderam ser processados.')
                                ->body(implode(' | ', array_slice($errors, 0, 3)))
                                ->warning()
                                ->send();
                        }
                    }),
            ]);
    }

    protected static function validateBeforePayment($record): void
    {
        if (! $record) {
            throw new RuntimeException('Registro inválido.');
        }

        if ($record->status === 'paid') {
            throw new RuntimeException('Este adiantamento já está pago.');
        }

        if ($record->status === 'canceled') {
            throw new RuntimeException('Adiantamento cancelado não pode ser pago.');
        }

        if ($record->status === 'integrated_payroll') {
            throw new RuntimeException('Adiantamento já integrado na folha não pode ser alterado.');
        }

        if ((float) $record->amount <= 0) {
            throw new RuntimeException('O valor do adiantamento deve ser maior que zero.');
        }

        if (blank($record->employee_id)) {
            throw new RuntimeException('O adiantamento precisa ter um colaborador vinculado.');
        }

        if (blank($record->advance_date)) {
            throw new RuntimeException('Informe a data do adiantamento antes de marcar como pago.');
        }

        if ($record->payment_method === 'pix') {
            if (blank($record->pix_key_type)) {
                throw new RuntimeException('Informe o tipo da chave PIX.');
            }

            if (blank($record->pix_key)) {
                throw new RuntimeException('Informe a chave PIX antes de marcar como pago.');
            }
        }
    }
}