<?php

namespace App\Filament\Resources\TimeEntries\Tables;

use App\Models\TimeEntry;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TimeEntriesTable
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

                TextColumn::make('employee.name')
                    ->label('Colaborador')
                    ->searchable()
                    ->sortable()
                    ->placeholder('Não vinculado'),

                TextColumn::make('entry_date')
                    ->label('Data')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('entry_datetime')
                    ->label('Marcação')
                    ->dateTime('d/m/Y H:i:s')
                    ->sortable(),

                TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'entrada', 'in', 'IN', 'ENTRY' => 'Entrada',
                        'saida', 'saída', 'out', 'OUT', 'EXIT' => 'Saída',
                        'unknown' => 'Não identificado',
                        default => $state ?: 'Não identificado',
                    })
                    ->color(fn (?string $state): string => match ($state) {
                        'entrada', 'in', 'IN', 'ENTRY' => 'success',
                        'saida', 'saída', 'out', 'OUT', 'EXIT' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'valid' => 'Válida',
                        'pending' => 'Pendente',
                        'ignored' => 'Ignorada',
                        'adjusted' => 'Ajustada',
                        default => $state ?: '-',
                    })
                    ->color(fn (?string $state): string => match ($state) {
                        'valid' => 'success',
                        'pending' => 'warning',
                        'ignored' => 'gray',
                        'adjusted' => 'info',
                        default => 'gray',
                    }),

                TextColumn::make('provider')
                    ->label('Origem')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'solides' => 'Sólides',
                        default => $state ?: '-',
                    }),

                TextColumn::make('external_employee_id')
                    ->label('ID Externo Colaborador')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('external_id')
                    ->label('ID Externo Marcação')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Importado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('company_id')
                    ->label('Empresa')
                    ->relationship('company', 'name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('employee_id')
                    ->label('Colaborador')
                    ->relationship('employee', 'name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'valid' => 'Válida',
                        'pending' => 'Pendente',
                        'ignored' => 'Ignorada',
                        'adjusted' => 'Ajustada',
                    ]),

                SelectFilter::make('provider')
                    ->label('Origem')
                    ->options([
                        'solides' => 'Sólides',
                    ]),

                Filter::make('entry_date')
                    ->label('Período')
                    ->form([
                        DatePicker::make('from')
                            ->label('Data inicial'),

                        DatePicker::make('until')
                            ->label('Data final'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'] ?? null,
                                fn (Builder $query, $date): Builder => $query->whereDate('entry_date', '>=', $date),
                            )
                            ->when(
                                $data['until'] ?? null,
                                fn (Builder $query, $date): Builder => $query->whereDate('entry_date', '<=', $date),
                            );
                    }),
            ])
            ->defaultSort('entry_datetime', 'desc')
            ->recordActions([
                Action::make('visualizar')
                    ->label('Visualizar')
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->modalHeading('Detalhes da Marcação')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Fechar')
                    ->modalContent(fn (TimeEntry $record) => view(
                        'filament.resources.time-entries.view-entry',
                        ['record' => $record]
                    )),
            ]);
    }
}