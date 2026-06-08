<?php

namespace App\Filament\Resources\TimeEntries\Tables;

use App\Models\TimeEntry;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
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
                    ->placeholder('-'),

                TextColumn::make('entry_date')
                    ->label('Data')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('entry_datetime')
                    ->label('Horário')
                    ->dateTime('H:i')
                    ->sortable(),

                TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'entrada' => 'Entrada',
                        'saida' => 'Saída',
                        default => $state ?: '-',
                    })
                    ->color(fn (?string $state): string => match ($state) {
                        'entrada' => 'success',
                        'saida' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('provider')
                    ->label('Provedor')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'solides' => 'Sólides',
                        'manual' => 'Manual',
                        default => $state ?: '-',
                    })
                    ->color(fn (?string $state): string => match ($state) {
                        'manual' => 'warning',
                        'solides' => 'info',
                        default => 'gray',
                    }),

                TextColumn::make('source')
                    ->label('Fonte')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'api' => 'API',
                        'manual' => 'Manual',
                        default => $state ?: '-',
                    }),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'valid' => 'Válido',
                        'invalid' => 'Inválido',
                        default => $state ?: '-',
                    })
                    ->color(fn (?string $state): string => match ($state) {
                        'valid' => 'success',
                        'invalid' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('notes')
                    ->label('Observação')
                    ->limit(60)
                    ->placeholder('-')
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('company_id')
                    ->relationship('company', 'name')
                    ->label('Empresa')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('employee_id')
                    ->relationship('employee', 'name')
                    ->label('Colaborador')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('type')
                    ->label('Tipo')
                    ->options([
                        'entrada' => 'Entrada',
                        'saida' => 'Saída',
                    ]),

                SelectFilter::make('provider')
                    ->label('Origem')
                    ->options([
                        'solides' => 'Sólides',
                        'manual' => 'Manual',
                    ]),

                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'valid' => 'Válido',
                        'invalid' => 'Inválido',
                    ]),

                Filter::make('periodo')
                    ->label('Período')
                    ->form([
                        DatePicker::make('start_date')
                            ->label('Data Inicial')
                            ->native(false)
                            ->displayFormat('d/m/Y'),

                        DatePicker::make('end_date')
                            ->label('Data Final')
                            ->native(false)
                            ->displayFormat('d/m/Y'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['start_date'] ?? null,
                                fn (Builder $query, $date): Builder => $query->whereDate('entry_date', '>=', $date),
                            )
                            ->when(
                                $data['end_date'] ?? null,
                                fn (Builder $query, $date): Builder => $query->whereDate('entry_date', '<=', $date),
                            );
                    }),
            ])
            ->defaultSort('entry_datetime', 'desc')
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
