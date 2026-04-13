<?php

namespace App\Filament\Resources\UserAccessScopes\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UserAccessScopesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('Usuário')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('company.name')
                    ->label('Empresa')
                    ->placeholder('-')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('branch.name')
                    ->label('Filial')
                    ->placeholder('-')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('work.name')
                    ->label('Obra')
                    ->placeholder('-')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('department.name')
                    ->label('Departamento')
                    ->placeholder('-')
                    ->searchable()
                    ->sortable(),

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