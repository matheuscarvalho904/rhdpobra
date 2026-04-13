<?php

namespace App\Filament\Resources\Holidays\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class HolidaysTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('holidayType.name')
                    ->label('Tipo')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('name')
                    ->label('Feriado')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('holiday_date')
                    ->label('Data')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('state')
                    ->label('UF')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('city')
                    ->label('Cidade')
                    ->sortable()
                    ->toggleable(),

                IconColumn::make('is_active')
                    ->label('Ativo')
                    ->boolean(),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }
}