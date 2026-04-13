<?php

namespace App\Filament\Resources\EmployeeSalaryHistories\Schemas;

use App\Models\Employee;
use App\Models\User;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\RawJs;

class EmployeeSalaryHistoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Histórico Salarial')
                    ->schema([
                        Select::make('employee_id')
                            ->label('Colaborador')
                            ->options(
                                Employee::query()
                                    ->orderBy('name')
                                    ->pluck('name', 'id')
                            )
                            ->searchable()
                            ->preload()
                            ->required(),

                        Select::make('salary_type')
                            ->label('Tipo Salarial')
                            ->options([
                                'monthly' => 'Mensalista',
                                'hourly' => 'Horista',
                                'daily' => 'Diarista',
                            ])
                            ->required(),

                        TextInput::make('previous_salary')
                            ->label('Salário Anterior')
                            ->prefix('R$')
                            ->mask(RawJs::make(<<<'JS'
                                $money($input, ',', '.', 2)
                            JS))
                            ->stripCharacters(['R$', ' '])
                            ->formatStateUsing(fn ($state) => self::formatMoneyForInput($state))
                            ->dehydrateStateUsing(fn ($state) => self::normalizeMoneyToDatabase($state))
                            ->default('0,00'),

                        TextInput::make('new_salary')
                            ->label('Novo Salário')
                            ->required()
                            ->prefix('R$')
                            ->mask(RawJs::make(<<<'JS'
                                $money($input, ',', '.', 2)
                            JS))
                            ->stripCharacters(['R$', ' '])
                            ->formatStateUsing(fn ($state) => self::formatMoneyForInput($state))
                            ->dehydrateStateUsing(fn ($state) => self::normalizeMoneyToDatabase($state))
                            ->default('0,00'),

                        DatePicker::make('effective_date')
                            ->label('Data de Vigência')
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->required(),

                        TextInput::make('reason')
                            ->label('Motivo')
                            ->maxLength(255),

                        Select::make('created_by')
                            ->label('Criado por')
                            ->options(
                                User::query()
                                    ->orderBy('name')
                                    ->pluck('name', 'id')
                            )
                            ->searchable()
                            ->preload(),
                    ]),
            ]);
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