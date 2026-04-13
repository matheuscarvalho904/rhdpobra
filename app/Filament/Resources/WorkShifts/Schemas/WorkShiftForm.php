<?php

namespace App\Filament\Resources\WorkShifts\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class WorkShiftForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Dados da Jornada')
                
                    ->schema([
                        TextInput::make('name')
                            ->label('Nome')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('code')
                            ->label('Código')
                            ->maxLength(30),

                        TextInput::make('weekly_workload')
                            ->label('Carga Horária Semanal')
                            ->numeric()
                            ->required(),

                        TextInput::make('monthly_workload')
                            ->label('Carga Horária Mensal')
                            ->numeric()
                            ->required(),

                        Toggle::make('is_active')
                            ->label('Ativo')
                            ->default(true),
                    ]),
            ]);
    }
}