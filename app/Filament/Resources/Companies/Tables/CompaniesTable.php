<?php

namespace App\Filament\Resources\Companies\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CompaniesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Razão Social')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('trade_name')
                    ->label('Nome Fantasia')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('code')
                    ->label('Código')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('document')
                    ->label('CNPJ')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('city')
                    ->label('Cidade')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('state')
                    ->label('UF')
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