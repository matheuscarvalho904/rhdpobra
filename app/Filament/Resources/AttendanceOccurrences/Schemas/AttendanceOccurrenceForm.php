<?php

namespace App\Filament\Resources\AttendanceOccurrences\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AttendanceOccurrenceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Dados da Ocorrência')
                    
                    ->schema([
                        TextInput::make('name')
                            ->label('Nome')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('code')
                            ->label('Código')
                            ->required()
                            ->maxLength(50)
                            ->unique(ignoreRecord: true),

                        Toggle::make('affects_payroll')
                            ->label('Afeta a Folha')
                            ->default(false),

                        Toggle::make('affects_hour_bank')
                            ->label('Afeta Banco de Horas')
                            ->default(false),

                        Toggle::make('is_active')
                            ->label('Ativo')
                            ->default(true),

                        Textarea::make('description')
                            ->label('Descrição')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}