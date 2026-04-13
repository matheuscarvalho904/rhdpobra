<?php

namespace App\Filament\Resources\WorkShifts\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class WorkShiftsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Jornada')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('code')
                    ->label('Código')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('weekly_workload')
                    ->label('Carga Semanal')
                    ->sortable(),

                TextColumn::make('monthly_workload')
                    ->label('Carga Mensal')
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label('Ativo')
                    ->boolean(),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }
}