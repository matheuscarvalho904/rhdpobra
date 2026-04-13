<?php

namespace App\Filament\Resources\FinancialCategories\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class FinancialCategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Dados da Categoria Financeira')
                
                    ->schema([
                        TextInput::make('name')
                            ->label('Nome')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('code')
                            ->label('Código')
                            ->maxLength(30),

                        Select::make('type')
                            ->label('Tipo')
                            ->options([
                                'expense' => 'Despesa',
                                'income' => 'Receita',
                            ])
                            ->required(),

                        Toggle::make('is_active')
                            ->label('Ativo')
                            ->default(true),
                    ]),
            ]);
    }
}