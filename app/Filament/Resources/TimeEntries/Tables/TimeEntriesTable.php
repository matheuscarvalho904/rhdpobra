<?php

namespace App\Filament\Resources\TimeEntries\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TimeEntriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('entry_date')
                    ->label('Data')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('employee.registration_number')
                    ->label('Matrícula')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('employee.name')
                    ->label('Colaborador')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('company.name')
                    ->label('Empresa')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('work.name')
                    ->label('Obra')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('attendanceOccurrence.name')
                    ->label('Ocorrência')
                    ->placeholder('-')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('entry_1')
                    ->label('Entrada 1')
                    ->time('H:i')
                    ->toggleable(),

                TextColumn::make('exit_1')
                    ->label('Saída 1')
                    ->time('H:i')
                    ->toggleable(),

                TextColumn::make('entry_2')
                    ->label('Entrada 2')
                    ->time('H:i')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('exit_2')
                    ->label('Saída 2')
                    ->time('H:i')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('worked_minutes')
                    ->label('Trabalhado (min)')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('overtime_minutes')
                    ->label('Extra (min)')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('lateness_minutes')
                    ->label('Atraso (min)')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('absence_minutes')
                    ->label('Falta (min)')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('night_minutes')
                    ->label('Noturno (min)')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('source')
                    ->label('Origem')
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => match ($state) {
                        'manual' => 'Manual',
                        'import' => 'Importação',
                        'integration' => 'Integração',
                        default => $state,
                    }),

                IconColumn::make('is_manual')
                    ->label('Manual')
                    ->boolean(),

                TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }
}