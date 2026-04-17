<?php

namespace App\Filament\Resources\EmployeeContracts\Schemas;

use App\Models\Branch;
use App\Models\Company;
use App\Models\ContractType;
use App\Models\CostCenter;
use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeContract;
use App\Models\JobRole;
use App\Models\Work;
use App\Models\WorkShift;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class EmployeeContractForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Tabs::make('Contrato')
                ->tabs([
                    Tab::make('Dados do Contrato')
                        ->schema([
                            Select::make('employee_id')
                                ->label('Colaborador')
                                ->options(fn () => Employee::query()->orderBy('name')->pluck('name', 'id')->toArray())
                                ->searchable()
                                ->preload()
                                ->live()
                                ->required()
                                ->afterStateUpdated(function (Set $set, Get $get, $state): void {
                                    if (! $state) {
                                        return;
                                    }

                                    $employee = Employee::query()->find($state);

                                    if (! $employee) {
                                        return;
                                    }

                                    $nextSequence = EmployeeContract::query()
                                        ->where('employee_id', $employee->id)
                                        ->max('contract_sequence');

                                    $nextSequence = ((int) $nextSequence) + 1;

                                    $set('contract_sequence', $nextSequence);

                                    if (blank($get('registration_number'))) {
                                        $employeeCode = $employee->code ?: str_pad((string) $employee->id, 4, '0', STR_PAD_LEFT);
                                        $registration = $employeeCode . '-' . str_pad((string) $nextSequence, 2, '0', STR_PAD_LEFT);

                                        $set('registration_number', $registration);
                                    }

                                    if (blank($get('company_id')) && $employee->company_id) {
                                        $set('company_id', $employee->company_id);
                                    }

                                    if (blank($get('branch_id')) && $employee->branch_id) {
                                        $set('branch_id', $employee->branch_id);
                                    }

                                    if (blank($get('work_id')) && $employee->work_id) {
                                        $set('work_id', $employee->work_id);
                                    }

                                    if (blank($get('department_id')) && $employee->department_id) {
                                        $set('department_id', $employee->department_id);
                                    }

                                    if (blank($get('job_role_id')) && $employee->job_role_id) {
                                        $set('job_role_id', $employee->job_role_id);
                                    }

                                    if (blank($get('cost_center_id')) && $employee->cost_center_id) {
                                        $set('cost_center_id', $employee->cost_center_id);
                                    }

                                    if (blank($get('contract_type_id')) && $employee->contract_type_id) {
                                        $set('contract_type_id', $employee->contract_type_id);
                                    }

                                    if (blank($get('work_shift_id')) && $employee->work_shift_id) {
                                        $set('work_shift_id', $employee->work_shift_id);
                                    }

                                    if (blank($get('admission_date')) && $employee->admission_date) {
                                        $set('admission_date', optional($employee->admission_date)->format('Y-m-d'));
                                    }

                                    if (blank($get('salary')) && $employee->salary) {
                                        $set('salary', (float) $employee->salary);
                                    }
                                }),

                            TextInput::make('registration_number')
                                ->label('Matrícula')
                                ->required()
                                ->maxLength(50)
                                ->helperText('Pode ser preenchida automaticamente ao selecionar o colaborador.'),

                            TextInput::make('contract_sequence')
                                ->label('Sequência')
                                ->numeric()
                                ->required()
                                ->default(1),
                        ]),

                    Tab::make('Lotação e Estrutura')
                        ->schema([
                            Select::make('company_id')
                                ->label('Empresa')
                                ->options(fn () => Company::query()->orderBy('name')->pluck('name', 'id')->toArray())
                                ->searchable()
                                ->preload()
                                ->live()
                                ->afterStateUpdated(function (Set $set): void {
                                    $set('branch_id', null);
                                    $set('work_id', null);
                                }),

                            Select::make('branch_id')
                                ->label('Filial')
                                ->options(fn (Get $get) => Branch::query()
                                    ->when($get('company_id'), fn ($query, $companyId) => $query->where('company_id', $companyId))
                                    ->orderBy('name')
                                    ->pluck('name', 'id')
                                    ->toArray())
                                ->searchable()
                                ->preload()
                                ->live()
                                ->afterStateUpdated(function (Set $set): void {
                                    $set('work_id', null);
                                }),

                            Select::make('work_id')
                                ->label('Obra')
                                ->options(fn (Get $get) => Work::query()
                                    ->when($get('company_id'), fn ($query, $companyId) => $query->where('company_id', $companyId))
                                    ->when($get('branch_id'), fn ($query, $branchId) => $query->where('branch_id', $branchId))
                                    ->orderBy('name')
                                    ->pluck('name', 'id')
                                    ->toArray())
                                ->searchable()
                                ->preload(),

                            Select::make('department_id')
                                ->label('Departamento')
                                ->options(fn () => Department::query()->orderBy('name')->pluck('name', 'id')->toArray())
                                ->searchable()
                                ->preload(),

                            Select::make('job_role_id')
                                ->label('Cargo')
                                ->options(fn () => JobRole::query()->orderBy('name')->pluck('name', 'id')->toArray())
                                ->searchable()
                                ->preload(),

                            Select::make('cost_center_id')
                                ->label('Centro de Custo')
                                ->options(fn () => CostCenter::query()->orderBy('name')->pluck('name', 'id')->toArray())
                                ->searchable()
                                ->preload(),
                        ]),

                    Tab::make('Condições do Vínculo')
                        ->schema([
                            Select::make('contract_type_id')
                                ->label('Tipo de Contrato')
                                ->options(fn () => ContractType::query()->orderBy('name')->pluck('name', 'id')->toArray())
                                ->searchable()
                                ->preload(),

                            Select::make('work_shift_id')
                                ->label('Jornada')
                                ->options(fn () => WorkShift::query()->orderBy('name')->pluck('name', 'id')->toArray())
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

                    Tab::make('Datas e Valores')
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

                    Tab::make('Observações')
                        ->schema([
                            Textarea::make('notes')
                                ->label('Observações')
                                ->rows(6),
                        ]),
                ])
                ->persistTabInQueryString()
                ->columnSpanFull(),
        ]);
    }
}