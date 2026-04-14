<?php

namespace App\Filament\Resources\PayrollAccountMappings\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class PayrollAccountMappingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                Grid::make(4)->schema([

                    Select::make('company_id')
                        ->label('Empresa')
                        ->relationship('company', 'name')
                        ->searchable(),

                    Select::make('branch_id')
                        ->label('Filial')
                        ->relationship('branch', 'name')
                        ->searchable(),

                    Select::make('work_id')
                        ->label('Obra')
                        ->relationship('work', 'name')
                        ->searchable(),

                    Select::make('cost_center_id')
                        ->label('Centro de Custo')
                        ->relationship('costCenter', 'name')
                        ->searchable(),
                ]),

                Grid::make(3)->schema([

                    Select::make('payroll_event_id')
                        ->label('Evento da Folha')
                        ->relationship('payrollEvent', 'name')
                        ->searchable(),

                    TextInput::make('event_code')
                        ->label('Código Evento')
                        ->maxLength(50),

                    Select::make('event_type')
                        ->label('Tipo Evento')
                        ->options([
                            'provento' => 'Provento',
                            'desconto' => 'Desconto',
                            'encargo' => 'Encargo',
                            'informativo' => 'Informativo',
                        ]),
                ]),

                Grid::make(2)->schema([

                    TextInput::make('debit_account')
                        ->label('Conta Débito')
                        ->required()
                        ->maxLength(50),

                    TextInput::make('credit_account')
                        ->label('Conta Crédito')
                        ->required()
                        ->maxLength(50),
                ]),

                TextInput::make('history_template')
                    ->label('Histórico Padrão')
                    ->placeholder('Folha {competencia} - {evento} - {colaborador}')
                    ->maxLength(255),

                Toggle::make('is_active')
                    ->label('Ativo')
                    ->default(true),
            ]);
    }
}