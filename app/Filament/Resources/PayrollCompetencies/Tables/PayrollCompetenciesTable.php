<?php

namespace App\Filament\Resources\PayrollCompetencies\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PayrollCompetenciesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('company.name')
                    ->label('Empresa')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('branch.name')
                    ->label('Filial')
                    ->placeholder('-')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('month')
                    ->label('Mês')
                    ->formatStateUsing(fn ($state) => str_pad((string) $state, 2, '0', STR_PAD_LEFT))
                    ->sortable(),

                TextColumn::make('year')
                    ->label('Ano')
                    ->sortable(),

                TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => match ($state) {
                        'monthly' => 'Mensal',
                        'vacation' => 'Férias',
                        'thirteenth' => '13º',
                        'termination' => 'Rescisão',
                        'advance' => 'Adiantamento',
                        default => $state,
                    })
                    ->sortable(),

                TextColumn::make('period_start')
                    ->label('Período Inicial')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('period_end')
                    ->label('Período Final')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('payment_date')
                    ->label('Pagamento')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => match ($state) {
                        'open' => 'Aberto',
                        'processing' => 'Processando',
                        'calculated' => 'Calculado',
                        'reviewed' => 'Conferido',
                        'closed' => 'Fechado',
                        'canceled' => 'Cancelado',
                        default => $state,
                    })
                    ->sortable(),

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