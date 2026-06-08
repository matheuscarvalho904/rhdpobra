<?php

namespace App\Filament\Resources\EmployeeVariableEvents\Schemas;

use App\Models\Employee;
use App\Models\PayrollCompetency;
use App\Models\PayrollEvent;
use App\Models\User;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\RawJs;

class EmployeeVariableEventForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Dados do Evento Variável')
                
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
                            ->required()
                            ->columnSpan(2),
                            Select::make('payroll_competency_id')
                                ->label('Competência')
                                ->options(
                                    PayrollCompetency::query()
                                        ->with('company')
                                        ->orderByDesc('year')
                                        ->orderByDesc('month')
                                        ->get()
                                        ->mapWithKeys(fn ($item) => [
                                            $item->id => $item->description
                                                ?: sprintf('%02d/%04d - %s', $item->month, $item->year, $item->company?->name ?? 'Empresa'),
                                        ])
                                        ->toArray()
                                )
                                ->searchable()
                                ->preload()
                                ->required()
                                ->columnSpan(2),

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
                            ->required()
                            ->columnSpan(2),

                        TextInput::make('amount')
                            ->label('Valor')
                            ->prefix('R$')
                            ->mask(RawJs::make(<<<'JS'
                                $money($input, ',', '.', 2)
                            JS))
                            ->formatStateUsing(fn ($state) => number_format((float) $state, 2, ',', '.'))
                            ->dehydrateStateUsing(function ($state) {
                                if (blank($state)) {
                                    return 0;
                                }

                                $value = str_replace(['R$', ' ', '.'], '', (string) $state);
                                $value = str_replace(',', '.', $value);

                                return (float) $value;
                            })
                            ->default('0,00')
                            ->columnSpan(2),

                        TextInput::make('percentage')
                            ->label('Percentual')
                            ->suffix('%')
                            ->numeric()
                            ->step('0.0001'),

                        TextInput::make('quantity')
                            ->label('Quantidade')
                            ->numeric()
                            ->step('0.0001'),

                        TextInput::make('reference')
                            ->label('Referência')
                            ->maxLength(255)
                            ->columnSpan(2),

                        Select::make('created_by')
                            ->label('Criado por')
                            ->options(
                                User::query()
                                    ->orderBy('name')
                                    ->pluck('name', 'id')
                            )
                            ->searchable()
                            ->preload()
                            ->columnSpan(2),

                        Placeholder::make('rule_hint')
                            ->label('Regra')
                            ->content('Use valor, percentual ou quantidade conforme a regra do evento. O cálculo automático da folha utilizará essas referências.')
                            ->columnSpanFull()
                            ->columnSpan(2),
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