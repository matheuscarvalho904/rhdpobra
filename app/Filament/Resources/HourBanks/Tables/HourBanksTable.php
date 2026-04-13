<?php

namespace App\Filament\Resources\HourBanks\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class HourBanksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('employee.code')
                    ->label('Matrícula')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('employee.name')
                    ->label('Colaborador')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('employee.work.name')
                    ->label('Obra')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('balance_minutes')
                    ->label('Saldo (min)')
                    ->sortable(),

                TextColumn::make('formatted_balance')
                    ->label('Saldo (hh:mm)')
                    ->sortable(query: fn ($query, $direction) => $query->orderBy('balance_minutes', $direction)),

                TextColumn::make('last_calculated_at')
                    ->label('Último Cálculo')
                    ->dateTime('d/m/Y H:i')
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