<?php

namespace App\Filament\Resources\EmployeeFixedEvents\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class EmployeeFixedEventsTable
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

                TextColumn::make('start_date')
                    ->label('Início')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('end_date')
                    ->label('Fim')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(),

                IconColumn::make('is_active')
                    ->label('Ativo')
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