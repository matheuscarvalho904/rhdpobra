<?php

namespace App\Filament\Resources\Works\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class WorksTable
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
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('name')
                    ->label('Obra')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('code')
                    ->label('Código')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('client_name')
                    ->label('Cliente')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('city')
                    ->label('Cidade')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('state')
                    ->label('UF')
                    ->sortable(),

                TextColumn::make('start_date')
                    ->label('Início')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('expected_end_date')
                    ->label('Término Previsto')
                    ->date('d/m/Y')
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