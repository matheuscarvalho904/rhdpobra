<?php

namespace App\Filament\Resources\EmployeeTerminations\Schemas;

use App\Models\EmployeeContract;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class EmployeeTerminationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Tabs::make('Desligamento')
                ->tabs([

                    Tab::make('Dados Principais')
                        ->schema([
                            Select::make('employee_id')
                                ->label('Colaborador')
                                ->relationship('employee', 'name')
                                ->searchable()
                                ->preload()
                                ->live()
                                ->required()
                                ->afterStateUpdated(function ($state, Set $set) {

                                    if (! $state) return;

                                    $contract = EmployeeContract::query()
                                        ->where('employee_id', $state)
                                        ->where('is_current', true)
                                        ->first();

                                    if ($contract) {
                                        $set('employee_contract_id', $contract->id);
                                    }
                                }),

                            Select::make('employee_contract_id')
                                ->label('Contrato')
                                ->relationship('contract', 'registration_number')
                                ->searchable()
                                ->preload()
                                ->required(),

                            Select::make('status')
                                ->label('Status')
                                ->options([
                                    'draft' => 'Rascunho',
                                    'in_progress' => 'Em andamento',
                                    'closed' => 'Fechado',
                                ])
                                ->default('draft')
                                ->required(),
                        ]),

                    Tab::make('Dados do Desligamento')
                        ->schema([
                            DatePicker::make('termination_date')
                                ->label('Data do Desligamento')
                                ->required(),

                            TextInput::make('dismissal_type')->label('Tipo'),
                            TextInput::make('termination_reason')->label('Motivo'),
                        ]),

                    Tab::make('Aviso Prévio')
                        ->schema([
                            Select::make('notice_type')
                                ->label('Tipo')
                                ->options([
                                    'worked' => 'Trabalhado',
                                    'indemnified' => 'Indenizado',
                                    'home' => 'Em Casa',
                                ])
                                ->live(),

                            TextInput::make('notice_days')
                                ->numeric()
                                ->default(30)
                                ->visible(fn (Get $get) => filled($get('notice_type'))),
                        ]),

                    Tab::make('Valores')
                        ->schema([
                            TextInput::make('notice_amount')
                                ->label('Valor do Aviso')
                                ->numeric()
                                ->prefix('R$'),

                            TextInput::make('termination_amount')
                                ->label('Valor da rescisão')
                                ->numeric()
                                ->prefix('R$'),
                        ]),

                    Tab::make('Observações')
                        ->schema([
                            Textarea::make('notes')->rows(5),
                        ]),
                ])
                ->columnSpanFull(),
        ]);
    }
}