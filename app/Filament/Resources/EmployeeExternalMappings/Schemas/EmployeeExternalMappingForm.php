<?php

namespace App\Filament\Resources\EmployeeExternalMappings\Schemas;

use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class EmployeeExternalMappingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Vínculo do Colaborador')
                ->schema([
                    Select::make('employee_id')
                        ->label('Colaborador do ERP')
                        ->relationship('employee', 'name')
                        ->searchable()
                        ->preload()
                        ->required(),

                    Select::make('provider')
                        ->label('Sistema Externo')
                        ->options([
                            'solides' => 'Sólides/Tangerino',
                        ])
                        ->default('solides')
                        ->required(),

                    TextInput::make('external_employee_id')
                        ->label('ID do Colaborador na Sólides')
                        ->placeholder('Ex: 6409608')
                        ->required()
                        ->maxLength(255),

                    TextInput::make('external_code')
                        ->label('Código Externo')
                        ->placeholder('Opcional')
                        ->maxLength(255),

                    TextInput::make('external_name')
                        ->label('Nome na Sólides')
                        ->placeholder('Opcional')
                        ->maxLength(255),
                ])
                ->columns(2),

            Section::make('Dados Técnicos')
                ->collapsed()
                ->schema([
                    KeyValue::make('metadata')
                        ->label('Metadata')
                        ->keyLabel('Chave')
                        ->valueLabel('Valor'),
                ]),
        ]);
    }
}