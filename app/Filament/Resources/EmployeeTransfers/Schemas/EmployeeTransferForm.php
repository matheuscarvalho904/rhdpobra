<?php

namespace App\Filament\Resources\EmployeeTransfers\Schemas;

use App\Models\Branch;
use App\Models\Company;
use App\Models\CostCenter;
use App\Models\Department;
use App\Models\Employee;
use App\Models\JobRole;
use App\Models\User;
use App\Models\Work;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class EmployeeTransferForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Transferência')
                    
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

                        DatePicker::make('transfer_date')
                            ->label('Data da Transferência')
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->required(),

                        Select::make('old_company_id')
                            ->label('Empresa Anterior')
                            ->options(Company::query()->orderBy('name')->pluck('name', 'id'))
                            ->searchable()
                            ->preload(),

                        Select::make('new_company_id')
                            ->label('Nova Empresa')
                            ->options(Company::query()->orderBy('name')->pluck('name', 'id'))
                            ->searchable()
                            ->preload(),

                        Select::make('old_branch_id')
                            ->label('Filial Anterior')
                            ->options(Branch::query()->orderBy('name')->pluck('name', 'id'))
                            ->searchable()
                            ->preload(),

                        Select::make('new_branch_id')
                            ->label('Nova Filial')
                            ->options(Branch::query()->orderBy('name')->pluck('name', 'id'))
                            ->searchable()
                            ->preload(),

                        Select::make('old_work_id')
                            ->label('Obra Anterior')
                            ->options(Work::query()->orderBy('name')->pluck('name', 'id'))
                            ->searchable()
                            ->preload(),

                        Select::make('new_work_id')
                            ->label('Nova Obra')
                            ->options(Work::query()->orderBy('name')->pluck('name', 'id'))
                            ->searchable()
                            ->preload(),

                        Select::make('old_department_id')
                            ->label('Departamento Anterior')
                            ->options(Department::query()->orderBy('name')->pluck('name', 'id'))
                            ->searchable()
                            ->preload(),

                        Select::make('new_department_id')
                            ->label('Novo Departamento')
                            ->options(Department::query()->orderBy('name')->pluck('name', 'id'))
                            ->searchable()
                            ->preload(),

                        Select::make('old_cost_center_id')
                            ->label('Centro de Custo Anterior')
                            ->options(CostCenter::query()->orderBy('name')->pluck('name', 'id'))
                            ->searchable()
                            ->preload(),

                        Select::make('new_cost_center_id')
                            ->label('Novo Centro de Custo')
                            ->options(CostCenter::query()->orderBy('name')->pluck('name', 'id'))
                            ->searchable()
                            ->preload(),

                        Select::make('old_job_role_id')
                            ->label('Cargo Anterior')
                            ->options(JobRole::query()->orderBy('name')->pluck('name', 'id'))
                            ->searchable()
                            ->preload(),

                        Select::make('new_job_role_id')
                            ->label('Novo Cargo')
                            ->options(JobRole::query()->orderBy('name')->pluck('name', 'id'))
                            ->searchable()
                            ->preload(),

                        TextInput::make('reason')
                            ->label('Motivo')
                            ->maxLength(255),

                        Select::make('created_by')
                            ->label('Criado por')
                            ->options(User::query()->orderBy('name')->pluck('name', 'id'))
                            ->searchable()
                            ->preload(),
                    ]),
            ]);
    }
}