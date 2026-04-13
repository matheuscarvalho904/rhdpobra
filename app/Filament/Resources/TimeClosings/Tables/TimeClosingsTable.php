<?php

namespace App\Filament\Resources\TimeClosings\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TimeClosingsTable
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

                TextColumn::make('work.name')
                    ->label('Obra')
                    ->placeholder('-')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('period_start')
                    ->label('Período Inicial')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('period_end')
                    ->label('Período Final')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => match ($state) {
                        'open' => 'Aberto',
                        'processing' => 'Processando',
                        'reviewed' => 'Conferido',
                        'approved' => 'Aprovado',
                        'closed' => 'Fechado',
                        'integrated_to_payroll' => 'Integrado à Folha',
                        default => $state,
                    }),

                TextColumn::make('processedBy.name')
                    ->label('Processado por')
                    ->placeholder('-')
                    ->toggleable(),

                TextColumn::make('approvedBy.name')
                    ->label('Aprovado por')
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('closedBy.name')
                    ->label('Fechado por')
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