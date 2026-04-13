<?php

namespace App\Filament\Resources\EmployeeSalaryHistories\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class EmployeeSalaryHistoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('employee.name')
                    ->label('Colaborador')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('salary_type')
                    ->label('Tipo Salarial')
                    ->formatStateUsing(fn (?string $state) => match ($state) {
                        'monthly' => 'Mensalista',
                        'hourly' => 'Horista',
                        'daily' => 'Diarista',
                        default => $state,
                    })
                    ->sortable(),

                TextColumn::make('previous_salary')
                    ->label('Salário Anterior')
                    ->money('BRL')
                    ->sortable(),

                TextColumn::make('new_salary')
                    ->label('Novo Salário')
                    ->money('BRL')
                    ->sortable(),

                TextColumn::make('effective_date')
                    ->label('Vigência')
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