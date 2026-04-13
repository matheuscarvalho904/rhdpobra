<?php

namespace App\Filament\Resources\HourBanks\Schemas;

use App\Models\Employee;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class HourBankForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Dados do Banco de Horas')
                    
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
                            ->unique(ignoreRecord: true),

                        TextInput::make('balance_minutes')
                            ->label('Saldo em Minutos')
                            ->numeric()
                            ->default(0)
                            ->required(),

                        Toggle::make('is_active')
                            ->label('Ativo')
                            ->default(true),

                        Placeholder::make('balance_hint')
                            ->label('Referência')
                            ->content('Ex.: 480 = 08h00, 60 = 01h00, -120 = -02h00.')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}