<?php

namespace App\Filament\Resources\Companies\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\RawJs;

class CompanyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Dados da Empresa')
                    ->schema([
                        TextInput::make('code')
                            ->label('Código')
                            ->required()
                            ->maxLength(20)
                            ->unique(ignoreRecord: true)
                            ->columnSpan(2),

                        TextInput::make('name')
                            ->label('Razão Social')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(2),

                        TextInput::make('trade_name')
                            ->label('Nome Fantasia')
                            ->maxLength(255)
                            ->columnSpan(2),

                        TextInput::make('document')
                            ->label('CNPJ')
                            ->mask('99.999.999/9999-99')
                            ->maxLength(18)
                            ->unique(ignoreRecord: true)
                            ->required()
                            ->dehydrateStateUsing(fn ($state) => preg_replace('/\D+/', '', (string) $state))
                            ->columnSpan(2),

                        Toggle::make('is_active')
                            ->label('Ativo')
                            ->default(true)
                            ->columnSpan(1),
                    ]),

                Section::make('Dados do Representante')
                    ->schema([
                        TextInput::make('legal_representative_name')
                            ->label('Representante Legal')
                            ->maxLength(255)
                            ->columnSpan(2),

                        TextInput::make('legal_representative_cpf')
                            ->label('CPF do Representante')
                            ->maxLength(14)
                            ->mask(RawJs::make("'999.999.999-99'"))
                            ->dehydrateStateUsing(fn ($state) => preg_replace('/\D+/', '', (string) $state))
                            ->columnSpan(1),

                        TextInput::make('legal_representative_rg')
                            ->label('RG do Representante')
                            ->maxLength(30)
                            ->columnSpan(1),

                        Select::make('legal_representative_role')
                            ->label('Cargo do Representante')
                            ->options([
                                'socio_administrador' => 'Sócio Administrador',
                                'diretor' => 'Diretor',
                                'empresario' => 'Empresário',
                                'proprietario' => 'Proprietário',
                                'administrador' => 'Administrador',
                                'gerente' => 'Gerente',
                                'representante_legal' => 'Representante Legal',
                            ])
                            ->searchable()
                            ->native(false)
                            ->columnSpan(2),
                    ]),

                Section::make('Endereço e Contato')
                    ->schema([
                        TextInput::make('zip_code')
                            ->label('CEP')
                            ->mask('99999-999')
                            ->maxLength(9)
                            ->dehydrateStateUsing(fn ($state) => preg_replace('/\D+/', '', (string) $state))
                            ->columnSpan(2),

                        TextInput::make('address')
                            ->label('Endereço')
                            ->maxLength(255)
                            ->columnSpan(2),

                        TextInput::make('number')
                            ->label('Número')
                            ->maxLength(20)
                            ->columnSpan(1),

                        TextInput::make('complement')
                            ->label('Complemento')
                            ->maxLength(255)
                            ->columnSpan(1),

                        TextInput::make('district')
                            ->label('Bairro')
                            ->maxLength(255)
                            ->columnSpan(2),

                        TextInput::make('city')
                            ->label('Cidade')
                            ->maxLength(255)
                            ->columnSpan(2),

                        TextInput::make('state')
                            ->label('UF')
                            ->maxLength(2)
                            ->columnSpan(2),

                        TextInput::make('phone')
                            ->label('Telefone')
                            ->mask('(99) 99999-9999')
                            ->maxLength(20)
                            ->dehydrateStateUsing(fn ($state) => preg_replace('/\D+/', '', (string) $state))
                            ->columnSpan(2),

                        TextInput::make('email')
                            ->label('E-mail')
                            ->email()
                            ->maxLength(255)
                            ->columnSpan(2),
                    ]),
            ]);
    }
}