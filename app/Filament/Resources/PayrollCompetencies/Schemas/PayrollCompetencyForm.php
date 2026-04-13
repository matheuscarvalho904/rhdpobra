<?php

namespace App\Filament\Resources\PayrollCompetencies\Schemas;

use App\Models\Branch;
use App\Models\Company;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class PayrollCompetencyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Dados da Competência')
                ->columns(12)
                ->schema([
                    Select::make('company_id')
                        ->label('Empresa')
                        ->options(fn () => Company::query()
                            ->where('is_active', true)
                            ->orderBy('name')
                            ->pluck('name', 'id')
                            ->toArray())
                        ->searchable()
                        ->preload()
                        ->required()
                        ->default(fn () => Auth::user()?->company_id)
                        ->live()
                        ->columnSpan(4)
                        ->afterStateUpdated(function (Set $set): void {
                            $set('branch_id', null);
                        }),

                    Select::make('branch_id')
                        ->label('Filial')
                        ->options(fn (Get $get) => Branch::query()
                            ->when(
                                $get('company_id'),
                                fn ($query, $companyId) => $query->where('company_id', $companyId)
                            )
                            ->where('is_active', true)
                            ->orderBy('name')
                            ->pluck('name', 'id')
                            ->toArray())
                        ->searchable()
                        ->preload()
                        ->columnSpan(4),

                    Select::make('type')
                        ->label('Tipo')
                        ->options([
                            'monthly' => 'Folha Mensal',
                            'vacation' => 'Férias',
                            'thirteenth' => '13º Salário',
                            'termination' => 'Rescisão',
                            'advance' => 'Adiantamento',
                        ])
                        ->default('monthly')
                        ->required()
                        ->live()
                        ->columnSpan(4)
                        ->afterStateUpdated(function (Get $get, Set $set): void {
                            self::updateDescription($get, $set);
                        }),

                    Select::make('month')
                        ->label('Mês')
                        ->options([
                            1 => '01 - Janeiro',
                            2 => '02 - Fevereiro',
                            3 => '03 - Março',
                            4 => '04 - Abril',
                            5 => '05 - Maio',
                            6 => '06 - Junho',
                            7 => '07 - Julho',
                            8 => '08 - Agosto',
                            9 => '09 - Setembro',
                            10 => '10 - Outubro',
                            11 => '11 - Novembro',
                            12 => '12 - Dezembro',
                        ])
                        ->required()
                        ->live()
                        ->columnSpan(3)
                        ->afterStateUpdated(function (Get $get, Set $set): void {
                            self::updatePeriod($get, $set);
                            self::updateDescription($get, $set);
                        }),

                    Select::make('year')
                        ->label('Ano')
                        ->options(
                            collect(range(now()->year - 2, now()->year + 5))
                                ->mapWithKeys(fn ($year) => [$year => (string) $year])
                                ->toArray()
                        )
                        ->default(now()->year)
                        ->required()
                        ->live()
                        ->columnSpan(3)
                        ->afterStateUpdated(function (Get $get, Set $set): void {
                            self::updatePeriod($get, $set);
                            self::updateDescription($get, $set);
                        }),

                    Select::make('status')
                        ->label('Status')
                        ->options([
                            'open' => 'Aberto',
                            'processing' => 'Processando',
                            'calculated' => 'Calculado',
                            'reviewed' => 'Conferido',
                            'closed' => 'Fechado',
                            'canceled' => 'Cancelado',
                        ])
                        ->default('open')
                        ->required()
                        ->columnSpan(3),

                    DatePicker::make('payment_date')
                        ->label('Data de Pagamento')
                        ->native(false)
                        ->displayFormat('d/m/Y')
                        ->columnSpan(3),

                    TextInput::make('description')
                        ->label('Descrição')
                        ->required()
                        ->maxLength(255)
                        ->columnSpan(6),

                    DatePicker::make('period_start')
                        ->label('Período Inicial')
                        ->native(false)
                        ->displayFormat('d/m/Y')
                        ->required()
                        ->columnSpan(3),

                    DatePicker::make('period_end')
                        ->label('Período Final')
                        ->native(false)
                        ->displayFormat('d/m/Y')
                        ->required()
                        ->columnSpan(3),

                    Placeholder::make('competency_hint')
                        ->label('Regra')
                        ->content('A competência é única por empresa, filial, mês, ano e tipo.')
                        ->columnSpanFull(),
                ]),

            Section::make('Observações')
                ->columns(12)
                ->schema([
                    Textarea::make('notes')
                        ->label('Observações')
                        ->rows(4)
                        ->columnSpanFull(),
                ]),
        ]);
    }

    protected static function updatePeriod(Get $get, Set $set): void
    {
        $month = $get('month');
        $year = $get('year');

        if (! $month || ! $year) {
            return;
        }

        $start = Carbon::createFromDate((int) $year, (int) $month, 1)->startOfMonth();
        $end = $start->copy()->endOfMonth();

        $set('period_start', $start->format('Y-m-d'));
        $set('period_end', $end->format('Y-m-d'));
    }

    protected static function updateDescription(Get $get, Set $set): void
    {
        $month = (int) ($get('month') ?? 0);
        $year = (int) ($get('year') ?? 0);
        $type = $get('type');

        if (! $month || ! $year) {
            return;
        }

        $months = [
            1 => 'Janeiro',
            2 => 'Fevereiro',
            3 => 'Março',
            4 => 'Abril',
            5 => 'Maio',
            6 => 'Junho',
            7 => 'Julho',
            8 => 'Agosto',
            9 => 'Setembro',
            10 => 'Outubro',
            11 => 'Novembro',
            12 => 'Dezembro',
        ];

        $types = [
            'monthly' => 'Folha Mensal',
            'vacation' => 'Férias',
            'thirteenth' => '13º Salário',
            'termination' => 'Rescisão',
            'advance' => 'Adiantamento',
        ];

        $monthLabel = $months[$month] ?? (string) $month;
        $typeLabel = $types[$type] ?? 'Competência';

        $set('description', $typeLabel . ' - ' . $monthLabel . ' / ' . $year);
    }
}