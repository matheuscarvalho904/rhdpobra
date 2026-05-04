<?php

namespace App\Filament\Resources\EmployeeWorkSchedules\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class EmployeeWorkScheduleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Vínculo da Jornada')
                ->schema([
                    Select::make('employee_id')
                        ->label('Colaborador')
                        ->relationship('employee', 'name')
                        ->searchable()
                        ->preload()
                        ->required(),

                    Select::make('work_schedule_id')
                        ->label('Jornada')
                        ->relationship('workSchedule', 'name')
                        ->searchable()
                        ->preload()
                        ->required(),

                    DatePicker::make('start_date')
                        ->label('Data Inicial')
                        ->required(),

                    DatePicker::make('end_date')
                        ->label('Data Final')
                        ->nullable(),

                    Toggle::make('is_active')
                        ->label('Ativo')
                        ->default(true),

                    Toggle::make('is_default')
                        ->label('Jornada padrão do colaborador')
                        ->default(true),
                ])
                ->columns(2),

            Section::make('Observações')
                ->collapsed()
                ->schema([
                    Textarea::make('notes')
                        ->label('Observações')
                        ->rows(3)
                        ->columnSpanFull(),

                    KeyValue::make('settings')
                        ->label('Configurações extras')
                        ->keyLabel('Chave')
                        ->valueLabel('Valor')
                        ->columnSpanFull(),
                ]),
        ]);
    }
}