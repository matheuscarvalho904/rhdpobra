<?php

namespace App\Filament\Resources\Employees\Schemas;

use App\Models\Bank;
use App\Models\Branch;
use App\Models\CboCode;
use App\Models\Company;
use App\Models\ContractType;
use App\Models\CostCenter;
use App\Models\Department;
use App\Models\JobRole;
use App\Models\LaborUnion;
use App\Models\Work;
use App\Models\WorkShift;
use App\Services\ContractProcessingRuleService;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\RawJs;

class EmployeeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Tabs::make('Cadastro do Colaborador')
                ->columnSpanFull()
                ->tabs([
                    Tab::make('Dados Gerais')
                        ->icon('heroicon-o-identification')
                        ->schema([
                            Section::make('Identificação e vínculo')
                                ->columns([
                                    'default' => 1,
                                    'md' => 6,
                                    'xl' => 12,
                                ])
                                ->schema([
                                    TextInput::make('code')
                                        ->label('Código')
                                        ->maxLength(30)
                                        ->columnSpan([
                                            'default' => 1,
                                            'md' => 2,
                                            'xl' => 2,
                                        ]),

                                    TextInput::make('name')
                                        ->label('Nome')
                                        ->required()
                                        ->maxLength(255)
                                        ->columnSpan([
                                            'default' => 1,
                                            'md' => 4,
                                            'xl' => 5,
                                        ]),

                                    TextInput::make('social_name')
                                        ->label('Nome Social')
                                        ->maxLength(255)
                                        ->columnSpan([
                                            'default' => 1,
                                            'md' => 4,
                                            'xl' => 5,
                                        ]),

                                    Select::make('status')
                                        ->label('Status')
                                        ->options([
                                            'active' => 'Ativo',
                                            'inactive' => 'Inativo',
                                            'terminated' => 'Desligado',
                                            'leave' => 'Afastado',
                                        ])
                                        ->default('active')
                                        ->required()
                                        ->columnSpan([
                                            'default' => 1,
                                            'md' => 3,
                                            'xl' => 2,
                                        ]),

                                    Toggle::make('is_active')
                                        ->label('Ativo')
                                        ->inline(false)
                                        ->default(true)
                                        ->columnSpan([
                                            'default' => 1,
                                            'md' => 3,
                                            'xl' => 2,
                                        ]),

                                    Select::make('company_id')
                                        ->label('Empresa')
                                        ->options(fn () => Company::query()
                                            ->orderBy('name')
                                            ->pluck('name', 'id')
                                            ->toArray())
                                        ->searchable()
                                        ->preload()
                                        ->required()
                                        ->native(false)
                                        ->live()
                                        ->columnSpan([
                                            'default' => 1,
                                            'md' => 6,
                                            'xl' => 4,
                                        ])
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
                                        ->required()
                                        ->native(false)
                                        ->live()
                                        ->columnSpan([
                                            'default' => 1,
                                            'md' => 3,
                                            'xl' => 4,
                                        ])
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
                                        ->native(false)
                                        ->columnSpan([
                                            'default' => 1,
                                            'md' => 3,
                                            'xl' => 4,
                                        ]),
                                ]),
                        ]),

                    Tab::make('Documentação')
                        ->icon('heroicon-o-document-text')
                        ->schema([
                            Section::make('Documentos principais')
                                ->columns([
                                    'default' => 1,
                                    'md' => 6,
                                    'xl' => 12,
                                ])
                                ->schema([
                                    TextInput::make('cpf')
                                        ->label('CPF')
                                        ->maxLength(14)
                                        ->mask(RawJs::make("'999.999.999-99'"))
                                        ->dehydrateStateUsing(fn ($state) => self::digits($state))
                                        ->columnSpan([
                                            'default' => 1,
                                            'md' => 2,
                                            'xl' => 3,
                                        ]),

                                    TextInput::make('rg')
                                        ->label('RG')
                                        ->maxLength(30)
                                        ->columnSpan([
                                            'default' => 1,
                                            'md' => 2,
                                            'xl' => 3,
                                        ]),

                                    TextInput::make('rg_issuer')
                                        ->label('Órgão Emissor')
                                        ->maxLength(20)
                                        ->columnSpan([
                                            'default' => 1,
                                            'md' => 2,
                                            'xl' => 3,
                                        ]),

                                    TextInput::make('pis')
                                        ->label('PIS')
                                        ->maxLength(14)
                                        ->mask(RawJs::make("'999.99999.99-9'"))
                                        ->dehydrateStateUsing(fn ($state) => self::digits($state))
                                        ->columnSpan([
                                            'default' => 1,
                                            'md' => 2,
                                            'xl' => 3,
                                        ]),

                                    TextInput::make('ctps')
                                        ->label('CTPS')
                                        ->maxLength(30)
                                        ->columnSpan([
                                            'default' => 1,
                                            'md' => 3,
                                            'xl' => 3,
                                        ]),

                                    TextInput::make('ctps_series')
                                        ->label('Série CTPS')
                                        ->maxLength(20)
                                        ->columnSpan([
                                            'default' => 1,
                                            'md' => 3,
                                            'xl' => 3,
                                        ]),
                                ]),
                        ]),

                    Tab::make('Dados Pessoais')
                        ->icon('heroicon-o-user')
                        ->schema([
                            Section::make('Informações pessoais')
                                ->columns([
                                    'default' => 1,
                                    'md' => 6,
                                    'xl' => 12,
                                ])
                                ->schema([
                                    DatePicker::make('birth_date')
                                        ->label('Data de Nascimento')
                                        ->columnSpan([
                                            'default' => 1,
                                            'md' => 2,
                                            'xl' => 3,
                                        ]),

                                    Select::make('gender')
                                        ->label('Sexo')
                                        ->options([
                                            'male' => 'Masculino',
                                            'female' => 'Feminino',
                                            'other' => 'Outro',
                                        ])
                                        ->columnSpan([
                                            'default' => 1,
                                            'md' => 2,
                                            'xl' => 3,
                                        ]),

                                    Select::make('marital_status')
                                        ->label('Estado Civil')
                                        ->options([
                                            'single' => 'Solteiro(a)',
                                            'married' => 'Casado(a)',
                                            'divorced' => 'Divorciado(a)',
                                            'widowed' => 'Viúvo(a)',
                                            'stable_union' => 'União Estável',
                                        ])
                                        ->columnSpan([
                                            'default' => 1,
                                            'md' => 2,
                                            'xl' => 3,
                                        ]),

                                    TextInput::make('nationality')
                                        ->label('Nacionalidade')
                                        ->maxLength(100)
                                        ->columnSpan([
                                            'default' => 1,
                                            'md' => 2,
                                            'xl' => 3,
                                        ]),

                                    TextInput::make('birthplace')
                                        ->label('Naturalidade')
                                        ->maxLength(100)
                                        ->columnSpan([
                                            'default' => 1,
                                            'md' => 2,
                                            'xl' => 4,
                                        ]),

                                    TextInput::make('mother_name')
                                        ->label('Nome da Mãe')
                                        ->maxLength(255)
                                        ->columnSpan([
                                            'default' => 1,
                                            'md' => 2,
                                            'xl' => 4,
                                        ]),

                                    TextInput::make('father_name')
                                        ->label('Nome do Pai')
                                        ->maxLength(255)
                                        ->columnSpan([
                                            'default' => 1,
                                            'md' => 2,
                                            'xl' => 4,
                                        ]),
                                ]),
                        ]),

                    Tab::make('Contato e Endereço')
                        ->icon('heroicon-o-map-pin')
                        ->schema([
                            Section::make('Contato')
                                ->columns([
                                    'default' => 1,
                                    'md' => 6,
                                    'xl' => 12,
                                ])
                                ->schema([
                                    TextInput::make('email')
                                        ->label('E-mail')
                                        ->email()
                                        ->maxLength(255)
                                        ->columnSpan([
                                            'default' => 1,
                                            'md' => 2,
                                            'xl' => 4,
                                        ]),

                                    TextInput::make('phone')
                                        ->label('Telefone')
                                        ->maxLength(15)
                                        ->mask(RawJs::make(<<<'JS'
                                            $input.length > 14 ? '(99) 99999-9999' : '(99) 9999-9999'
                                        JS))
                                        ->dehydrateStateUsing(fn ($state) => self::digits($state))
                                        ->columnSpan([
                                            'default' => 1,
                                            'md' => 2,
                                            'xl' => 4,
                                        ]),

                                    TextInput::make('mobile')
                                        ->label('Celular')
                                        ->maxLength(15)
                                        ->mask(RawJs::make("'(99) 99999-9999'"))
                                        ->dehydrateStateUsing(fn ($state) => self::digits($state))
                                        ->columnSpan([
                                            'default' => 1,
                                            'md' => 2,
                                            'xl' => 4,
                                        ]),
                                ]),

                            Section::make('Endereço')
                                ->columns([
                                    'default' => 1,
                                    'md' => 6,
                                    'xl' => 12,
                                ])
                                ->schema([
                                    TextInput::make('zip_code')
                                        ->label('CEP')
                                        ->maxLength(9)
                                        ->mask(RawJs::make("'99999-999'"))
                                        ->dehydrateStateUsing(fn ($state) => self::digits($state))
                                        ->columnSpan([
                                            'default' => 1,
                                            'md' => 2,
                                            'xl' => 2,
                                        ]),

                                    TextInput::make('address')
                                        ->label('Endereço')
                                        ->maxLength(255)
                                        ->columnSpan([
                                            'default' => 1,
                                            'md' => 4,
                                            'xl' => 5,
                                        ]),

                                    TextInput::make('number')
                                        ->label('Número')
                                        ->maxLength(20)
                                        ->columnSpan([
                                            'default' => 1,
                                            'md' => 2,
                                            'xl' => 2,
                                        ]),

                                    TextInput::make('complement')
                                        ->label('Complemento')
                                        ->maxLength(255)
                                        ->columnSpan([
                                            'default' => 1,
                                            'md' => 4,
                                            'xl' => 3,
                                        ]),

                                    TextInput::make('district')
                                        ->label('Bairro')
                                        ->maxLength(255)
                                        ->columnSpan([
                                            'default' => 1,
                                            'md' => 2,
                                            'xl' => 4,
                                        ]),

                                    TextInput::make('city')
                                        ->label('Cidade')
                                        ->maxLength(255)
                                        ->columnSpan([
                                            'default' => 1,
                                            'md' => 2,
                                            'xl' => 4,
                                        ]),

                                    TextInput::make('state')
                                        ->label('UF')
                                        ->maxLength(2)
                                        ->columnSpan([
                                            'default' => 1,
                                            'md' => 2,
                                            'xl' => 2,
                                        ]),
                                ]),
                        ]),

                    Tab::make('Dados Funcionais')
                        ->icon('heroicon-o-briefcase')
                        ->schema([
                            Section::make('Lotação e cadastro funcional')
                                ->columns([
                                    'default' => 1,
                                    'md' => 6,
                                    'xl' => 12,
                                ])
                                ->schema([
                                    Select::make('department_id')
                                        ->label('Departamento')
                                        ->options(fn () => Department::query()->orderBy('name')->pluck('name', 'id')->toArray())
                                        ->searchable()
                                        ->preload()
                                        ->native(false)
                                        ->columnSpan([
                                            'default' => 1,
                                            'md' => 2,
                                            'xl' => 3,
                                        ]),

                                    Select::make('cost_center_id')
                                        ->label('Centro de Custo')
                                        ->options(fn () => CostCenter::query()->orderBy('name')->pluck('name', 'id')->toArray())
                                        ->searchable()
                                        ->preload()
                                        ->native(false)
                                        ->columnSpan([
                                            'default' => 1,
                                            'md' => 2,
                                            'xl' => 3,
                                        ]),

                                    Select::make('job_role_id')
                                        ->label('Cargo')
                                        ->options(fn () => JobRole::query()->orderBy('name')->pluck('name', 'id')->toArray())
                                        ->searchable()
                                        ->preload()
                                        ->native(false)
                                        ->columnSpan([
                                            'default' => 1,
                                            'md' => 2,
                                            'xl' => 3,
                                        ]),

                                    Select::make('cbo_code_id')
                                        ->label('CBO')
                                        ->options(fn () => CboCode::query()->orderBy('code')->pluck('name', 'id')->toArray())
                                        ->searchable()
                                        ->preload()
                                        ->native(false)
                                        ->columnSpan([
                                            'default' => 1,
                                            'md' => 2,
                                            'xl' => 3,
                                        ]),

                                    Select::make('labor_union_id')
                                        ->label('Sindicato')
                                        ->options(fn () => LaborUnion::query()->orderBy('name')->pluck('name', 'id')->toArray())
                                        ->searchable()
                                        ->preload()
                                        ->native(false)
                                        ->columnSpan([
                                            'default' => 1,
                                            'md' => 3,
                                            'xl' => 4,
                                        ]),

                                    Select::make('work_shift_id')
                                        ->label('Jornada de Trabalho')
                                        ->options(fn () => WorkShift::query()->orderBy('name')->pluck('name', 'id')->toArray())
                                        ->searchable()
                                        ->preload()
                                        ->native(false)
                                        ->columnSpan([
                                            'default' => 1,
                                            'md' => 3,
                                            'xl' => 4,
                                        ]),

                                    DatePicker::make('admission_date')
                                        ->label('Data de Admissão')
                                        ->live()
                                        ->columnSpan([
                                            'default' => 1,
                                            'md' => 3,
                                            'xl' => 2,
                                        ])
                                        ->afterStateUpdated(function (Set $set, Get $get, $state): void {
                                            if ($state && ! $get('contract_start_date')) {
                                                $set('contract_start_date', $state);
                                            }

                                            if ($get('contract_term_type') === 'fixed') {
                                                self::recalculateServiceContractDates($set, $get);
                                            }

                                            if ($get('has_experience_period')) {
                                                $set('experience_start_date', $state);
                                                self::recalculateExperienceDates($set, $get);
                                            }
                                        }),

                                    DatePicker::make('termination_date')
                                        ->label('Data de Desligamento')
                                        ->columnSpan([
                                            'default' => 1,
                                            'md' => 3,
                                            'xl' => 2,
                                        ]),
                                ]),

                            Section::make('Remuneração e contrato')
                                ->columns([
                                    'default' => 1,
                                    'md' => 6,
                                    'xl' => 12,
                                ])
                                ->schema([
                                    TextInput::make('salary')
                                        ->label('Salário Base')
                                        ->required()
                                        ->mask(RawJs::make('$money($input, ",", ".", 2)'))
                                        ->prefix('R$ ')
                                        ->dehydrateStateUsing(fn ($state) => self::moneyToDatabase($state))
                                        ->formatStateUsing(fn ($state) => self::moneyFromDatabase($state))
                                        ->columnSpan([
                                            'default' => 1,
                                            'md' => 2,
                                            'xl' => 3,
                                        ]),

                                    TextInput::make('salary_advance_amount')
                                        ->label('Adiantamento Salarial')
                                        ->mask(RawJs::make('$money($input, ",", ".", 2)'))
                                        ->prefix('R$ ')
                                        ->default('0,00')
                                        ->dehydrateStateUsing(fn ($state) => self::moneyToDatabase($state) ?? 0)
                                        ->formatStateUsing(fn ($state) => self::moneyFromDatabase($state ?? 0))
                                        ->columnSpan([
                                            'default' => 1,
                                            'md' => 2,
                                            'xl' => 3,
                                        ]),

                                    Select::make('payment_method')
                                        ->label('Forma de Pagamento')
                                        ->options([
                                            'pix' => 'PIX',
                                            'transfer' => 'Transferência',
                                            'bank_deposit' => 'Depósito Bancário',
                                            'cash' => 'Dinheiro',
                                        ])
                                        ->native(false)
                                        ->columnSpan([
                                            'default' => 1,
                                            'md' => 2,
                                            'xl' => 3,
                                        ]),

                                    Select::make('contract_type_id')
                                        ->label('Tipo de Contrato')
                                        ->options(fn () => ContractType::query()
                                            ->where('is_active', true)
                                            ->orderBy('sort_order')
                                            ->orderBy('name')
                                            ->pluck('name', 'id')
                                            ->toArray())
                                        ->searchable()
                                        ->preload()
                                        ->required()
                                        ->native(false)
                                        ->live()
                                        ->columnSpan([
                                            'default' => 1,
                                            'md' => 2,
                                            'xl' => 3,
                                        ])
                                        ->afterStateUpdated(function (?string $state, Set $set, Get $get): void {
                                            $rules = ContractProcessingRuleService::getByContractTypeId(
                                                $state ? (int) $state : null
                                            );

                                            $hasFgts = (bool) ($rules['has_fgts'] ?? false);
                                            $hasInss = (bool) ($rules['has_inss'] ?? true);

                                            $set('processing_type', $rules['processing_type'] ?? 'payroll');
                                            $set('generates_payroll', (bool) ($rules['generates_payroll'] ?? true));
                                            $set('generates_accounts_payable', (bool) ($rules['generates_accounts_payable'] ?? false));
                                            $set('allows_payslip', (bool) ($rules['allows_payslip'] ?? true));

                                            $set('has_fgts', $hasFgts);
                                            $set('fgts_rate', $hasFgts ? (float) ($rules['fgts_rate'] ?? 8) : 0);

                                            $set('has_inss', $hasInss);
                                            $set('inss_optional', (bool) ($rules['inss_optional'] ?? false));
                                            $set('with_inss', $hasInss ? (bool) ($rules['with_inss'] ?? true) : false);

                                            $set('has_irrf', (bool) ($rules['has_irrf'] ?? true));

                                            if (self::isServiceContract($state ? (int) $state : null)) {
                                                if (! $get('contract_term_type')) {
                                                    $set('contract_term_type', 'indeterminate');
                                                }

                                                if (! $get('contract_start_date') && $get('admission_date')) {
                                                    $set('contract_start_date', $get('admission_date'));
                                                }

                                                self::recalculateServiceContractDates($set, $get);
                                            } else {
                                                $set('contract_term_type', null);
                                                $set('contract_term_days', null);
                                                $set('contract_start_date', null);
                                                $set('contract_end_date', null);
                                            }

                                            if (! self::isCltContract($state ? (int) $state : null)) {
                                                $set('has_experience_period', false);
                                                $set('experience_model', null);
                                                $set('experience_days_first', null);
                                                $set('experience_days_second', null);
                                                $set('experience_total_days', null);
                                                $set('experience_start_date', null);
                                                $set('experience_end_date', null);
                                            } elseif ($get('has_experience_period')) {
                                                if (! $get('experience_start_date') && $get('admission_date')) {
                                                    $set('experience_start_date', $get('admission_date'));
                                                }

                                                self::applyExperienceModel($set, $get, $get('experience_model'));
                                            }
                                        }),

                                    Section::make('Contrato PF / PJ')
                                        ->description('Controle de prazo para contratos PF, PJ, autônomo e RPA, com início pela admissão e término calculado automaticamente.')
                                        ->columns([
                                            'default' => 1,
                                            'md' => 6,
                                            'xl' => 12,
                                        ])
                                        ->visible(fn (Get $get) => self::isServiceContract($get('contract_type_id') ? (int) $get('contract_type_id') : null))
                                        ->schema([
                                            Select::make('contract_term_type')
                                                ->label('Tipo de Prazo')
                                                ->options([
                                                    'indeterminate' => 'Indeterminado',
                                                    'fixed' => 'Determinado',
                                                ])
                                                ->default('indeterminate')
                                                ->native(false)
                                                ->live()
                                                ->dehydrated(true)
                                                ->afterStateHydrated(function (Set $set, Get $get, $state): void {
                                                    if (! $state && self::isServiceContract($get('contract_type_id') ? (int) $get('contract_type_id') : null)) {
                                                        $set('contract_term_type', 'indeterminate');
                                                    }

                                                    if (! $get('contract_start_date') && $get('admission_date')) {
                                                        $set('contract_start_date', $get('admission_date'));
                                                    }

                                                    self::recalculateServiceContractDates($set, $get);
                                                })
                                                ->afterStateUpdated(function (Set $set, Get $get, $state): void {
                                                    if ($state === 'indeterminate') {
                                                        $set('contract_term_days', null);
                                                        $set('contract_end_date', null);
                                                    }

                                                    if (! $get('contract_start_date') && $get('admission_date')) {
                                                        $set('contract_start_date', $get('admission_date'));
                                                    }

                                                    self::recalculateServiceContractDates($set, $get);
                                                })
                                                ->columnSpan([
                                                    'default' => 1,
                                                    'md' => 2,
                                                    'xl' => 3,
                                                ]),

                                            Select::make('contract_term_days')
                                                ->label('Prazo do Contrato')
                                                ->options([
                                                    30 => '30 dias',
                                                    45 => '45 dias',
                                                    60 => '60 dias',
                                                    90 => '90 dias',
                                                    120 => '120 dias',
                                                    180 => '180 dias',
                                                ])
                                                ->native(false)
                                                ->live()
                                                ->dehydrated(true)
                                                ->visible(fn (Get $get) => $get('contract_term_type') === 'fixed')
                                                ->required(fn (Get $get) => $get('contract_term_type') === 'fixed')
                                                ->afterStateUpdated(function (Set $set, Get $get): void {
                                                    if (! $get('contract_start_date') && $get('admission_date')) {
                                                        $set('contract_start_date', $get('admission_date'));
                                                    }

                                                    self::recalculateServiceContractDates($set, $get);
                                                })
                                                ->columnSpan([
                                                    'default' => 1,
                                                    'md' => 2,
                                                    'xl' => 3,
                                                ]),

                                            DatePicker::make('contract_start_date')
                                                ->label('Início do Contrato')
                                                ->native(false)
                                                ->live()
                                                ->dehydrated(true)
                                                ->afterStateHydrated(function (Set $set, Get $get, $state): void {
                                                    if (! $state && $get('admission_date')) {
                                                        $set('contract_start_date', $get('admission_date'));
                                                    }

                                                    self::recalculateServiceContractDates($set, $get);
                                                })
                                                ->afterStateUpdated(function (Set $set, Get $get): void {
                                                    self::recalculateServiceContractDates($set, $get);
                                                })
                                                ->columnSpan([
                                                    'default' => 1,
                                                    'md' => 2,
                                                    'xl' => 3,
                                                ]),

                                            DatePicker::make('contract_end_date')
                                                ->label('Fim do Contrato')
                                                ->native(false)
                                                ->disabled()
                                                ->dehydrated(true)
                                                ->visible(fn (Get $get) => $get('contract_term_type') === 'fixed')
                                                ->columnSpan([
                                                    'default' => 1,
                                                    'md' => 2,
                                                    'xl' => 3,
                                                ]),
                                        ])
                                        ->columnSpanFull(),

                                    Toggle::make('has_experience_period')
                                        ->label('Contrato com Experiência')
                                        ->inline(false)
                                        ->default(false)
                                        ->live()
                                        ->visible(fn (Get $get) => self::isCltContract($get('contract_type_id') ? (int) $get('contract_type_id') : null))
                                        ->afterStateUpdated(function (Set $set, Get $get, $state): void {
                                            if (! $state) {
                                                $set('experience_model', null);
                                                $set('experience_days_first', null);
                                                $set('experience_days_second', null);
                                                $set('experience_total_days', null);
                                                $set('experience_start_date', null);
                                                $set('experience_end_date', null);
                                                return;
                                            }

                                            $admissionDate = $get('admission_date');

                                            if ($admissionDate) {
                                                $set('experience_start_date', $admissionDate);
                                            }

                                            self::applyExperienceModel($set, $get, $get('experience_model'));
                                        })
                                        ->columnSpan([
                                            'default' => 1,
                                            'md' => 2,
                                            'xl' => 3,
                                        ]),

                                    Select::make('experience_model')
                                        ->label('Modelo da Experiência')
                                        ->options([
                                            '30' => '30 dias',
                                            '30_30' => '30 + 30',
                                            '30_60' => '30 + 60',
                                            '45_45' => '45 + 45',
                                            '60_30' => '60 + 30',
                                            'manual' => 'Manual',
                                        ])
                                        ->native(false)
                                        ->live()
                                        ->visible(fn (Get $get) =>
                                            self::isCltContract($get('contract_type_id') ? (int) $get('contract_type_id') : null)
                                            && (bool) $get('has_experience_period')
                                        )
                                        ->afterStateUpdated(function (Set $set, Get $get, $state): void {
                                            self::applyExperienceModel($set, $get, $state);
                                        })
                                        ->columnSpan([
                                            'default' => 1,
                                            'md' => 2,
                                            'xl' => 3,
                                        ]),

                                    TextInput::make('experience_days_first')
                                        ->label('1ª Etapa (dias)')
                                        ->numeric()
                                        ->live()
                                        ->dehydrated(true)
                                        ->visible(fn (Get $get) =>
                                            self::isCltContract($get('contract_type_id') ? (int) $get('contract_type_id') : null)
                                            && (bool) $get('has_experience_period')
                                        )
                                        ->disabled(fn (Get $get) => $get('experience_model') !== 'manual')
                                        ->afterStateUpdated(function (Set $set, Get $get): void {
                                            self::recalculateExperienceDates($set, $get);
                                        })
                                        ->columnSpan([
                                            'default' => 1,
                                            'md' => 1,
                                            'xl' => 2,
                                        ]),

                                    TextInput::make('experience_days_second')
                                        ->label('2ª Etapa (dias)')
                                        ->numeric()
                                        ->live()
                                        ->dehydrated(true)
                                        ->visible(fn (Get $get) =>
                                            self::isCltContract($get('contract_type_id') ? (int) $get('contract_type_id') : null)
                                            && (bool) $get('has_experience_period')
                                        )
                                        ->disabled(fn (Get $get) => $get('experience_model') !== 'manual')
                                        ->afterStateUpdated(function (Set $set, Get $get): void {
                                            self::recalculateExperienceDates($set, $get);
                                        })
                                        ->columnSpan([
                                            'default' => 1,
                                            'md' => 1,
                                            'xl' => 2,
                                        ]),

                                    TextInput::make('experience_total_days')
                                        ->label('Total (dias)')
                                        ->numeric()
                                        ->disabled()
                                        ->dehydrated(true)
                                        ->visible(fn (Get $get) =>
                                            self::isCltContract($get('contract_type_id') ? (int) $get('contract_type_id') : null)
                                            && (bool) $get('has_experience_period')
                                        )
                                        ->columnSpan([
                                            'default' => 1,
                                            'md' => 1,
                                            'xl' => 2,
                                        ]),

                                    DatePicker::make('experience_start_date')
                                        ->label('Início da Experiência')
                                        ->native(false)
                                        ->live()
                                        ->visible(fn (Get $get) =>
                                            self::isCltContract($get('contract_type_id') ? (int) $get('contract_type_id') : null)
                                            && (bool) $get('has_experience_period')
                                        )
                                        ->afterStateUpdated(function (Set $set, Get $get): void {
                                            self::recalculateExperienceDates($set, $get);
                                        })
                                        ->columnSpan([
                                            'default' => 1,
                                            'md' => 2,
                                            'xl' => 2,
                                        ]),

                                    DatePicker::make('experience_end_date')
                                        ->label('Fim da Experiência')
                                        ->native(false)
                                        ->disabled()
                                        ->dehydrated(true)
                                        ->visible(fn (Get $get) =>
                                            self::isCltContract($get('contract_type_id') ? (int) $get('contract_type_id') : null)
                                            && (bool) $get('has_experience_period')
                                        )
                                        ->columnSpan([
                                            'default' => 1,
                                            'md' => 2,
                                            'xl' => 2,
                                        ]),
                                ]),
                        ]),

                    Tab::make('Banco / PIX')
                        ->icon('heroicon-o-building-library')
                        ->schema([
                            Section::make('Dados bancários')
                                ->columns([
                                    'default' => 1,
                                    'md' => 6,
                                    'xl' => 12,
                                ])
                                ->schema([
                                    Select::make('bank_id')
                                        ->label('Banco')
                                        ->options(fn () => Bank::query()->orderBy('name')->pluck('name', 'id')->toArray())
                                        ->searchable()
                                        ->preload()
                                        ->native(false)
                                        ->columnSpan([
                                            'default' => 1,
                                            'md' => 2,
                                            'xl' => 3,
                                        ]),

                                    TextInput::make('bank_agency')
                                        ->label('Agência')
                                        ->maxLength(20)
                                        ->columnSpan([
                                            'default' => 1,
                                            'md' => 2,
                                            'xl' => 3,
                                        ]),

                                    TextInput::make('bank_account')
                                        ->label('Conta')
                                        ->maxLength(30)
                                        ->columnSpan([
                                            'default' => 1,
                                            'md' => 2,
                                            'xl' => 3,
                                        ]),

                                    TextInput::make('bank_account_digit')
                                        ->label('Dígito')
                                        ->maxLength(10)
                                        ->columnSpan([
                                            'default' => 1,
                                            'md' => 2,
                                            'xl' => 1,
                                        ]),

                                    Select::make('bank_account_type')
                                        ->label('Tipo de Conta')
                                        ->options([
                                            'checking' => 'Corrente',
                                            'savings' => 'Poupança',
                                            'salary' => 'Salário',
                                        ])
                                        ->native(false)
                                        ->columnSpan([
                                            'default' => 1,
                                            'md' => 2,
                                            'xl' => 2,
                                        ]),
                                ]),

                            Section::make('Dados PIX')
                                ->columns([
                                    'default' => 1,
                                    'md' => 6,
                                    'xl' => 12,
                                ])
                                ->schema([
                                    Select::make('pix_key_type')
                                        ->label('Tipo de Chave PIX')
                                        ->options([
                                            'cpf' => 'CPF',
                                            'cnpj' => 'CNPJ',
                                            'email' => 'E-mail',
                                            'phone' => 'Telefone',
                                            'random' => 'Aleatória',
                                        ])
                                        ->native(false)
                                        ->live()
                                        ->columnSpan([
                                            'default' => 1,
                                            'md' => 2,
                                            'xl' => 3,
                                        ])
                                        ->afterStateUpdated(function (Set $set): void {
                                            $set('pix_key', null);
                                            $set('pix_holder_document', null);
                                        }),

                                    TextInput::make('pix_key')
                                        ->label('Chave PIX')
                                        ->maxLength(255)
                                        ->mask(fn (Get $get) => match ($get('pix_key_type')) {
                                            'cpf' => RawJs::make("'999.999.999-99'"),
                                            'cnpj' => RawJs::make("'99.999.999/9999-99'"),
                                            'phone' => RawJs::make("'(99) 99999-9999'"),
                                            default => null,
                                        })
                                        ->placeholder(fn (Get $get) => match ($get('pix_key_type')) {
                                            'cpf' => '000.000.000-00',
                                            'cnpj' => '00.000.000/0000-00',
                                            'email' => 'email@exemplo.com',
                                            'phone' => '(00) 00000-0000',
                                            'random' => 'Chave aleatória',
                                            default => 'Selecione o tipo de chave',
                                        })
                                        ->helperText(fn (Get $get) => match ($get('pix_key_type')) {
                                            'cpf' => 'Informe a chave PIX no formato CPF.',
                                            'cnpj' => 'Informe a chave PIX no formato CNPJ.',
                                            'email' => 'Informe uma chave PIX do tipo e-mail.',
                                            'phone' => 'Informe a chave PIX no formato celular.',
                                            'random' => 'Informe a chave aleatória completa.',
                                            default => 'Escolha o tipo de chave para aplicar a máscara correta.',
                                        })
                                        ->dehydrateStateUsing(function ($state, Get $get) {
                                            return match ($get('pix_key_type')) {
                                                'cpf', 'cnpj', 'phone' => self::digits($state),
                                                'email' => filled($state) ? trim(mb_strtolower((string) $state)) : null,
                                                default => filled($state) ? trim((string) $state) : null,
                                            };
                                        })
                                        ->columnSpan([
                                            'default' => 1,
                                            'md' => 2,
                                            'xl' => 3,
                                        ]),

                                    TextInput::make('pix_holder_name')
                                        ->label('Titular da Chave PIX')
                                        ->maxLength(255)
                                        ->columnSpan([
                                            'default' => 1,
                                            'md' => 2,
                                            'xl' => 4,
                                        ]),

                                    TextInput::make('pix_holder_document')
                                        ->label('Documento do Titular')
                                        ->maxLength(18)
                                        ->mask(fn (Get $get) => match ($get('pix_key_type')) {
                                            'cnpj' => RawJs::make("'99.999.999/9999-99'"),
                                            default => RawJs::make("'999.999.999-99'"),
                                        })
                                        ->placeholder(fn (Get $get) => $get('pix_key_type') === 'cnpj'
                                            ? '00.000.000/0000-00'
                                            : '000.000.000-00')
                                        ->helperText('A máscara muda conforme o tipo de documento esperado.')
                                        ->dehydrateStateUsing(fn ($state) => self::digits($state))
                                        ->columnSpan([
                                            'default' => 1,
                                            'md' => 2,
                                            'xl' => 2,
                                        ]),
                                ]),
                        ]),

                    Tab::make('Processamento')
                        ->icon('heroicon-o-cog-6-tooth')
                        ->schema([
                            Section::make('Regras automáticas do vínculo')
                                ->description('Esses campos podem ser ajustados manualmente quando necessário.')
                                ->columns([
                                    'default' => 1,
                                    'md' => 6,
                                    'xl' => 12,
                                ])
                                ->schema([
                                    Select::make('processing_type')
                                        ->label('Tipo de Processamento')
                                        ->options(ContractProcessingRuleService::processingTypeOptions())
                                        ->default('payroll')
                                        ->required()
                                        ->native(false)
                                        ->dehydrated(true)
                                        ->columnSpan([
                                            'default' => 1,
                                            'md' => 2,
                                            'xl' => 3,
                                        ]),

                                    Toggle::make('generates_payroll')
                                        ->label('Gera Folha')
                                        ->inline(false)
                                        ->default(true)
                                        ->dehydrated(true)
                                        ->columnSpan([
                                            'default' => 1,
                                            'md' => 1,
                                            'xl' => 2,
                                        ]),

                                    Toggle::make('generates_accounts_payable')
                                        ->label('Gera Contas a Pagar')
                                        ->inline(false)
                                        ->default(false)
                                        ->dehydrated(true)
                                        ->columnSpan([
                                            'default' => 1,
                                            'md' => 1,
                                            'xl' => 2,
                                        ]),

                                    Toggle::make('allows_payslip')
                                        ->label('Permite Holerite / Comprovante')
                                        ->inline(false)
                                        ->default(true)
                                        ->dehydrated(true)
                                        ->columnSpan([
                                            'default' => 1,
                                            'md' => 1,
                                            'xl' => 2,
                                        ]),

                                    Toggle::make('has_fgts')
                                        ->label('Tem FGTS')
                                        ->inline(false)
                                        ->default(true)
                                        ->live()
                                        ->afterStateHydrated(function (Set $set, $state, ?\App\Models\Employee $record) {
                                            if (! $state) {
                                                $set('fgts_rate', 0);
                                                return;
                                            }

                                            $set('fgts_rate', $record?->fgts_rate ?? 8);
                                        })
                                        ->afterStateUpdated(function (Set $set, $state, Get $get): void {
                                            if (! $state) {
                                                $set('fgts_rate', 0);
                                                return;
                                            }

                                            $current = $get('fgts_rate');
                                            $set('fgts_rate', filled($current) ? $current : 8);
                                        })
                                        ->dehydrated(true)
                                        ->columnSpan([
                                            'default' => 1,
                                            'md' => 1,
                                            'xl' => 1,
                                        ]),

                                    TextInput::make('fgts_rate')
                                        ->label('Alíquota FGTS (%)')
                                        ->numeric()
                                        ->default(8)
                                        ->disabled(fn (Get $get) => ! $get('has_fgts'))
                                        ->dehydrated(true)
                                        ->dehydrateStateUsing(function ($state, Get $get) {
                                            return $get('has_fgts') ? ((float) ($state ?: 8)) : 0;
                                        })
                                        ->columnSpan([
                                            'default' => 1,
                                            'md' => 1,
                                            'xl' => 2,
                                        ]),

                                    Toggle::make('has_inss')
                                        ->label('Tem INSS')
                                        ->inline(false)
                                        ->default(true)
                                        ->live()
                                        ->dehydrated(true)
                                        ->afterStateUpdated(function (Set $set, $state): void {
                                            if (! $state) {
                                                $set('inss_optional', false);
                                                $set('with_inss', false);
                                            }
                                        })
                                        ->columnSpan([
                                            'default' => 1,
                                            'md' => 1,
                                            'xl' => 1,
                                        ]),

                                    Toggle::make('inss_optional')
                                        ->label('INSS Opcional')
                                        ->inline(false)
                                        ->default(false)
                                        ->visible(fn (Get $get) => (bool) $get('has_inss'))
                                        ->dehydrated(true)
                                        ->columnSpan([
                                            'default' => 1,
                                            'md' => 1,
                                            'xl' => 1,
                                        ]),

                                    Toggle::make('with_inss')
                                        ->label('Reter INSS')
                                        ->inline(false)
                                        ->default(true)
                                        ->visible(fn (Get $get) => (bool) $get('has_inss'))
                                        ->dehydrated(true)
                                        ->columnSpan([
                                            'default' => 1,
                                            'md' => 1,
                                            'xl' => 1,
                                        ]),

                                    Toggle::make('has_irrf')
                                        ->label('Tem IRRF')
                                        ->inline(false)
                                        ->default(true)
                                        ->dehydrated(true)
                                        ->columnSpan([
                                            'default' => 1,
                                            'md' => 1,
                                            'xl' => 1,
                                        ]),
                                ]),
                        ]),

                    Tab::make('Observações')
                        ->icon('heroicon-o-chat-bubble-left-ellipsis')
                        ->schema([
                            Section::make('Informações complementares')
                                ->columns([
                                    'default' => 1,
                                    'md' => 6,
                                    'xl' => 12,
                                ])
                                ->schema([
                                    Textarea::make('notes')
                                        ->label('Observações')
                                        ->rows(6)
                                        ->columnSpanFull(),
                                ]),
                        ]),
                ])
                ->persistTabInQueryString(),
        ]);
    }

    protected static function digits(?string $value): ?string
    {
        if (blank($value)) {
            return null;
        }

        return preg_replace('/\D+/', '', $value);
    }

    protected static function moneyToDatabase($state): ?float
    {
        if ($state === null || $state === '') {
            return null;
        }

        $value = trim((string) $state);

        if (preg_match('/^\d+(\.\d{1,2})?$/', $value)) {
            return (float) $value;
        }

        $value = str_replace('.', '', $value);
        $value = str_replace(',', '.', $value);

        return is_numeric($value) ? (float) $value : null;
    }

    protected static function moneyFromDatabase($state): ?string
    {
        if ($state === null || $state === '') {
            return null;
        }

        return number_format((float) $state, 2, ',', '.');
    }

    protected static function isCltContract(?int $contractTypeId): bool
    {
        if (! $contractTypeId) {
            return false;
        }

        $contractType = ContractType::find($contractTypeId);

        if (! $contractType) {
            return false;
        }

        $name = mb_strtolower(trim((string) $contractType->name));

        return str_contains($name, 'clt');
    }

    protected static function isServiceContract(?int $contractTypeId): bool
    {
        if (! $contractTypeId) {
            return false;
        }

        $contractType = ContractType::find($contractTypeId);

        if (! $contractType) {
            return false;
        }

        $name = mb_strtolower(trim((string) $contractType->name));

        return str_contains($name, 'pf')
            || str_contains($name, 'pj')
            || str_contains($name, 'pessoa física')
            || str_contains($name, 'pessoa fisica')
            || str_contains($name, 'pessoa jurídica')
            || str_contains($name, 'pessoa juridica')
            || str_contains($name, 'autônomo')
            || str_contains($name, 'autonomo')
            || str_contains($name, 'rpa');
    }

    protected static function recalculateServiceContractDates(Set $set, Get $get): void
    {
        if ($get('contract_term_type') !== 'fixed') {
            $set('contract_end_date', null);
            return;
        }

        $days = (int) ($get('contract_term_days') ?: 0);
        $allowedDays = [30, 45, 60, 90, 120, 180];

        if (! in_array($days, $allowedDays, true)) {
            $set('contract_end_date', null);
            return;
        }

        $startDate = $get('contract_start_date') ?: $get('admission_date');

        if (! $startDate) {
            $set('contract_end_date', null);
            return;
        }

        $set('contract_start_date', $startDate);
        $set('contract_end_date', Carbon::parse($startDate)
            ->addDays($days - 1)
            ->format('Y-m-d'));
    }

        protected static function applyExperienceModel(Set $set, Get $get, ?string $model): void
    {
        if (! $model) {
            $set('experience_days_first', null);
            $set('experience_days_second', null);
            $set('experience_total_days', null);
            $set('experience_end_date', null);
            return;
        }

        if ($model === '30') {
            $set('experience_days_first', 30);
            $set('experience_days_second', 0);
        }

        if ($model === '30_30') {
            $set('experience_days_first', 30);
            $set('experience_days_second', 30);
        }
        if ($model === '30_60') {
            $set('experience_days_first', 30);
            $set('experience_days_second', 60);
        }

        if ($model === '45_45') {
            $set('experience_days_first', 45);
            $set('experience_days_second', 45);
        }

        if ($model === '60_30') {
            $set('experience_days_first', 60);
            $set('experience_days_second', 30);
        }

        if ($model === 'manual') {
            $set('experience_days_first', $get('experience_days_first') ?: null);
            $set('experience_days_second', $get('experience_days_second') ?: null);
        }

        self::recalculateExperienceDates($set, $get);
    }

    protected static function recalculateExperienceDates(Set $set, Get $get): void
{
    $first = (int) ($get('experience_days_first') ?: 0);
    $second = (int) ($get('experience_days_second') ?: 0);

    $total = $first + $second;

    if ($total > 90) {
        $total = 90;
    }

    $set('experience_total_days', $total);

    $startDate = $get('experience_start_date') ?: $get('admission_date');

    if (! $startDate || $total <= 0) {
        $set('experience_end_date', null);
        return;
    }

    $endDate = Carbon::parse($startDate)
        ->addDays($total - 1)
        ->format('Y-m-d');

    $set('experience_end_date', $endDate);
}
}