<?php

namespace App\Filament\Resources\TimeEntries\Schemas;

use App\Models\AttendanceOccurrence;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Employee;
use App\Models\User;
use App\Models\Work;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TimeEntryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Dados do Lançamento')
                    
                    ->schema([
                        Select::make('company_id')
                            ->label('Empresa')
                            ->options(
                                Company::query()
                                    ->where('is_active', true)
                                    ->orderBy('name')
                                    ->pluck('name', 'id')
                            )
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set) {
                                $set('branch_id', null);
                                $set('work_id', null);
                                $set('employee_id', null);
                            }),

                        Select::make('branch_id')
                            ->label('Filial')
                            ->options(fn ($get) => Branch::query()
                                ->when($get('company_id'), fn ($query, $companyId) => $query->where('company_id', $companyId))
                                ->where('is_active', true)
                                ->orderBy('name')
                                ->pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set) {
                                $set('work_id', null);
                                $set('employee_id', null);
                            }),

                        Select::make('work_id')
                            ->label('Obra')
                            ->options(fn ($get) => Work::query()
                                ->when($get('company_id'), fn ($query, $companyId) => $query->where('company_id', $companyId))
                                ->when($get('branch_id'), fn ($query, $branchId) => $query->where('branch_id', $branchId))
                                ->where('is_active', true)
                                ->orderBy('name')
                                ->pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set) {
                                $set('employee_id', null);
                            }),

                        Select::make('employee_id')
                            ->label('Colaborador')
                            ->options(fn ($get) => Employee::query()
                                ->when($get('company_id'), fn ($query, $companyId) => $query->where('company_id', $companyId))
                                ->when($get('branch_id'), fn ($query, $branchId) => $query->where('branch_id', $branchId))
                                ->when($get('work_id'), fn ($query, $workId) => $query->where('work_id', $workId))
                                ->where('is_active', true)
                                ->orderBy('name')
                                ->pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->required(),

                        DatePicker::make('entry_date')
                            ->label('Data')
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->required(),

                        Select::make('attendance_occurrence_id')
                            ->label('Ocorrência')
                            ->options(
                                AttendanceOccurrence::query()
                                    ->where('is_active', true)
                                    ->orderBy('name')
                                    ->pluck('name', 'id')
                            )
                            ->searchable()
                            ->preload(),

                        Toggle::make('is_manual')
                            ->label('Lançamento Manual')
                            ->default(true),

                        Select::make('source')
                            ->label('Origem')
                            ->options([
                                'manual' => 'Manual',
                                'import' => 'Importação',
                                'integration' => 'Integração',
                            ])
                            ->default('manual')
                            ->required(),

                        Select::make('created_by')
                            ->label('Criado por')
                            ->options(
                                User::query()
                                    ->orderBy('name')
                                    ->pluck('name', 'id')
                            )
                            ->searchable()
                            ->preload(),
                    ]),

                Section::make('Marcações')
                    
                    ->schema([
                        TimePicker::make('entry_1')
                            ->label('Entrada 1')
                            ->seconds(false),

                        TimePicker::make('exit_1')
                            ->label('Saída 1')
                            ->seconds(false),

                        TimePicker::make('entry_2')
                            ->label('Entrada 2')
                            ->seconds(false),

                        TimePicker::make('exit_2')
                            ->label('Saída 2')
                            ->seconds(false),
                    ]),

                Section::make('Apuração')
                    
                    ->schema([
                        TextInput::make('expected_minutes')
                            ->label('Minutos Esperados')
                            ->numeric()
                            ->default(0)
                            ->required(),

                        TextInput::make('worked_minutes')
                            ->label('Minutos Trabalhados')
                            ->numeric()
                            ->default(0)
                            ->required(),

                        TextInput::make('overtime_minutes')
                            ->label('Minutos Extras')
                            ->numeric()
                            ->default(0)
                            ->required(),

                        TextInput::make('lateness_minutes')
                            ->label('Minutos de Atraso')
                            ->numeric()
                            ->default(0)
                            ->required(),

                        TextInput::make('absence_minutes')
                            ->label('Minutos de Falta')
                            ->numeric()
                            ->default(0)
                            ->required(),

                        TextInput::make('night_minutes')
                            ->label('Minutos Noturnos')
                            ->numeric()
                            ->default(0)
                            ->required(),

                        Placeholder::make('worked_time_hint')
                            ->label('Referência')
                            ->content('Os campos acima devem ser preenchidos em minutos. Ex.: 480 = 08h00.')
                            ->columnSpanFull(),
                    ]),

                Section::make('Observações')
                    ->schema([
                        Textarea::make('notes')
                            ->label('Observações')
                            ->rows(4),

                        Select::make('updated_by')
                            ->label('Atualizado por')
                            ->options(
                                User::query()
                                    ->orderBy('name')
                                    ->pluck('name', 'id')
                            )
                            ->searchable()
                            ->preload(),
                    ]),
            ]);
    }
}