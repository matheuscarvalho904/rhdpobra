<?php

namespace App\Filament\Resources\TimeEntryImports\Tables;

use App\Models\TimeEntryImport;
use App\Services\Integrations\Solides\SolidesPunchImportService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TimeEntryImportsTable
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

                TextColumn::make('start_date')
                    ->label('Data Inicial')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('end_date')
                    ->label('Data Final')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'completed' => 'Concluída',
                        'processing' => 'Processando',
                        'failed' => 'Falhou',
                        'pending' => 'Pendente',
                        default => $state ?: '-',
                    })
                    ->color(fn (?string $state): string => match ($state) {
                        'completed' => 'success',
                        'processing' => 'warning',
                        'failed' => 'danger',
                        'pending' => 'gray',
                        default => 'gray',
                    }),

                TextColumn::make('total_records')
                    ->label('Total')
                    ->sortable(),

                TextColumn::make('imported_records')
                    ->label('Importados')
                    ->sortable(),

                TextColumn::make('ignored_records')
                    ->label('Ignorados')
                    ->sortable(),

                TextColumn::make('error_message')
                    ->label('Erro')
                    ->limit(60)
                    ->placeholder('-')
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Importado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                Action::make('ver')
                    ->label('Ver')
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->modalHeading('Detalhes da Importação')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Fechar')
                    ->modalContent(fn (TimeEntryImport $record) => view(
                        'filament.resources.time-entry-imports.view',
                        ['record' => $record]
                    )),

                Action::make('reprocessar')
                    ->label('Reprocessar')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Reprocessar Importação')
                    ->modalDescription('Deseja reprocessar este período novamente?')
                    ->modalSubmitActionLabel('Reprocessar')
                    ->action(function (TimeEntryImport $record): void {
                        $integration = $record->pointIntegration;

                        if (! $integration) {
                            Notification::make()
                                ->title('Integração não encontrada')
                                ->danger()
                                ->send();

                            return;
                        }

                        $import = app(SolidesPunchImportService::class)
                            ->import(
                                $integration,
                                $record->start_date->format('Y-m-d'),
                                $record->end_date->format('Y-m-d')
                            );

                        if ($import->status === 'completed') {
                            Notification::make()
                                ->title('Importação reprocessada')
                                ->body("Total: {$import->total_records} | Importados: {$import->imported_records} | Ignorados: {$import->ignored_records}")
                                ->success()
                                ->send();

                            return;
                        }

                        Notification::make()
                            ->title('Falha ao reprocessar')
                            ->body($import->error_message ?? 'Erro desconhecido.')
                            ->danger()
                            ->send();
                    }),
            ]);
    }
}