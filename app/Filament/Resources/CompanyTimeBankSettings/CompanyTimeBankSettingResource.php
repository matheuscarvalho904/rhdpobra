<?php

namespace App\Filament\Resources\CompanyTimeBankSettings;

use App\Filament\Resources\CompanyTimeBankSettings\Pages\CreateCompanyTimeBankSetting;
use App\Filament\Resources\CompanyTimeBankSettings\Pages\EditCompanyTimeBankSetting;
use App\Filament\Resources\CompanyTimeBankSettings\Pages\ListCompanyTimeBankSettings;
use App\Models\CompanyTimeBankSetting;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CompanyTimeBankSettingResource extends Resource
{
    protected static ?string $model = CompanyTimeBankSetting::class;

    protected static ?string $navigationLabel = 'Config. Empresa BH';

    protected static ?string $modelLabel = 'Configuração Empresa Banco de Horas';

    protected static ?string $pluralModelLabel = 'Configurações Empresa Banco de Horas';

    protected static string|\UnitEnum|null $navigationGroup = 'Ponto e Jornada';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-building-office';

    protected static ?int $navigationSort = 42;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([

                Section::make('Empresa')
                    ->schema([

                        Select::make('company_id')
                            ->label('Empresa')
                            ->relationship('company', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),

                    ]),

                Section::make('Configuração Geral')
                    ->schema([

                        Toggle::make('enabled')
                            ->label('Banco de horas ativo')
                            ->default(false),

                        Select::make('default_overtime_destination')
                            ->label('Destino padrão HE')
                            ->options([
                                'payroll' => 'Folha de pagamento',
                                'time_bank' => 'Banco de horas',
                                'mixed' => 'Híbrido',
                            ])
                            ->default('payroll')
                            ->required(),

                        TextInput::make('monthly_bank_limit')
                            ->label('Limite mensal banco')
                            ->numeric()
                            ->default(20)
                            ->suffix(' h'),

                        Toggle::make('excess_to_payroll')
                            ->label('Excedente vai para folha')
                            ->default(true),

                        Toggle::make('compensate_delays_with_balance')
                            ->label('Compensar atrasos automaticamente')
                            ->default(true),

                        Toggle::make('allow_negative_balance')
                            ->label('Permitir saldo negativo')
                            ->default(false),

                        TextInput::make('expiration_days')
                            ->label('Dias para expiração')
                            ->numeric()
                            ->default(180)
                            ->suffix(' dias'),

                    ])
                    ->columns(2),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('company.name')
                    ->label('Empresa')
                    ->searchable()
                    ->sortable(),

                IconColumn::make('enabled')
                    ->label('Ativo')
                    ->boolean(),

                TextColumn::make('default_overtime_destination')
                    ->label('Destino HE')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'payroll' => 'Folha',
                        'time_bank' => 'Banco',
                        'mixed' => 'Híbrido',
                        default => $state,
                    })
                    ->color(fn ($state) => match ($state) {
                        'payroll' => 'warning',
                        'time_bank' => 'success',
                        'mixed' => 'info',
                        default => 'gray',
                    }),

                TextColumn::make('monthly_bank_limit')
                    ->label('Limite')
                    ->numeric(decimalPlaces: 2)
                    ->suffix(' h'),

                IconColumn::make('excess_to_payroll')
                    ->label('Excedente Folha')
                    ->boolean(),

                IconColumn::make('compensate_delays_with_balance')
                    ->label('Compensa Atrasos')
                    ->boolean(),

                TextColumn::make('expiration_days')
                    ->label('Expiração')
                    ->suffix(' dias'),

                TextColumn::make('updated_at')
                    ->label('Atualizado em')
                    ->dateTime('d/m/Y H:i'),
            ])

            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCompanyTimeBankSettings::route('/'),
            'create' => CreateCompanyTimeBankSetting::route('/create'),
            'edit' => EditCompanyTimeBankSetting::route('/{record}/edit'),
        ];
    }
}