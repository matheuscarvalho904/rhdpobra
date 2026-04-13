<?php

namespace App\Filament\Resources\EmployeeTransfers\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class EmployeeTransfersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('employee.name')
                    ->label('Colaborador')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('oldWork.name')
                    ->label('Obra Anterior')
                    ->placeholder('-')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('newWork.name')
                    ->label('Nova Obra')
                    ->placeholder('-')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('oldDepartment.name')
                    ->label('Depto. Anterior')
                    ->placeholder('-')
                    ->toggleable(),

                TextColumn::make('newDepartment.name')
                    ->label('Novo Depto.')
                    ->placeholder('-')
                    ->toggleable(),

                TextColumn::make('transfer_date')
                    ->label('Data')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('reason')
                    ->label('Motivo')
                    ->searchable()
                    ->toggleable(),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }
}