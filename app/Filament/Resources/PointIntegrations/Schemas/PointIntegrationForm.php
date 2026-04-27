<?php

namespace App\Filament\Resources\PointIntegrations\Schemas;

use App\Models\Company;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PointIntegrationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Dados da Integração')
                    ->description('Configuração principal da integração com sistema de ponto externo.')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('company_id')
                                    ->label('Empresa')
                                    ->options(fn () => Company::query()
                                        ->orderBy('name')
                                        ->pluck('name', 'id'))
                                    ->searchable()
                                    ->preload()
                                    ->nullable(),

                                Select::make('provider')
                                    ->label('Sistema de Ponto')
                                    ->options([
                                        'solides' => 'Sólides Ponto',
                                    ])
                                    ->default('solides')
                                    ->required()
                                    ->native(false),

                                TextInput::make('name')
                                    ->label('Nome da Integração')
                                    ->default('Sólides Ponto')
                                    ->required()
                                    ->maxLength(255),

                                Toggle::make('active')
                                    ->label('Integração Ativa')
                                    ->default(true)
                                    ->inline(false),
                            ]),
                    ])
                    ->columns(1),

                Section::make('Credenciais da API')
                    ->description('Informe os dados fornecidos pela Sólides para comunicação via API.')
                    ->schema([
                        TextInput::make('base_url')
                            ->label('URL Base da API')
                            ->placeholder('https://api.exemplo.com.br')
                            ->maxLength(255)
                            ->helperText('Informe a URL base da API da Sólides, quando fornecida pela plataforma.'),

                        TextInput::make('token')
                            ->label('Token de Integração')
                            ->password()
                            ->revealable()
                            ->dehydrateStateUsing(fn ($state) => filled($state) ? $state : null)
                            ->dehydrated(fn ($state) => filled($state))
                            ->helperText('Token gerado no painel da Sólides em Empregador > Integrações.'),

                        DateTimePicker::make('last_sync_at')
                            ->label('Última Sincronização')
                            ->disabled()
                            ->dehydrated(false),
                    ])
                    ->columns(1),

                Section::make('Configurações Avançadas')
                    ->collapsed()
                    ->schema([
                        KeyValue::make('settings')
                            ->label('Parâmetros Extras')
                            ->keyLabel('Chave')
                            ->valueLabel('Valor')
                            ->helperText('Use apenas se a integração exigir parâmetros específicos.'),

                        Textarea::make('notes')
                            ->label('Observações')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),

                Section::make('Orientação')
                    ->collapsed()
                    ->schema([
                        Placeholder::make('integration_info')
                            ->label('Fluxo recomendado')
                            ->content('Após salvar a integração, o próximo passo será vincular os colaboradores do ERP com o código externo da Sólides e testar a busca das marcações de ponto.'),
                    ]),
            ]);
    }
}