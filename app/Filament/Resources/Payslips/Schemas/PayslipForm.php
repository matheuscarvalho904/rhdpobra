<?php

namespace App\Filament\Resources\Payslips\Schemas;

use App\Models\Branch;
use App\Models\Company;
use App\Models\Employee;
use App\Models\PayrollRun;
use App\Models\Work;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\RawJs;

class PayslipForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Dados do Holerite')
                    ->columns(4)
                    ->schema([
                        Select::make('payroll_run_id')
                            ->label('Processamento da Folha')
                            ->options(
                                PayrollRun::query()
                                    ->orderByDesc('id')
                                    ->get()
                                    ->mapWithKeys(fn ($item) => [
                                        $item->id => sprintf(
                                            '#%d - %s',
                                            $item->id,
                                            $item->description
                                        ),
                                    ])
                            )
                            ->searchable()
                            ->preload()
                            ->required()
                            ->columnSpan(2),

                        Select::make('employee_id')
                            ->label('Colaborador')
                            ->options(
                                Employee::query()
                                    ->orderBy('name')
                                    ->pluck('name', 'id')
                            )
                            ->searchable()
                            ->preload()
                            ->required()
                            ->columnSpan(2),

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
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set) {
                                $set('branch_id', null);
                                $set('work_id', null);
                            }),

                        Select::make('branch_id')
                            ->label('Filial')
                            ->options(fn ($get) => Branch::query()
                                ->when($get('company_id'), fn ($query, $companyId) => $query->where('company_id', $companyId))
                                ->where('is_active', true)
                                ->orderBy('name')
                                ->pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set) {
                                $set('work_id', null);
                            }),

                        Select::make('work_id')
                            ->label('Obra')
                            ->options(fn ($get) => Work::query()
                                ->when($get('company_id'), fn ($query, $companyId) => $query->where('company_id', $companyId))
                                ->when($get('branch_id'), fn ($query, $branchId) => $query->where('branch_id', $branchId))
                                ->where('is_active', true)
                                ->orderBy('name')
                                ->pluck('name', 'id'))
                            ->searchable()
                            ->preload(),

                        TextInput::make('file_path')
                            ->label('Arquivo')
                            ->maxLength(255),

                        self::moneyField('total_gross', 'Total Bruto'),
                        self::moneyField('deduction_total', 'Total de Descontos'),
                        self::moneyField('net_total', 'Total Líquido'),

                        self::moneyField('base_inss', 'Base INSS'),
                        self::moneyField('base_fgts', 'Base FGTS'),
                        self::moneyField('base_irrf', 'Base IRRF'),

                        Placeholder::make('payslip_hint')
                            ->label('Observação')
                            ->content('O holerite idealmente é gerado automaticamente pelo processamento da folha.')
                            ->columnSpanFull(),
                    ]),

                Section::make('Controle')
                    ->columns(2)
                    ->schema([
                        DateTimePicker::make('printed_at')
                            ->label('Impresso em')
                            ->seconds(false),

                        DateTimePicker::make('sent_at')
                            ->label('Enviado em')
                            ->seconds(false),
                    ]),
            ]);
    }

    protected static function moneyField(string $name, string $label): TextInput
    {
        return TextInput::make($name)
            ->label($label)
            ->required()
            ->prefix('R$')
            ->mask(RawJs::make(<<<'JS'
                $money($input, ',', '.', 2)
            JS))
            ->stripCharacters(['R$', ' '])
            ->formatStateUsing(fn ($state) => self::formatMoneyForInput($state))
            ->dehydrateStateUsing(fn ($state) => self::normalizeMoneyToDatabase($state))
            ->default('0,00');
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

        $value = preg_replace('/[^\d,.-]/', '', (string) $state);
        $value = str_replace('.', '', $value);
        $value = str_replace(',', '.', $value);

        return (float) $value;
    }
}