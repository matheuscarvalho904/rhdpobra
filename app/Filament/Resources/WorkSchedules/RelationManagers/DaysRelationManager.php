<?php

namespace App\Filament\Resources\WorkSchedules\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DaysRelationManager extends RelationManager
{
    protected static string $relationship = 'days';

    protected static ?string $title = 'Dias da Jornada';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Dia da Semana')
                ->schema([
                    Select::make('weekday')
                        ->label('Dia')
                        ->options([
                            0 => 'Domingo',
                            1 => 'Segunda-feira',
                            2 => 'Terça-feira',
                            3 => 'Quarta-feira',
                            4 => 'Quinta-feira',
                            5 => 'Sexta-feira',
                            6 => 'Sábado',
                        ])
                        ->required(),

                    Toggle::make('is_working_day')
                        ->label('Dia trabalhado')
                        ->default(true),

                    TextInput::make('expected_hours')
                        ->label('Horas previstas')
                        ->numeric()
                        ->default(0)
                        ->required(),
                ])
                ->columns(3),

            Section::make('Horários')
                ->schema([
                    TimePicker::make('first_start')
                        ->label('1º Entrada')
                        ->seconds(false),

                    TimePicker::make('first_end')
                        ->label('1º Saída')
                        ->seconds(false),

                    TimePicker::make('second_start')
                        ->label('2º Entrada')
                        ->seconds(false),

                    TimePicker::make('second_end')
                        ->label('2º Saída')
                        ->seconds(false),
                ])
                ->columns(4),

            Section::make('Regras do Dia')
                ->schema([
                    TextInput::make('overtime_50_after_hours')
                        ->label('HE 50% após horas')
                        ->numeric()
                        ->placeholder('Ex: 8'),

                    TextInput::make('overtime_100_after_hours')
                        ->label('HE 100% após horas')
                        ->numeric()
                        ->placeholder('Opcional'),

                    Toggle::make('holiday_keeps_schedule')
                        ->label('Feriado mantém jornada')
                        ->default(false),

                    Toggle::make('holiday_generates_overtime_100')
                        ->label('Feriado gera HE 100%')
                        ->default(true),

                    TextInput::make('entry_tolerance_minutes')
                        ->label('Tolerância entrada')
                        ->numeric()
                        ->default(5),

                    TextInput::make('exit_tolerance_minutes')
                        ->label('Tolerância saída')
                        ->numeric()
                        ->default(5),
                ])
                ->columns(3),

            Section::make('Avançado')
                ->collapsed()
                ->schema([
                    KeyValue::make('settings')
                        ->label('Configurações')
                        ->keyLabel('Chave')
                        ->valueLabel('Valor')
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('weekday')
                    ->label('Dia')
                    ->formatStateUsing(fn ($state): string => match ((int) $state) {
                        0 => 'Domingo',
                        1 => 'Segunda',
                        2 => 'Terça',
                        3 => 'Quarta',
                        4 => 'Quinta',
                        5 => 'Sexta',
                        6 => 'Sábado',
                        default => '-',
                    })
                    ->sortable(),

                IconColumn::make('is_working_day')
                    ->label('Trabalha')
                    ->boolean(),

                TextColumn::make('first_start')
                    ->label('Entrada 1')
                    ->placeholder('-'),

                TextColumn::make('first_end')
                    ->label('Saída 1')
                    ->placeholder('-'),

                TextColumn::make('second_start')
                    ->label('Entrada 2')
                    ->placeholder('-'),

                TextColumn::make('second_end')
                    ->label('Saída 2')
                    ->placeholder('-'),

                TextColumn::make('expected_hours')
                    ->label('Previstas')
                    ->suffix('h'),

                TextColumn::make('overtime_50_after_hours')
                    ->label('HE 50 após')
                    ->suffix('h')
                    ->placeholder('-'),

                IconColumn::make('holiday_keeps_schedule')
                    ->label('Feriado mantém')
                    ->boolean(),
            ])
            ->defaultSort('weekday')
            ->headerActions([
                CreateAction::make()
                    ->label('Novo Dia'),
            ])
            ->recordActions([
                EditAction::make()->label('Editar'),
                DeleteAction::make()->label('Excluir'),
            ]);
    }
}