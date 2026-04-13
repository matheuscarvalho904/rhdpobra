<?php

namespace App\Filament\Resources\PayrollEvents\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PayrollEventsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('Código')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => match ($state) {
                        'earning' => 'Provento',
                        'deduction' => 'Desconto',
                        'base' => 'Base',
                        'informational' => 'Informativo',
                        default => $state,
                    })
                    ->sortable(),

                TextColumn::make('calculation_type')
                    ->label('Cálculo')
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => match ($state) {
                        'fixed' => 'Valor Fixo',
                        'percentage' => 'Percentual',
                        'quantity_x_value' => 'Qtd x Valor',
                        'manual' => 'Manual',
                        'automatic' => 'Automático',
                        default => $state,
                    })
                    ->sortable(),

                IconColumn::make('affects_inss')
                    ->label('INSS')
                    ->boolean(),

                IconColumn::make('affects_fgts')
                    ->label('FGTS')
                    ->boolean(),

                IconColumn::make('affects_irrf')
                    ->label('IRRF')
                    ->boolean(),

                IconColumn::make('affects_net')
                    ->label('Líquido')
                    ->boolean(),

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