<?php

namespace App\Filament\Resources\Users\RelationManagers;

use App\Models\Company;
use Filament\Actions\AttachAction;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CompaniesRelationManager extends RelationManager
{
    protected static string $relationship = 'companies';

    protected static ?string $title = 'Empresas Vinculadas';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Toggle::make('is_default')
                ->label('Empresa padrão'),

            Toggle::make('is_active')
                ->label('Vínculo ativo')
                ->default(true),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Empresa')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('document')
                    ->label('CNPJ')
                    ->searchable(),

                IconColumn::make('pivot.is_default')
                    ->label('Padrão')
                    ->boolean(),

                IconColumn::make('pivot.is_active')
                    ->label('Ativo')
                    ->boolean(),
            ])
            ->headerActions([
                AttachAction::make()
                    ->label('Vincular Empresa')
                    ->modalHeading('Vincular empresa ao usuário')
                    ->recordTitle(fn (Company $record): string => $record->name)
                    ->recordSelectSearchColumns(['name', 'document'])
                    ->form(fn (AttachAction $action): array => [
                        $action->getRecordSelect()
                            ->label('Empresa')
                            ->placeholder('Selecione a empresa')
                            ->searchable()
                            ->preload(),

                        Toggle::make('is_default')
                            ->label('Empresa padrão'),

                        Toggle::make('is_active')
                            ->label('Ativo')
                            ->default(true),
                    ]),
            ])
            ->recordActions([
                DeleteAction::make()
                    ->label('Remover'),
            ]);
    }
}