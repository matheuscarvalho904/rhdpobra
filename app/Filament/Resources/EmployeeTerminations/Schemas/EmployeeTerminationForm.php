<?php

namespace App\Filament\Resources\EmployeeTerminations\Schemas;

use App\Models\Employee;
use App\Models\EmployeeContract;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
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
                ->columnSpanFull()
                ->tabs([

                    Tab::make('Dados Principais')
                        ->schema([
                            Section::make('Colaborador e contrato')
                                ->columns(1)
                                ->schema([
                                    Select::make('employee_id')
                                        ->label('Colaborador')
                                        ->options(fn () => Employee::query()
                                            ->where('is_active', true)
                                            ->orderBy('name')
                                            ->pluck('name', 'id')
                                            ->toArray())
                                        ->searchable()
                                        ->preload()
                                        ->live()
                                        ->required()
                                        ->afterStateUpdated(function ($state, Set $set): void {
                                            if (! $state) {
                                                $set('employee_contract_id', null);
                                                return;
                                            }

                                            $contract = EmployeeContract::query()
                                                ->where('employee_id', $state)
                                                ->where('is_current', true)
                                                ->first();

                                            if ($contract) {
                                                $set('employee_contract_id', $contract->id);
                                            }
                                        }),

                                    Select::make('employee_contract_id')
                                        ->label('Contrato atual')
                                        ->options(fn (Get $get) => EmployeeContract::query()
                                            ->when($get('employee_id'), fn ($query, $employeeId) => $query->where('employee_id', $employeeId))
                                            ->orderByDesc('id')
                                            ->get()
                                            ->mapWithKeys(fn ($contract) => [
                                                $contract->id => $contract->registration_number
                                                    ?: 'Contrato #' . $contract->id,
                                            ])
                                            ->toArray())
                                        ->searchable()
                                        ->preload()
                                        ->required(),

                                    Select::make('status')
                                        ->label('Status')
                                        ->options([
                                            'draft' => 'Rascunho',
                                            'in_progress' => 'Em andamento',
                                            'calculated' => 'Calculado',
                                            'closed' => 'Fechado',
                                            'canceled' => 'Cancelado',
                                        ])
                                        ->default('draft')
                                        ->required(),
                                ]),
                        ]),

                    Tab::make('Dados do Desligamento')
                        ->schema([
                            Section::make('Motivo e datas')
                                ->columns(1)
                                ->schema([
                                    Select::make('dismissal_type')
                                        ->label('Tipo de Desligamento')
                                        ->options([
                                            'without_cause' => 'Dispensa sem justa causa',
                                            'with_cause' => 'Dispensa com justa causa',
                                            'resignation' => 'Pedido de demissão',
                                            'mutual_agreement' => 'Acordo entre as partes',
                                            'contract_end' => 'Término de contrato',
                                            'death' => 'Falecimento',
                                            'retirement' => 'Aposentadoria',
                                        ])
                                        ->native(false)
                                        ->required(),

                                    Select::make('termination_reason')
                                        ->label('Motivo')
                                        ->options([
                                            'company_initiative' => 'Iniciativa da empresa',
                                            'employee_initiative' => 'Iniciativa do colaborador',
                                            'end_of_contract' => 'Fim de contrato determinado',
                                            'disciplinary' => 'Motivo disciplinar',
                                            'agreement' => 'Acordo legal',
                                            'other' => 'Outro',
                                        ])
                                        ->native(false),

                                    DatePicker::make('termination_date')
                                        ->label('Data do Desligamento')
                                        ->native(false)
                                        ->displayFormat('d/m/Y')
                                        ->required()
                                        ->live()
                                        ->afterStateUpdated(function (Get $get, Set $set): void {
                                            self::recalculateNoticeDates($get, $set);
                                        }),

                                    DatePicker::make('last_worked_date')
                                        ->label('Último Dia Trabalhado')
                                        ->native(false)
                                        ->displayFormat('d/m/Y'),
                                ]),
                        ]),

                    Tab::make('Aviso Prévio')
                        ->schema([
                            Section::make('Regras do aviso')
                                ->columns(1)
                                ->schema([
                                    Select::make('notice_type')
                                        ->label('Tipo de Aviso')
                                        ->options([
                                            'worked' => 'Trabalhado',
                                            'indemnified' => 'Indenizado',
                                            'dismissed' => 'Dispensado do cumprimento',
                                            'not_applicable' => 'Não se aplica',
                                        ])
                                        ->native(false)
                                        ->live()
                                        ->afterStateUpdated(function (Get $get, Set $set): void {
                                            self::recalculateNoticeDates($get, $set);
                                        }),

                                    DatePicker::make('notice_start_date')
                                        ->label('Início do Aviso')
                                        ->native(false)
                                        ->displayFormat('d/m/Y')
                                        ->visible(fn (Get $get) => in_array($get('notice_type'), ['worked', 'indemnified', 'dismissed'], true))
                                        ->live()
                                        ->afterStateUpdated(function (Get $get, Set $set): void {
                                            self::recalculateNoticeDates($get, $set);
                                        }),

                                    TextInput::make('notice_days')
                                        ->label('Dias de Aviso')
                                        ->numeric()
                                        ->default(30)
                                        ->visible(fn (Get $get) => in_array($get('notice_type'), ['worked', 'indemnified', 'dismissed'], true))
                                        ->live()
                                        ->afterStateUpdated(function (Get $get, Set $set): void {
                                            self::recalculateNoticeDates($get, $set);
                                        }),

                                    DatePicker::make('notice_end_date')
                                        ->label('Fim do Aviso')
                                        ->native(false)
                                        ->displayFormat('d/m/Y')
                                        ->disabled()
                                        ->dehydrated(true)
                                        ->visible(fn (Get $get) => in_array($get('notice_type'), ['worked', 'indemnified', 'dismissed'], true)),

                                    DatePicker::make('projected_end_date')
                                        ->label('Data Projetada')
                                        ->native(false)
                                        ->displayFormat('d/m/Y')
                                        ->disabled()
                                        ->dehydrated(true),

                                    Select::make('reduction_type')
                                        ->label('Redução do Aviso')
                                        ->options([
                                            'none' => 'Sem redução',
                                            'two_hours' => 'Redução de 2 horas diárias',
                                            'seven_days' => 'Redução de 7 dias corridos',
                                        ])
                                        ->default('none')
                                        ->native(false)
                                        ->visible(fn (Get $get) => $get('notice_type') === 'worked'),

                                    Select::make('is_notice_projected')
                                        ->label('Projeta aviso nas verbas?')
                                        ->options([
                                            1 => 'Sim',
                                            0 => 'Não',
                                        ])
                                        ->default(1)
                                        ->native(false),
                                ]),
                        ]),

                    Tab::make('Valores')
                        ->schema([
                            Section::make('Resumo financeiro')
                                ->columns(1)
                                ->schema([
                                    TextInput::make('notice_amount')
                                        ->label('Valor do Aviso')
                                        ->numeric()
                                        ->prefix('R$'),

                                    TextInput::make('termination_amount')
                                        ->label('Valor da Rescisão')
                                        ->numeric()
                                        ->prefix('R$'),
                                ]),
                        ]),

                    Tab::make('Observações')
                        ->schema([
                            Section::make('Informações complementares')
                                ->columns(1)
                                ->schema([
                                    Textarea::make('notes')
                                        ->label('Observações')
                                        ->rows(5),
                                ]),
                        ]),
                ]),
        ]);
    }

    protected static function recalculateNoticeDates(Get $get, Set $set): void
    {
        $noticeType = $get('notice_type');

        if (! in_array($noticeType, ['worked', 'indemnified', 'dismissed'], true)) {
            $set('notice_end_date', null);
            $set('projected_end_date', $get('termination_date'));
            return;
        }

        $startDate = $get('notice_start_date') ?: $get('termination_date');

        if (! $startDate) {
            $set('notice_end_date', null);
            $set('projected_end_date', null);
            return;
        }

        $days = (int) ($get('notice_days') ?: 30);

        if ($days <= 0) {
            $days = 30;
        }

        $endDate = Carbon::parse($startDate)
            ->addDays($days - 1)
            ->format('Y-m-d');

        $set('notice_start_date', Carbon::parse($startDate)->format('Y-m-d'));
        $set('notice_end_date', $endDate);

        if ($noticeType === 'worked') {
            $set('last_worked_date', $endDate);
            $set('termination_date', $endDate);
            $set('projected_end_date', $endDate);
            return;
        }

        if ($noticeType === 'indemnified') {
            $set('projected_end_date', $endDate);
            return;
        }

        if ($noticeType === 'dismissed') {
            $set('projected_end_date', $endDate);
        }
    }
}