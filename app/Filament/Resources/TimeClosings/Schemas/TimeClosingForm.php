<?php

namespace App\Filament\Resources\TimeClosings\Schemas;

use App\Models\PayrollCompetency;
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