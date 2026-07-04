<?php

namespace App\Filament\Resources\EmployeeVariableEvents\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class EmployeeVariableEventsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('employee.code')
                    ->label('Matrícula')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('employee.name')
                    ->label('Colaborador')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('competency.description')
                    ->label('Competência')
                    ->searchable()
                    ->sortable()
                    ->placeholder('-'),

                TextColumn::make('payrollEvent.name')
                    ->label('Evento')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('amount')
                    ->label('Valor')
                    ->formatStateUsing(fn ($state) => $state !== null ? 'R$ ' . number_format((float) $state, 2, ',', '.') : '-')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('percentage')
                    ->label('Percentual')
                    ->formatStateUsing(fn ($state) => $state !== null ? number_format((float) $state, 4, ',', '.') . '%' : '-')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('quantity')
                    ->label('Quantidade')
                    ->formatStateUsing(fn ($state) => $state !== null ? number_format((float) $state, 4, ',', '.') : '-')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('reference')
                    ->label('Referência')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('notes')
                    ->label('Observações')
                    ->limit(40)
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('createdBy.name')
                    ->label('Criado por')
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('employee_id')
                    ->label('Colaborador')
                    ->relationship('employee', 'name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('payroll_competency_id')
                    ->label('Competência')
                    ->relationship('competency', 'description')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('payroll_event_id')
                    ->label('Evento')
                    ->relationship('payrollEvent', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                EditAction::make()
                    ->label('Editar'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Excluir selecionados')
                        ->modalHeading('Excluir eventos variáveis selecionados')
                        ->modalDescription('Confirma a exclusão dos eventos variáveis selecionados? Esta ação não poderá ser desfeita.')
                        ->modalSubmitActionLabel('Sim, excluir')
                        ->successNotificationTitle('Eventos variáveis excluídos com sucesso'),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
