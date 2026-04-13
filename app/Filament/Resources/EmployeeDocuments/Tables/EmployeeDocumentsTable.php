<?php

namespace App\Filament\Resources\EmployeeDocuments\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class EmployeeDocumentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('employee.name')
                    ->label('Colaborador')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('documentType.name')
                    ->label('Tipo')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('document_number')
                    ->label('Número')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('issue_date')
                    ->label('Emissão')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('expiration_date')
                    ->label('Validade')
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