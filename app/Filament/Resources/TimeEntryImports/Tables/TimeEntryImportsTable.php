<?php

namespace App\Filament\Resources\TimeEntryImports\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TimeEntryImportsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('file_name')
                    ->label('Arquivo')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('company.name')
                    ->label('Empresa')
                    ->placeholder('-')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

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
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => match ($state) {
                        'pending' => 'Pendente',
                        'processing' => 'Processando',
                        'completed' => 'Concluído',
                        'completed_with_errors' => 'Concluído com Erros',
                        'failed' => 'Falhou',
                        default => $state,
                    }),

                TextColumn::make('imported_rows')
                    ->label('Importadas')
                    ->sortable(),

                TextColumn::make('valid_rows')
                    ->label('Válidas')
                    ->sortable(),

                TextColumn::make('invalid_rows')
                    ->label('Inválidas')
                    ->sortable(),

                TextColumn::make('importedBy.name')
                    ->label('Importado por')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }
}