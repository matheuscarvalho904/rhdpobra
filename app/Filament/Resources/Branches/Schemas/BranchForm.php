<?php

namespace App\Filament\Resources\Branches\Schemas;

use App\Models\Company;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class BranchForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Dados da Filial')
                
                    ->schema([
                        Select::make('company_id')
                            ->label('Empresa')
                            ->options(Company::query()->where('is_active', true)->pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->required(),

                        TextInput::make('name')
                            ->label('Nome')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('code')
                            ->label('Código')
                            ->maxLength(30),

                        TextInput::make('document')
                            ->label('CNPJ')
                            ->mask('99.999.999/9999-99')
                            ->maxLength(20),

                        TextInput::make('phone')
                            ->label('Telefone')
                            ->tel()
                            ->mask('(99) 99999-9999')
                            ->maxLength(20),

                        TextInput::make('email')
                            ->label('E-mail')
                            ->email()
                            ->maxLength(255),

                        TextInput::make('city')
                            ->label('Cidade')
                            ->maxLength(255),

                        TextInput::make('state')
                            ->label('UF')
                            ->maxLength(2),

                        Toggle::make('is_active')
                            ->label('Ativo')
                            ->default(true)
                            ->inline(false),
                    ]),
            ]);
    }
}