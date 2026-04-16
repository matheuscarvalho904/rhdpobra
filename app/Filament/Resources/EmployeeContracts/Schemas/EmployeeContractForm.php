<?php

namespace App\Filament\Resources\EmployeeContracts\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Tabs;

class EmployeeContractForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Tabs::make('Contrato')
                ->tabs([
                    Tabs\Tab::make('Dados do Contrato')
                        ->schema([
                            Select::make('employee_id')
                                ->label('Colaborador')
                                ->relationship('employee', 'name')
                                ->searchable()
                                ->preload()
                                ->required(),

                            TextInput::make('registration_number')
                                ->label('Matrícula')
                                ->required()
                                ->maxLength(50),

                            TextInput::make('contract_sequence')
                                ->label('Sequência')
                                ->numeric()
                                ->required(),
                        ]),

                    Tabs\Tab::make('Lotação e Estrutura')
                        ->schema([
                            Select::make('company_id')
                                ->label('Empresa')
                                ->relationship('company', 'name')
                                ->searchable()
                                ->preload(),

                            Select::make('branch_id')
                                ->label('Filial')
                                ->relationship('branch', 'name')
                                ->searchable()
                                ->preload(),

                            Select::make('work_id')
                                ->label('Obra')
                                ->relationship('work', 'name')
                                ->searchable()
                                ->preload(),

                            Select::make('department_id')
                                ->label('Departamento')
                                ->relationship('department', 'name')
                                ->searchable()
                                ->preload(),

                            Select::make('job_role_id')
                                ->label('Cargo')
                                ->relationship('jobRole', 'name')
                                ->searchable()
                                ->preload(),

                            Select::make('cost_center_id')
                                ->label('Centro de Custo')
                                ->relationship('costCenter', 'name')
                                ->searchable()
                                ->preload(),
                        ]),

                    Tabs\Tab::make('Condições do Vínculo')
                        ->schema([
                            Select::make('contract_type_id')
                                ->label('Tipo de Contrato')
                                ->relationship('contractType', 'name')
                                ->searchable()
                                ->preload(),

                            Select::make('work_shift_id')
                                ->label('Jornada')
                                ->relationship('workShift', 'name')
                                ->searchable()
                                ->preload(),

                            Select::make('status')
                                ->label('Status')
                                ->options([
                                    'ativo' => 'Ativo',
                                    'em_aviso' => 'Em Aviso',
                                    'desligado' => 'Desligado',
                                    'suspenso' => 'Suspenso',
                                    'afastado' => 'Afastado',
                                ])
                                ->default('ativo')
                                ->required(),
                        ]),

                    Tabs\Tab::make('Datas e Valores')
                        ->schema([
                            DatePicker::make('admission_date')
                                ->label('Admissão')
                                ->required(),

                            DatePicker::make('termination_date')
                                ->label('Desligamento'),

                            TextInput::make('salary')
                                ->label('Salário')
                                ->numeric()
                                ->prefix('R$')
                                ->required(),
                        ]),

                    Tabs\Tab::make('Observações')
                        ->schema([
                            Textarea::make('notes')
                                ->label('Observações')
                                ->rows(6)
                                ->columnSpanFull(),
                        ]),
                ])
                ->columnSpanFull(),
        ]);
    }
}