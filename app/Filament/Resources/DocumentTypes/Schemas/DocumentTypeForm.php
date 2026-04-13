<?php

namespace App\Filament\Resources\DocumentTypes\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class DocumentTypeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Dados do Tipo de Documento')
                
                    ->schema([
                        TextInput::make('name')
                            ->label('Nome')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('code')
                            ->label('Código')
                            ->maxLength(30),

                        Toggle::make('requires_expiration')
                            ->label('Exige Vencimento')
                            ->default(false)
                            ->inline(false),

                        Toggle::make('is_active')
                            ->label('Ativo')
                            ->default(true)
                            ->inline(false),
                    ]),
            ]);
    }
}