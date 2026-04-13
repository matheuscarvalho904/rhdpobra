<?php

namespace App\Filament\Resources\EmployeeDependents\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class EmployeeDependentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('employee.name')
                    ->label('Colaborador')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('name')
                    ->label('Dependente')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('relationship')
                    ->label('Parentesco')
                    ->formatStateUsing(fn (?string $state) => match ($state) {
                        'child' => 'Filho(a)',
                        'spouse' => 'Cônjuge',
                        'partner' => 'Companheiro(a)',
                        'stepchild' => 'Enteado(a)',
                        'father' => 'Pai',
                        'mother' => 'Mãe',
                        'sibling' => 'Irmão(ã)',
                        'other' => 'Outros',
                        default => $state,
                    })
                    ->sortable(),

                TextColumn::make('cpf')
                    ->label('CPF')
                    ->searchable()
                    ->toggleable(),

                IconColumn::make('is_ir_dependent')
                    ->label('IR')
                    ->boolean(),

                IconColumn::make('is_family_allowance_dependent')
                    ->label('Sal. Família')
                    ->boolean(),

                IconColumn::make('is_active')
                    ->label('Ativo')
                    ->boolean(),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }
}