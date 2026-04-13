<?php

namespace App\Filament\Resources\Departments\Schemas;

use App\Models\Company;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class DepartmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Dados do Departamento')
                    
                    ->schema([
                        Select::make('company_id')
                            ->label('Empresa')
                            ->options(Company::query()->where('is_active', true)->pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->placeholder('Global / Todas'),

                        TextInput::make('name')
                            ->label('Nome')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('code')
                            ->label('Código')
                            ->maxLength(30),

                        Toggle::make('is_active')
                            ->label('Ativo')
                            ->default(true)
                            ->inline(false),

                        Textarea::make('description')
                            ->label('Descrição')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}