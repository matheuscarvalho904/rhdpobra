<?php

namespace App\Filament\Resources\PayrollRuns\Schemas;

use App\Models\Branch;
use App\Models\Company;
use App\Models\PayrollCompetency;
use App\Models\Work;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class PayrollRunForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Dados da Folha')
                ->columns(12)
                ->schema([
                    Select::make('payroll_competency_id')
                        ->label('Competência')
                        ->options(fn () => PayrollCompetency::query()
                            ->orderByDesc('year')
                            ->orderByDesc('month')
                            ->get()
                            ->mapWithKeys(fn (PayrollCompetency $competency) => [
                                $competency->id => $competency->display_name,
                            ])
                            ->toArray())
                        ->searchable()
                        ->preload()
                        ->required()
                        ->live()
                        ->columnSpan(12)
                        ->afterStateUpdated(function (?string $state, Set $set, Get $get): void {
                            if (blank($state)) {
                                return;
                            }

                            $competency = PayrollCompetency::query()->find($state);

                            if (! $competency) {
                                return;
                            }

                            if (blank(request()->route('record'))) {
                                $runTypeLabel = self::runTypeOptions()[$get('run_type') ?? 'payroll_clt'] ?? 'Folha';

                                $set('description', $runTypeLabel . ' - ' . $competency->display_name);
                            }
                        }),

                    Select::make('run_type')
                        ->label('Tipo de Processamento')
                        ->options(self::runTypeOptions())
                        ->required()
                        ->default('payroll_clt')
                        ->live()
                        ->columnSpan(8)
                        ->afterStateUpdated(function (?string $state, Set $set, Get $get): void {
                            if (blank(request()->route('record'))) {
                                $competencyId = $get('payroll_competency_id');
                                $competency = $competencyId
                                    ? PayrollCompetency::query()->find($competencyId)
                                    : null;

                                $runTypeLabel = self::runTypeOptions()[$state ?? 'payroll_clt'] ?? 'Folha';

                                if ($competency) {
                                    $set('description', $runTypeLabel . ' - ' . $competency->display_name);
                                } else {
                                    $set('description', $runTypeLabel);
                                }
                            }
                        }),

                    Select::make('status')
                        ->label('Status')
                        ->options([
                            'open' => 'Aberta',
                            'processing' => 'Processando',
                            'processed' => 'Processada',
                            'closed' => 'Fechada',
                            'error' => 'Erro',
                        ])
                        ->default('open')
                        ->required()
                        ->disabled(fn (?string $operation) => $operation === 'create')
                        ->dehydrated(true)
                        ->columnSpan(4),

                    Select::make('company_id')
                        ->label('Empresa')
                        ->options(fn () => Company::query()
                            ->orderBy('name')
                            ->pluck('name', 'id')
                            ->toArray())
                        ->searchable()
                        ->preload()
                        ->required()
                        ->live()
                        ->columnSpan(12)
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
                        ->columnSpan(12)
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
                        ->preload()
                        ->columnSpan(12),

                    TextInput::make('description')
                        ->label('Descrição')
                        ->required()
                        ->maxLength(255)
                        ->columnSpan(12),

                    Textarea::make('notes')
                        ->label('Observações')
                        ->rows(4)
                        ->columnSpan(12),
                ]),

            Section::make('Totais e Controle')
                ->description('Esses campos são preenchidos automaticamente após o processamento.')
                ->columns(12)
                ->schema([
                    TextInput::make('total_gross')
                        ->label('Total Bruto')
                        ->prefix('R$')
                        ->disabled()
                        ->dehydrated(false)
                        ->formatStateUsing(fn ($state) => number_format((float) ($state ?? 0), 2, ',', '.'))
                        ->columnSpan(6),

                    TextInput::make('total_discounts')
                        ->label('Total de Descontos')
                        ->prefix('R$')
                        ->disabled()
                        ->dehydrated(false)
                        ->formatStateUsing(fn ($state) => number_format((float) ($state ?? 0), 2, ',', '.'))
                        ->columnSpan(6),

                    TextInput::make('total_net')
                        ->label('Total Líquido')
                        ->prefix('R$')
                        ->disabled()
                        ->dehydrated(false)
                        ->formatStateUsing(fn ($state) => number_format((float) ($state ?? 0), 2, ',', '.'))
                        ->columnSpan(6),

                    TextInput::make('total_fgts')
                        ->label('Total FGTS')
                        ->prefix('R$')
                        ->disabled()
                        ->dehydrated(false)
                        ->formatStateUsing(fn ($state) => number_format((float) ($state ?? 0), 2, ',', '.'))
                        ->columnSpan(6),

                    TextInput::make('processed_employees')
                        ->label('Colaboradores Processados')
                        ->disabled()
                        ->dehydrated(false)
                        ->columnSpan(6),

                    TextInput::make('processed_at')
                        ->label('Processada em')
                        ->disabled()
                        ->dehydrated(false)
                        ->formatStateUsing(fn ($state) => $state ? \Carbon\Carbon::parse($state)->format('d/m/Y H:i') : null)
                        ->columnSpan(6),

                    TextInput::make('closed_at')
                        ->label('Fechada em')
                        ->disabled()
                        ->dehydrated(false)
                        ->formatStateUsing(fn ($state) => $state ? \Carbon\Carbon::parse($state)->format('d/m/Y H:i') : null)
                        ->columnSpan(6),

                    Textarea::make('error_message')
                        ->label('Mensagem de Erro')
                        ->disabled()
                        ->dehydrated(false)
                        ->rows(3)
                        ->columnSpan(6),
                ]),
        ]);
    }

    protected static function runTypeOptions(): array
    {
        return [
            'payroll_clt' => 'Folha CLT',
            'payroll_apprentice' => 'Folha Aprendiz',
            'internship_payment' => 'Folha Estágio',
            'payroll_rpa' => 'Folha RPA / PF',
            'accounts_payable' => 'Contas a Pagar / PJ',
        ];
    }
}