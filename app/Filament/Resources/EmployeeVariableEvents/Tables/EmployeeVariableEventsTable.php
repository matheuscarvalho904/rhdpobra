<?php

namespace App\Filament\Resources\EmployeeVariableEvents\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class EmployeeVariableEventsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('employee.code')
                    ->label('Matrícula')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('employee.name')
                    ->label('Colaborador')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('competency.month')
                    ->label('Mês')
                    ->formatStateUsing(fn ($state) => str_pad((string) $state, 2, '0', STR_PAD_LEFT))
                    ->sortable(),

                TextColumn::make('competency.year')
                    ->label('Ano')
                    ->sortable(),

                TextColumn::make('payrollEvent.name')
                    ->label('Evento')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('amount')
                    ->label('Valor')
                    ->formatStateUsing(fn ($state) => $state !== null ? 'R$ ' . number_format((float) $state, 2, ',', '.') : '-')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('percentage')
                    ->label('Percentual')
                    ->formatStateUsing(fn ($state) => $state !== null ? number_format((float) $state, 4, ',', '.') . '%' : '-')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('quantity')
                    ->label('Quantidade')
                    ->formatStateUsing(fn ($state) => $state !== null ? number_format((float) $state, 4, ',', '.') : '-')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('reference')
                    ->label('Referência')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('createdBy.name')
                    ->label('Criado por')
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),

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