<?php

namespace App\Filament\Resources\PayrollAccountMappings\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PayrollAccountMappingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('company.name')
                    ->label('Empresa')
                    ->searchable(),

                TextColumn::make('work.name')
                    ->label('Obra')
                    ->searchable(),

                TextColumn::make('event_code')
                    ->label('Código'),

                TextColumn::make('event_type')
                    ->label('Tipo')
                    ->badge(),

                TextColumn::make('debit_account')
                    ->label('Débito'),

                TextColumn::make('credit_account')
                    ->label('Crédito'),

                TextColumn::make('history_template')
                    ->label('Histórico')
                    ->limit(40),

                TextColumn::make('is_active')
                    ->label('Ativo')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state ? 'Sim' : 'Não'),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }
}