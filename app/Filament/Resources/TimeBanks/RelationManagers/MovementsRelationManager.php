<?php

namespace App\Filament\Resources\TimeBanks\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MovementsRelationManager extends RelationManager
{
    protected static string $relationship = 'movements';

    protected static ?string $title = 'Extrato do Banco de Horas';

    public function form(Schema $schema): Schema
    {
        return $schema;
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('movement_date')
                    ->label('Data')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'credit' => 'Crédito',
                        'debit' => 'Débito',
                        'adjustment' => 'Ajuste',
                        'payout' => 'Pagamento',
                        'expiration' => 'Expiração',
                        default => ucfirst((string) $state),
                    })
                    ->color(fn ($state) => match ($state) {
                        'credit' => 'success',
                        'debit', 'expiration' => 'danger',
                        'payout' => 'warning',
                        'adjustment' => 'info',
                        default => 'gray',
                    }),

                TextColumn::make('origin')
                    ->label('Origem')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'manual' => 'Manual',
                        'time_closing' => 'Fechamento',
                        'payroll' => 'Folha',
                        'expiration' => 'Expiração',
                        'adjustment' => 'Ajuste',
                        default => ucfirst((string) $state),
                    }),

                TextColumn::make('hours')
                    ->label('Horas')
                    ->numeric(decimalPlaces: 2)
                    ->suffix(' h'),

                TextColumn::make('balance_after')
                    ->label('Saldo Após')
                    ->numeric(decimalPlaces: 2)
                    ->suffix(' h')
                    ->badge()
                    ->color(fn ($state): string => match (true) {
                        (float) $state > 0 => 'success',
                        (float) $state < 0 => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('description')
                    ->label('Descrição')
                    ->wrap()
                    ->limit(80),

                TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('movement_date', 'desc');
    }
}