<?php

namespace App\Filament\Resources\EmployeeTerminations\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class EmployeeTerminationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Dados Principais')
                ->schema([
                    Grid::make([
                        'default' => 1,
                        'md' => 2,
                        'xl' => 3,
                    ])->schema([
                        Select::make('employee_id')
                            ->label('Colaborador')
                            ->relationship('employee', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),

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
                                'cancelled' => 'Cancelado',
                            ])
                            ->default('draft')
                            ->required(),
                    ]),
                ]),

            Section::make('Dados do Desligamento')
                ->schema([
                    Grid::make([
                        'default' => 1,
                        'md' => 2,
                        'xl' => 3,
                    ])->schema([
                        DatePicker::make('termination_date')
                            ->label('Data do Desligamento')
                            ->required(),

                        TextInput::make('dismissal_type')
                            ->label('Tipo de Desligamento')
                            ->placeholder('Ex.: Sem justa causa'),

                        TextInput::make('termination_reason')
                            ->label('Motivo')
                            ->placeholder('Ex.: Encerramento de contrato'),
                    ]),
                ]),

            Section::make('Aviso Prévio')
                ->schema([
                    Grid::make([
                        'default' => 1,
                        'md' => 2,
                        'xl' => 3,
                    ])->schema([
                        Select::make('notice_type')
                            ->label('Tipo de Aviso')
                            ->options([
                                'worked' => 'Trabalhado',
                                'indemnified' => 'Indenizado',
                                'home' => 'Em Casa',
                            ])
                            ->live(),

                        TextInput::make('notice_days')
                            ->label('Dias de Aviso')
                            ->numeric()
                            ->default(30)
                            ->required(fn (Get $get) => filled($get('notice_type')))
                            ->visible(fn (Get $get) => filled($get('notice_type'))),

                        Select::make('reduction_type')
                            ->label('Redução')
                            ->options([
                                'none' => 'Sem redução',
                                '2_hours_daily' => '2 horas diárias',
                                '7_days_final' => '7 dias finais',
                            ])
                            ->default('none')
                            ->visible(fn (Get $get) => $get('notice_type') === 'worked'),

                        Select::make('is_notice_projected')
                            ->label('Aviso Projetado?')
                            ->options([
                                0 => 'Não',
                                1 => 'Sim',
                            ])
                            ->default(0)
                            ->visible(fn (Get $get) => in_array($get('notice_type'), ['worked', 'indemnified'], true)),
                    ]),

                    Grid::make([
                        'default' => 1,
                        'md' => 2,
                        'xl' => 3,
                    ])->schema([
                        DatePicker::make('notice_start_date')
                            ->label('Início do Aviso')
                            ->visible(fn (Get $get) => in_array($get('notice_type'), ['worked', 'home'], true))
                            ->required(fn (Get $get) => in_array($get('notice_type'), ['worked', 'home'], true)),

                        DatePicker::make('notice_end_date')
                            ->label('Fim do Aviso')
                            ->visible(fn (Get $get) => in_array($get('notice_type'), ['worked', 'home'], true)),

                        DatePicker::make('last_worked_date')
                            ->label('Último Dia Trabalhado')
                            ->visible(fn (Get $get) => in_array($get('notice_type'), ['worked', 'home'], true)),

                        DatePicker::make('projected_end_date')
                            ->label('Data Projetada')
                            ->visible(fn (Get $get) => $get('notice_type') === 'indemnified'),
                    ]),
                ]),

            Section::make('Valores')
                ->schema([
                    Grid::make([
                        'default' => 1,
                        'md' => 2,
                    ])->schema([
                        TextInput::make('notice_amount')
                            ->label('Valor do Aviso')
                            ->numeric()
                            ->prefix('R$')
                            ->visible(fn (Get $get) => filled($get('notice_type'))),

                        TextInput::make('termination_amount')
                            ->label('Valor da Rescisão')
                            ->numeric()
                            ->prefix('R$'),
                    ]),
                ]),

            Section::make('Observações')
                ->schema([
                    Textarea::make('notes')
                        ->label('Observações')
                        ->rows(4)
                        ->columnSpanFull(),
                ]),
        ]);
    }
}