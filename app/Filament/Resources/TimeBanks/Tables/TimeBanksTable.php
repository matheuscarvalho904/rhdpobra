<?php

namespace App\Filament\Resources\TimeBanks\Tables;

use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use App\Services\TimeBankService;

class TimeBanksTable
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
                    ->searchable()
                    ->sortable(),

                TextColumn::make('positive_balance_hours')
                    ->label('Saldo Positivo')
                    ->suffix('h')
                    ->numeric(decimalPlaces: 2)
                    ->sortable(),

                TextColumn::make('negative_balance_hours')
                    ->label('Saldo Negativo')
                    ->suffix('h')
                    ->numeric(decimalPlaces: 2)
                    ->sortable(),

                TextColumn::make('net_balance_hours')
                    ->label('Saldo Líquido')
                    ->suffix('h')
                    ->numeric(decimalPlaces: 2)
                    ->badge()
                    ->color(fn ($state): string => (float) $state >= 0 ? 'success' : 'danger')
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label('Ativo')
                    ->boolean(),

                TextColumn::make('last_movement_at')
                    ->label('Último Movimento')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('-')
                    ->sortable(),

                TextColumn::make('updated_at')
                    ->label('Atualizado em')
                    ->dateTime('d/m/Y H:i')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('net_balance_hours', 'desc')
            ->recordActions([
                ViewAction::make()->label('Ver Movimentos'),
            ]);
    }
}