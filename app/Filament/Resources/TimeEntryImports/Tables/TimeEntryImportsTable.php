<?php

namespace App\Filament\Resources\TimeEntryImports\Tables;

use App\Models\TimeEntryImport;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TimeEntryImportsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('company.name')->label('Empresa')->searchable()->sortable()->placeholder('-'),
                TextColumn::make('start_date')->label('Data Inicial')->date('d/m/Y')->sortable(),
                TextColumn::make('end_date')->label('Data Final')->date('d/m/Y')->sortable(),

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

                TextColumn::make('total_records')->label('Total')->sortable(),
                TextColumn::make('imported_records')->label('Importados')->sortable(),
                TextColumn::make('ignored_records')->label('Ignorados')->sortable(),
                TextColumn::make('error_message')->label('Erro')->limit(60)->placeholder('-')->toggleable(),
                TextColumn::make('created_at')->label('Importado em')->dateTime('d/m/Y H:i')->sortable(),
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
                    ->modalDescription(
                        'O período será importado novamente em páginas curtas, evitando timeout.'
                    )
                    ->modalSubmitActionLabel('Reprocessar')
                    ->url(fn (TimeEntryImport $record): string => route(
                        'time-entry-imports.reprocess.show',
                        $record
                    )),
            ]);
    }
}
