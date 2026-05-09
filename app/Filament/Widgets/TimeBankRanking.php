<?php

namespace App\Filament\Widgets;

use App\Models\TimeBank;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class TimeBankRanking extends TableWidget
{
    protected static ?string $heading = 'Ranking Banco de Horas';

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                TimeBank::query()
                    ->with(['employee', 'company'])
                    ->orderByDesc('net_balance_hours')
            )
            ->columns([
                TextColumn::make('employee.name')
                    ->label('Colaborador')
                    ->searchable(),

                TextColumn::make('company.name')
                    ->label('Empresa'),

                TextColumn::make('positive_balance_hours')
                    ->label('Positivo')
                    ->numeric(decimalPlaces: 2)
                    ->suffix(' h'),

                TextColumn::make('negative_balance_hours')
                    ->label('Negativo')
                    ->numeric(decimalPlaces: 2)
                    ->suffix(' h'),

                TextColumn::make('net_balance_hours')
                    ->label('Saldo')
                    ->badge()
                    ->numeric(decimalPlaces: 2)
                    ->suffix(' h')
                    ->color(fn ($state) => match (true) {
                        (float) $state > 0 => 'success',
                        (float) $state < 0 => 'danger',
                        default => 'gray',
                    }),
            ]);
    }
}