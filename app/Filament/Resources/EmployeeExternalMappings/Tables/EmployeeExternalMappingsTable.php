<?php

namespace App\Filament\Resources\EmployeeExternalMappings\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class EmployeeExternalMappingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('employee.name')
                    ->label('Colaborador ERP')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('provider')
                    ->label('Sistema')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'solides' => 'Sólides/Tangerino',
                        default => $state ?: '-',
                    }),

                TextColumn::make('external_employee_id')
                    ->label('ID Sólides')
                    ->searchable()
                    ->copyable(),

                TextColumn::make('external_code')
                    ->label('Código Externo')
                    ->searchable()
                    ->placeholder('-')
                    ->copyable(),

                TextColumn::make('external_name')
                    ->label('Nome Externo')
                    ->searchable()
                    ->placeholder('-'),

                TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('id', 'desc')
            ->recordActions([
                EditAction::make()->label('Editar'),
                DeleteAction::make()->label('Excluir'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->label('Excluir selecionados'),
                ]),
            ]);
    }
}