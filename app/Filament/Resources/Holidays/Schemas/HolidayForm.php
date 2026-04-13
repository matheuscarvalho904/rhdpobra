<?php

namespace App\Filament\Resources\Holidays\Schemas;

use App\Models\HolidayType;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class HolidayForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Dados do Feriado')
                
                    ->schema([
                        Select::make('holiday_type_id')
                            ->label('Tipo')
                            ->options(
                                HolidayType::query()
                                    ->where('is_active', true)
                                    ->orderBy('name')
                                    ->pluck('name', 'id')
                            )
                            ->searchable()
                            ->preload()
                            ->required(),

                        TextInput::make('name')
                            ->label('Nome')
                            ->required()
                            ->maxLength(255),

                        DatePicker::make('holiday_date')
                            ->label('Data')
                            ->required()
                            ->native(false)
                            ->displayFormat('d/m/Y'),

                        TextInput::make('state')
                            ->label('UF')
                            ->maxLength(2),

                        TextInput::make('city')
                            ->label('Cidade')
                            ->maxLength(255),

                        Toggle::make('is_active')
                            ->label('Ativo')
                            ->default(true),
                    ]),
            ]);
    }
}