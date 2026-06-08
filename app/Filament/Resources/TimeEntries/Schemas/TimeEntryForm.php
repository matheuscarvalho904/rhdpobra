<?php

namespace App\Filament\Resources\TimeEntries\Schemas;

use App\Models\Company;
use App\Models\Employee;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class TimeEntryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Dados da Marcação')
                ->description('Inclua ou ajuste marcações de ponto manualmente quando a importação da API vier incompleta.')
                ->columns(2)
                ->schema([
                    Select::make('company_id')
                        ->label('Empresa')
                        ->options(fn () => Company::query()
                            ->where('is_active', true)
                            ->orderBy('name')
                            ->pluck('name', 'id')
                            ->toArray())
                        ->default(fn () => Auth::user()?->company_id)
                        ->searchable()
                        ->preload()
                        ->native(false)
                        ->required()
                        ->live()
                        ->afterStateUpdated(function (Set $set): void {
                            $set('employee_id', null);
                        }),

                    Select::make('employee_id')
                        ->label('Colaborador')
                        ->options(fn (Get $get) => Employee::query()
                            ->when($get('company_id'), fn ($query, $companyId) => $query->where('company_id', $companyId))
                            ->orderBy('name')
                            ->pluck('name', 'id')
                            ->toArray())
                        ->searchable()
                        ->preload()
                        ->native(false)
                        ->required(),

                    DatePicker::make('entry_date')
                        ->label('Data')
                        ->native(false)
                        ->displayFormat('d/m/Y')
                        ->required()
                        ->live()
                        ->afterStateUpdated(function (Get $get, Set $set, $state): void {
                            $dateTime = $get('entry_datetime');

                            if (! $state || ! $dateTime) {
                                return;
                            }

                            $time = \Carbon\Carbon::parse($dateTime)->format('H:i:s');
                            $set('entry_datetime', $state . ' ' . $time);
                        }),

                    DateTimePicker::make('entry_datetime')
                        ->label('Data/Hora da Marcação')
                        ->native(false)
                        ->displayFormat('d/m/Y H:i')
                        ->seconds(false)
                        ->required()
                        ->live()
                        ->afterStateUpdated(function (Set $set, $state): void {
                            if (! $state) {
                                return;
                            }

                            $set('entry_date', \Carbon\Carbon::parse($state)->toDateString());
                        }),

                    Select::make('type')
                        ->label('Tipo')
                        ->options([
                            'entrada' => 'Entrada',
                            'saida' => 'Saída',
                        ])
                        ->native(false)
                        ->required(),

                    Select::make('status')
                        ->label('Status')
                        ->options([
                            'valid' => 'Válido',
                            'invalid' => 'Inválido',
                        ])
                        ->default('valid')
                        ->native(false)
                        ->required(),

                    Select::make('provider')
                        ->label('Provedor')
                        ->options([
                            'manual' => 'Manual',
                            'solides' => 'Sólides',
                        ])
                        ->default('manual')
                        ->native(false)
                        ->required(),

                    Select::make('source')
                        ->label('Fonte')
                        ->options([
                            'manual' => 'Manual',
                            'api' => 'API',
                        ])
                        ->default('manual')
                        ->native(false)
                        ->required(),

                    Textarea::make('notes')
                        ->label('Motivo / Observação')
                        ->placeholder('Exemplo: Marcações lançadas manualmente conforme espelho de ponto da Sólides.')
                        ->rows(4)
                        ->columnSpanFull(),
                ]),
        ]);
    }
}
