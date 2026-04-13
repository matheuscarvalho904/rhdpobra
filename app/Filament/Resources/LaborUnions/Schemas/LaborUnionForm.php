<?php

namespace App\Filament\Resources\LaborUnions\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class LaborUnionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Dados do Sindicato')
                    
                    ->schema([
                        TextInput::make('name')
                            ->label('Nome')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('code')
                            ->label('Código')
                            ->maxLength(30),

                        TextInput::make('document')
                            ->label('CNPJ')
                            ->maxLength(20),

                        TextInput::make('phone')
                            ->label('Telefone')
                            ->maxLength(20),

                        TextInput::make('email')
                            ->label('E-mail')
                            ->email()
                            ->maxLength(255),

                        Toggle::make('is_active')
                            ->label('Ativo')
                            ->default(true),
                    ]),
            ]);
    }
}