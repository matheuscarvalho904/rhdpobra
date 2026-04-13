<?php

namespace App\Filament\Resources\SalaryAdvances\Schemas;

use App\Models\Branch;
use App\Models\Company;
use App\Models\Employee;
use App\Models\PayrollCompetency;
use App\Models\Work;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\RawJs;

class SalaryAdvanceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Dados do Adiantamento')
                    ->columns([
                        'default' => 1,
                        'md' => 6,
                        'xl' => 12,
                    ])
                    ->schema([
                        Select::make('company_id')
                            ->label('Empresa')
                            ->options(
                                Company::query()
                                    ->where('is_active', true)
                                    ->orderBy('name')
                                    ->pluck('name', 'id')
                            )
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->required()
                            ->live()
                            ->afterStateUpdated(function (Set $set) {
                                $set('branch_id', null);
                                $set('work_id', null);
                                $set('employee_id', null);

                                self::resetPaymentData($set);
                            })
                            ->columnSpan([
                                'default' => 1,
                                'md' => 6,
                                'xl' => 6,
                            ]),

                        Select::make('branch_id')
                            ->label('Filial')
                            ->options(fn (Get $get) => Branch::query()
                                ->when($get('company_id'), fn ($query, $companyId) => $query->where('company_id', $companyId))
                                ->where('is_active', true)
                                ->orderBy('name')
                                ->pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->live()
                            ->afterStateUpdated(function (Set $set) {
                                $set('work_id', null);
                                $set('employee_id', null);

                                self::resetPaymentData($set);
                            })
                            ->columnSpan([
                                'default' => 1,
                                'md' => 6,
                                'xl' => 6,
                            ]),

                        Select::make('work_id')
                            ->label('Obra')
                            ->options(fn (Get $get) => Work::query()
                                ->when($get('company_id'), fn ($query, $companyId) => $query->where('company_id', $companyId))
                                ->when($get('branch_id'), fn ($query, $branchId) => $query->where('branch_id', $branchId))
                                ->where('is_active', true)
                                ->orderBy('name')
                                ->pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->live()
                            ->afterStateUpdated(function (Set $set) {
                                $set('employee_id', null);

                                self::resetPaymentData($set);
                            })
                            ->columnSpan([
                                'default' => 1,
                                'md' => 6,
                                'xl' => 4,
                            ]),

                        Select::make('employee_id')
                            ->label('Colaborador')
                            ->options(fn (Get $get) => Employee::query()
                                ->when($get('company_id'), fn ($query, $companyId) => $query->where('company_id', $companyId))
                                ->when($get('branch_id'), fn ($query, $branchId) => $query->where('branch_id', $branchId))
                                ->when($get('work_id'), fn ($query, $workId) => $query->where('work_id', $workId))
                                ->where('is_active', true)
                                ->where('status', 'active')
                                ->orderBy('name')
                                ->pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, Set $set) {
                                if (! $state) {
                                    self::resetPaymentData($set);
                                    return;
                                }

                                $employee = Employee::find($state);

                                if (! $employee) {
                                    self::resetPaymentData($set);
                                    return;
                                }

                                self::fillPaymentDataFromEmployee($set, $employee);
                            })
                            ->columnSpan([
                                'default' => 1,
                                'md' => 6,
                                'xl' => 6,
                            ]),

                        Select::make('payroll_competency_id')
                            ->label('Competência')
                            ->options(
                                PayrollCompetency::query()
                                    ->orderByDesc('year')
                                    ->orderByDesc('month')
                                    ->get()
                                    ->mapWithKeys(fn ($item) => [
                                        $item->id => sprintf(
                                            '%02d/%04d - %s',
                                            $item->month,
                                            $item->year,
                                            match ($item->type) {
                                                'monthly' => 'Mensal',
                                                'vacation' => 'Férias',
                                                'thirteenth' => '13º',
                                                'termination' => 'Rescisão',
                                                'advance' => 'Adiantamento',
                                                default => $item->type,
                                            }
                                        ),
                                    ])
                            )
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->columnSpan([
                                'default' => 1,
                                'md' => 6,
                                'xl' => 3,
                            ]),

                        DatePicker::make('advance_date')
                            ->label('Data do Adiantamento')
                            ->required()
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->default(now())
                            ->columnSpan([
                                'default' => 1,
                                'md' => 3,
                                'xl' => 2,
                            ]),

                        TextInput::make('amount')
                            ->label('Valor')
                            ->required()
                            ->prefix('R$')
                            ->mask(RawJs::make(<<<'JS'
                                $money($input, ',', '.', 2)
                            JS))
                            ->formatStateUsing(fn ($state) => self::formatMoneyForInput($state))
                            ->dehydrateStateUsing(fn ($state) => self::normalizeMoneyToDatabase($state))
                            ->default('0,00')
                            ->columnSpan([
                                'default' => 1,
                                'md' => 3,
                                'xl' => 2,
                            ]),

                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'draft' => 'Rascunho',
                                'paid' => 'Pago',
                                'canceled' => 'Cancelado',
                                'integrated_payroll' => 'Integrado na Folha',
                            ])
                            ->default('draft')
                            ->native(false)
                            ->required()
                            ->columnSpan([
                                'default' => 1,
                                'md' => 3,
                                'xl' => 2,
                            ]),

                        Select::make('payment_method')
                            ->label('Forma de Pagamento')
                            ->options([
                                'pix' => 'PIX',
                                'bank_transfer' => 'Transferência',
                                'cash' => 'Dinheiro',
                            ])
                            ->default('pix')
                            ->native(false)
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                if ($state !== 'pix') {
                                    self::clearPixFields($set);
                                    return;
                                }

                                $employeeId = $get('employee_id');

                                if (! $employeeId) {
                                    self::clearPixFields($set);
                                    return;
                                }

                                $employee = Employee::find($employeeId);

                                if (! $employee) {
                                    self::clearPixFields($set);
                                    return;
                                }

                                self::fillPaymentDataFromEmployee($set, $employee);
                            })
                            ->columnSpan([
                                'default' => 1,
                                'md' => 3,
                                'xl' => 3,
                            ]),

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
                            ->required(fn (Get $get) => $get('payment_method') === 'pix')
                            ->visible(fn (Get $get) => $get('payment_method') === 'pix')
                            ->live()
                            ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                $display = $get('pix_key_display');

                                if (blank($display)) {
                                    return;
                                }

                                $formatted = self::formatPixKey($display, $state);

                                $set('pix_key_display', $formatted);
                                $set('pix_key', self::normalizePixKey($formatted, $state));

                                $holderDocument = $get('pix_holder_document');

                                if (filled($holderDocument)) {
                                    $set('pix_holder_document', self::formatDocument($holderDocument, $state));
                                }
                            })
                            ->columnSpan([
                                'default' => 1,
                                'md' => 6,
                                'xl' => 6,
                            ]),

                        Hidden::make('pix_key'),

                        TextInput::make('pix_key_display')
                            ->label('Chave PIX')
                            ->required(fn (Get $get) => $get('payment_method') === 'pix')
                            ->visible(fn (Get $get) => $get('payment_method') === 'pix')
                            ->placeholder(fn (Get $get) => match ($get('pix_key_type')) {
                                'cpf' => '000.000.000-00',
                                'cnpj' => '00.000.000/0000-00',
                                'email' => 'exemplo@dominio.com',
                                'phone' => '(00) 00000-0000',
                                'random' => 'Chave aleatória',
                                default => 'Informe a chave',
                            })
                            ->live()
                            ->dehydrated(false)
                            ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                $formatted = self::formatPixKey($state, $get('pix_key_type'));

                                if ($formatted !== $state) {
                                    $set('pix_key_display', $formatted);
                                }

                                $set('pix_key', self::normalizePixKey($formatted, $get('pix_key_type')));
                            })
                            ->columnSpan([
                                'default' => 1,
                                'md' => 6,
                                'xl' => 6,
                            ]),

                        TextInput::make('pix_holder_name')
                            ->label('Favorecido')
                            ->maxLength(255)
                            ->visible(fn (Get $get) => $get('payment_method') === 'pix')
                            ->columnSpan([
                                'default' => 1,
                                'md' => 6,
                                'xl' => 6,
                            ]),

                        TextInput::make('pix_holder_document')
                            ->label('CPF/CNPJ Favorecido')
                            ->maxLength(30)
                            ->visible(fn (Get $get) => $get('payment_method') === 'pix')
                            ->placeholder(fn (Get $get) => $get('pix_key_type') === 'cnpj'
                                ? '00.000.000/0000-00'
                                : '000.000.000-00')
                            ->mask(fn (Get $get) => match ($get('pix_key_type')) {
                                'cnpj' => RawJs::make("'99.999.999/9999-99'"),
                                default => RawJs::make("'999.999.999-99'"),
                            })
                            ->dehydrateStateUsing(fn ($state) => self::digits($state))
                            ->columnSpan([
                                'default' => 1,
                                'md' => 6,
                                'xl' => 6,
                            ]),

                        Textarea::make('notes')
                            ->label('Observações')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    protected static function resetPaymentData(Set $set): void
    {
        $set('payment_method', 'pix');
        self::clearPixFields($set);
    }

    protected static function fillPaymentDataFromEmployee(Set $set, Employee $employee): void
    {
        $pixKeyType = self::resolvePixKeyType($employee);
        $pixKey = self::resolvePixKey($employee, $pixKeyType);

        if (blank($pixKeyType) || blank($pixKey)) {
            $set('payment_method', $employee->payment_method ?: 'pix');
            self::clearPixFields($set);
            return;
        }

        $formattedPixKey = self::formatPixKey($pixKey, $pixKeyType);
        $holderDocument = $employee->pix_holder_document ?: $employee->cpf;

        $set('payment_method', 'pix');
        $set('pix_holder_name', $employee->pix_holder_name ?: $employee->name);
        $set('pix_holder_document', self::formatDocument($holderDocument, $pixKeyType));
        $set('pix_key', self::normalizePixKey($formattedPixKey, $pixKeyType));
        $set('pix_key_display', $formattedPixKey);
        $set('pix_key_type', $pixKeyType);
    }

    protected static function resolvePixKeyType(Employee $employee): ?string
    {
        if (filled($employee->pix_key_type)) {
            return $employee->pix_key_type;
        }

        if (filled($employee->cpf)) {
            return 'cpf';
        }

        if (filled($employee->email)) {
            return 'email';
        }

        if (filled($employee->mobile ?: $employee->phone)) {
            return 'phone';
        }

        if (filled($employee->pix_key)) {
            return 'random';
        }

        return null;
    }

    protected static function resolvePixKey(Employee $employee, ?string $pixKeyType): ?string
    {
        if (filled($employee->pix_key)) {
            return $employee->pix_key;
        }

        return match ($pixKeyType) {
            'cpf' => $employee->cpf,
            'cnpj' => $employee->pix_holder_document,
            'email' => $employee->email,
            'phone' => $employee->mobile ?: $employee->phone,
            default => null,
        };
    }

    protected static function clearPixFields(Set $set): void
    {
        $set('pix_key_type', null);
        $set('pix_key', null);
        $set('pix_key_display', null);
        $set('pix_holder_name', null);
        $set('pix_holder_document', null);
    }

    protected static function normalizePixKey(?string $value, ?string $type): ?string
    {
        if (blank($value)) {
            return null;
        }

        return match ($type) {
            'cpf', 'cnpj', 'phone' => self::digits($value),
            default => trim((string) $value),
        };
    }

    protected static function digits(?string $value): ?string
    {
        if (blank($value)) {
            return null;
        }

        return preg_replace('/\D+/', '', $value);
    }

    protected static function formatPixKey(?string $value, ?string $type): ?string
    {
        if (blank($value)) {
            return null;
        }

        $value = trim((string) $value);
        $digits = self::digits($value);

        return match ($type) {
            'cpf' => strlen($digits) === 11 ? preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $digits) : $value,
            'cnpj' => strlen($digits) === 14 ? preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $digits) : $value,
            'phone' => strlen($digits) === 11
                ? preg_replace('/(\d{2})(\d{5})(\d{4})/', '($1) $2-$3', $digits)
                : (strlen($digits) === 10 ? preg_replace('/(\d{2})(\d{4})(\d{4})/', '($1) $2-$3', $digits) : $value),
            default => $value,
        };
    }

    protected static function formatDocument(?string $value, ?string $type): ?string
    {
        if (blank($value)) {
            return null;
        }

        $digits = self::digits($value);

        if ($type === 'cnpj' && strlen($digits) === 14) {
            return preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $digits);
        }

        if (strlen($digits) === 11) {
            return preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $digits);
        }

        return $value;
    }

    protected static function formatMoneyForInput(mixed $state): string
    {
        if ($state === null || $state === '') {
            return '0,00';
        }

        return number_format((float) $state, 2, ',', '.');
    }

    protected static function normalizeMoneyToDatabase(mixed $state): float
    {
        if ($state === null || $state === '') {
            return 0;
        }

        $value = trim((string) $state);

        if (preg_match('/^\d+(\.\d{1,2})?$/', $value)) {
            return (float) $value;
        }

        $value = preg_replace('/[^\d,.-]/', '', $value);
        $value = str_replace('.', '', $value);
        $value = str_replace(',', '.', $value);

        return is_numeric($value) ? (float) $value : 0;
    }
}
