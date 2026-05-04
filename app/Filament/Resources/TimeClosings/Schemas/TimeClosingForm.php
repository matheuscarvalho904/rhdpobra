<?php

namespace App\Filament\Resources\TimeClosings\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TimeClosingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Dados do Fechamento')
                ->schema([
                    TextInput::make('name')
                        ->label('Nome do Fechamento')
                        ->placeholder('Ex: Fechamento Abril/2026')
                        ->required()
                        ->maxLength(255),

                    Select::make('company_id')
                        ->label('Empresa')
                        ->relationship('company', 'name')
                        ->searchable()
                        ->preload()
                        ->required(),

                    Select::make('payroll_competency_id')
                        ->label('Competência da Folha')
                        ->relationship('payrollCompetency', 'id')
                        ->getOptionLabelFromRecordUsing(function ($record): string {
                            $month = str_pad((string) $record->month, 2, '0', STR_PAD_LEFT);
                            $year = $record->year;

                            $type = match ($record->type ?? null) {
                                'monthly' => 'Mensal',
                                'vacation' => 'Férias',
                                '13th' => '13º',
                                'termination' => 'Rescisão',
                                'advance' => 'Adiantamento',
                                default => ucfirst((string) ($record->type ?? 'Folha')),
                            };

                            return "{$month}/{$year} - {$type}";
                        })
                        ->searchable()
                        ->preload()
                        ->required(),

                    DatePicker::make('start_date')
                        ->label('Data Inicial')
                        ->required(),

                    DatePicker::make('end_date')
                        ->label('Data Final')
                        ->required(),
                ])
                ->columns(2),
        ]);
    }
}