<?php

namespace App\Filament\Resources\EmployeeDependents\Schemas;

use App\Models\Employee;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class EmployeeDependentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Dados do Dependente')
                    
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

                        TextInput::make('name')
                            ->label('Nome')
                            ->required()
                            ->maxLength(255),

                        Select::make('relationship')
                            ->label('Parentesco')
                            ->options([
                                'child' => 'Filho(a)',
                                'spouse' => 'Cônjuge',
                                'partner' => 'Companheiro(a)',
                                'stepchild' => 'Enteado(a)',
                                'father' => 'Pai',
                                'mother' => 'Mãe',
                                'sibling' => 'Irmão(ã)',
                                'other' => 'Outros',
                            ])
                            ->required(),

                        TextInput::make('cpf')
                            ->label('CPF')
                            ->mask('999.999.999-99')
                            ->maxLength(14),

                        DatePicker::make('birth_date')
                            ->label('Data de Nascimento')
                            ->native(false)
                            ->displayFormat('d/m/Y'),

                        Toggle::make('is_ir_dependent')
                            ->label('Dependente de IR')
                            ->default(false),

                        Toggle::make('is_family_allowance_dependent')
                            ->label('Dependente de Salário Família')
                            ->default(false),

                        Toggle::make('is_active')
                            ->label('Ativo')
                            ->default(true),
                    ]),
            ]);
    }
}