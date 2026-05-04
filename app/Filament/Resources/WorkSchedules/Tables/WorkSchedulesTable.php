<?php

namespace App\Filament\Resources\WorkSchedules\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class WorkSchedulesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('company.name')
                    ->label('Empresa')
                    ->placeholder('Todas')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('name')
                    ->label('Jornada')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('code')
                    ->label('Código')
                    ->searchable()
                    ->placeholder('-')
                    ->toggleable(),

                TextColumn::make('schedule_type')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'fixed' => 'Fixa',
                        'flexible' => 'Flexível',
                        'shift' => 'Turno',
                        '12x36' => '12x36',
                        'custom' => 'Personalizada',
                        default => $state ?: '-',
                    }),

                TextColumn::make('weekly_hours')
                    ->label('Semanal')
                    ->suffix('h')
                    ->sortable(),

                TextColumn::make('monthly_hours')
                    ->label('Mensal')
                    ->suffix('h')
                    ->sortable(),

                IconColumn::make('works_on_holidays')
                    ->label('Feriado')
                    ->boolean(),

                IconColumn::make('uses_time_bank')
                    ->label('Banco')
                    ->boolean(),

                IconColumn::make('is_active')
                    ->label('Ativa')
                    ->boolean(),

                TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('name')
            ->recordActions([
                EditAction::make()->label('Editar'),
                DeleteAction::make()->label('Excluir'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->label('Excluir selecionadas'),
                ]),
            ]);
    }
}