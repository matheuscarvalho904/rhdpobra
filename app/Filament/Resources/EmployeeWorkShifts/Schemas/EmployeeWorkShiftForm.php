<?php

namespace App\Filament\Resources\EmployeeWorkShifts\Schemas;

use App\Models\Employee;
use App\Models\WorkShift;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class EmployeeWorkShiftForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Jornada do Colaborador')
                    
                    ->schema([
                        Select::make('employee_id')
                            ->label('Colaborador')
                            ->options(
                                Employee::query()
                                    ->orderBy('name')
                                    ->pluck('name', 'id')
                            )
                            ->searchable()
                            ->preload()
                            ->required(),

                        Select::make('work_shift_id')
                            ->label('Jornada')
                            ->options(
                                WorkShift::query()
                                    ->where('is_active', true)
                                    ->orderBy('name')
                                    ->pluck('name', 'id')
                            )
                            ->searchable()
                            ->preload()
                            ->required(),

                        DatePicker::make('start_date')
                            ->label('Data Inicial')
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->required(),

                        DatePicker::make('end_date')
                            ->label('Data Final')
                            ->native(false)
                            ->displayFormat('d/m/Y'),

                        Toggle::make('is_active')
                            ->label('Ativo')
                            ->default(true),
                    ]),
            ]);
    }
}