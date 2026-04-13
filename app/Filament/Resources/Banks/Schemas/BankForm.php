<?php

namespace App\Filament\Resources\Banks\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class BankForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Dados do Banco')
                    
                    ->schema([
                        TextInput::make('code')
                            ->label('Código')
                            ->maxLength(10),

                        TextInput::make('name')
                            ->label('Nome')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('full_name')
                            ->label('Nome Completo')
                            ->maxLength(255),

                        Toggle::make('is_active')
                            ->label('Ativo')
                            ->default(true),
                    ]),
            ]);
    }
}