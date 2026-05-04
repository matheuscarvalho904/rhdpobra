<?php

namespace App\Filament\Resources\WorkSchedules\Schemas;

use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class WorkScheduleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Identificação da Jornada')
                ->schema([
                    Select::make('company_id')
                        ->label('Empresa')
                        ->relationship('company', 'name')
                        ->searchable()
                        ->preload()
                        ->nullable(),

                    TextInput::make('name')
                        ->label('Nome da Jornada')
                        ->placeholder('Ex: Jornada Sólides 44h')
                        ->required()
                        ->maxLength(255),

                    TextInput::make('code')
                        ->label('Código')
                        ->placeholder('Ex: SOLIDES-44H')
                        ->maxLength(50),

                    Select::make('schedule_type')
                        ->label('Tipo de Jornada')
                        ->options([
                            'fixed' => 'Fixa',
                            'flexible' => 'Flexível',
                            'shift' => 'Turno',
                            '12x36' => '12x36',
                            'custom' => 'Personalizada',
                        ])
                        ->default('fixed')
                        ->required(),

                    Toggle::make('is_active')
                        ->label('Ativa')
                        ->default(true),
                ])
                ->columns(2),

            Section::make('Regras Gerais')
                ->schema([
                    Toggle::make('works_on_holidays')
                        ->label('Trabalha em feriados')
                        ->default(false),

                    Toggle::make('uses_time_bank')
                        ->label('Usa banco de horas')
                        ->default(false),

                    TextInput::make('daily_tolerance_minutes')
                        ->label('Tolerância diária (min)')
                        ->numeric()
                        ->default(10)
                        ->required(),

                    TextInput::make('monthly_tolerance_minutes')
                        ->label('Tolerância mensal (min)')
                        ->numeric()
                        ->default(0)
                        ->required(),

                    TextInput::make('weekly_hours')
                        ->label('Horas semanais')
                        ->numeric()
                        ->default(44)
                        ->required(),

                    TextInput::make('monthly_hours')
                        ->label('Horas mensais')
                        ->numeric()
                        ->default(220)
                        ->required(),
                ])
                ->columns(3),

            Section::make('Configuração avançada')
                ->description('Regras padrão Sólides/Senior para fechamento, folha e horas extras.')
                ->schema([
                    TextInput::make('settings.saturday_expected_hours')
                        ->label('Horas sábado')
                        ->numeric()
                        ->default(4)
                        ->helperText('Exemplo: 4 para jornada 44h.'),

                    Toggle::make('settings.holiday_keeps_expected_hours')
                        ->label('Feriado mantém jornada')
                        ->default(true),

                    TextInput::make('settings.sunday_expected_hours')
                        ->label('Horas domingo')
                        ->numeric()
                        ->default(0),

                    Select::make('settings.overtime_mode')
                        ->label('Modo de horas extras')
                        ->options([
                            'period_balance' => 'Saldo do período (Sólides)',
                            'daily_split' => 'Separação por dia',
                        ])
                        ->default('period_balance'),

                    Toggle::make('settings.rpa_overtime_all_100')
                        ->label('RPA tudo 100%')
                        ->default(true),
                ])
                ->columns(2),

            Section::make('Observações')
                ->schema([
                    Textarea::make('notes')
                        ->label('Observações')
                        ->rows(3)
                        ->columnSpanFull(),

                    KeyValue::make('settings')
                        ->label('Configurações extras')
                        ->keyLabel('Chave')
                        ->valueLabel('Valor')
                        ->columnSpanFull(),
                ])
                ->collapsed(),
        ]);
    }
}