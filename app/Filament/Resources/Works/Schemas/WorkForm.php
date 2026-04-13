<?php

namespace App\Filament\Resources\Works\Schemas;

use App\Models\Branch;
use App\Models\Company;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class WorkForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Dados da Obra')
                
                    ->schema([
                        Select::make('company_id')
                            ->label('Empresa')
                            ->options(Company::query()->where('is_active', true)->pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live(),

                        Select::make('branch_id')
                            ->label('Filial')
                            ->options(fn ($get) => Branch::query()
                                ->when($get('company_id'), fn ($query, $companyId) => $query->where('company_id', $companyId))
                                ->where('is_active', true)
                                ->pluck('name', 'id'))
                            ->searchable()
                            ->preload(),

                        TextInput::make('name')
                            ->label('Nome da Obra')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('code')
                            ->label('Código')
                            ->maxLength(30),

                        TextInput::make('client_name')
                            ->label('Cliente')
                            ->maxLength(255),

                        Toggle::make('is_active')
                            ->label('Ativo')
                            ->default(true)
                            ->inline(false),

                        TextInput::make('city')
                            ->label('Cidade')
                            ->maxLength(255),

                        TextInput::make('state')
                            ->label('UF')
                            ->maxLength(2),

                        DatePicker::make('start_date')
                            ->label('Data de Início')
                            ->native(false)
                            ->displayFormat('d/m/Y'),

                        DatePicker::make('expected_end_date')
                            ->label('Previsão de Término')
                            ->native(false)
                            ->displayFormat('d/m/Y'),
                    ]),
            ]);
    }
}