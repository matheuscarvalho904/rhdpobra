<?php

namespace App\Filament\Resources\EmployeeFixedEvents\Schemas;

use App\Models\Employee;
use App\Models\PayrollEvent;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\RawJs;

class EmployeeFixedEventForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Dados do Evento Fixo')
                    
                    ->schema([
                        Select::make('employee_id')
                            ->label('Colaborador')
                            ->options(
                                Employee::query()
                                    ->where('is_active', true)
                                    ->orderBy('name')
                                    ->pluck('name', 'id')
                            )
                            ->searchable()
                            ->preload()
                            ->required(),

                        Select::make('payroll_event_id')
                            ->label('Evento da Folha')
                            ->options(
                                PayrollEvent::query()
                                    ->where('is_active', true)
                                    ->orderBy('name')
                                    ->pluck('name', 'id')
                            )
                            ->searchable()
                            ->preload()
                            ->required(),

                        Toggle::make('is_active')
                            ->label('Ativo')
                            ->default(true),

                        TextInput::make('amount')
                            ->label('Valor')
                            ->prefix('R$')
                            ->mask(RawJs::make(<<<'JS'
                                $money($input, ',', '.', 2)
                            JS))
                            ->stripCharacters(['R$', '.', ' '])
                            ->dehydrateStateUsing(fn ($state) => blank($state) ? null : str_replace(',', '.', $state))
                            ->default('0,00'),

                        TextInput::make('percentage')
                            ->label('Percentual')
                            ->suffix('%')
                            ->numeric()
                            ->step('0.0001'),

                        TextInput::make('quantity')
                            ->label('Quantidade')
                            ->numeric()
                            ->step('0.0001'),

                        DatePicker::make('start_date')
                            ->label('Data Inicial')
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->required(),

                        DatePicker::make('end_date')
                            ->label('Data Final')
                            ->native(false)
                            ->displayFormat('d/m/Y'),

                        Placeholder::make('fixed_event_hint')
                            ->label('Regra')
                            ->content('Use valor, percentual ou quantidade conforme a regra do evento. Eventos fixos ativos entram automaticamente no cálculo da folha.')
                            ->columnSpanFull(),
                    ]),

                Section::make('Observações')
                    ->schema([
                        Textarea::make('notes')
                            ->label('Observações')
                            ->rows(4),
                    ]),
            ]);
    }
}