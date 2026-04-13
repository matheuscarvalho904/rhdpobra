<?php

namespace App\Filament\Resources\Payslips\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PayslipsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('payrollRun.id')
                    ->label('Proc.')
                    ->sortable(),

                TextColumn::make('employee.registration_number')
                    ->label('Matrícula')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('employee.name')
                    ->label('Colaborador')
                    ->searchable()
                    ->sortable(),

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
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('total_gross')
                    ->label('Bruto')
                    ->formatStateUsing(fn ($state) => 'R$ ' . number_format((float) $state, 2, ',', '.'))
                    ->sortable(),

                TextColumn::make('deduction_total')
                    ->label('Descontos')
                    ->formatStateUsing(fn ($state) => 'R$ ' . number_format((float) $state, 2, ',', '.'))
                    ->sortable(),

                TextColumn::make('net_total')
                    ->label('Líquido')
                    ->formatStateUsing(fn ($state) => 'R$ ' . number_format((float) $state, 2, ',', '.'))
                    ->sortable(),

                TextColumn::make('base_inss')
                    ->label('Base INSS')
                    ->formatStateUsing(fn ($state) => 'R$ ' . number_format((float) $state, 2, ',', '.'))
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('base_fgts')
                    ->label('Base FGTS')
                    ->formatStateUsing(fn ($state) => 'R$ ' . number_format((float) $state, 2, ',', '.'))
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('base_irrf')
                    ->label('Base IRRF')
                    ->formatStateUsing(fn ($state) => 'R$ ' . number_format((float) $state, 2, ',', '.'))
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('printed_at')
                    ->label('Impresso em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('sent_at')
                    ->label('Enviado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(),

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