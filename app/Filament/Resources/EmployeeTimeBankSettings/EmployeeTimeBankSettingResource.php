<?php

namespace App\Filament\Resources\EmployeeTimeBankSettings;

use App\Filament\Resources\EmployeeTimeBankSettings\Pages\CreateEmployeeTimeBankSetting;
use App\Filament\Resources\EmployeeTimeBankSettings\Pages\EditEmployeeTimeBankSetting;
use App\Filament\Resources\EmployeeTimeBankSettings\Pages\ListEmployeeTimeBankSettings;
use App\Models\EmployeeTimeBankSetting;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class EmployeeTimeBankSettingResource extends Resource
{
    protected static ?string $model = EmployeeTimeBankSetting::class;

    protected static ?string $navigationLabel = 'Config. Banco de Horas';

    protected static ?string $modelLabel = 'Configuração de Banco de Horas';

    protected static ?string $pluralModelLabel = 'Configurações de Banco de Horas';

    protected static string|\UnitEnum|null $navigationGroup = 'Ponto e Jornada';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?int $navigationSort = 41;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Vínculo')
                    ->schema([
                        Select::make('company_id')
                            ->label('Empresa')
                            ->relationship('company', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Select::make('employee_id')
                            ->label('Colaborador')
                            ->relationship('employee', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                    ])
                    ->columns(2),

                Section::make('Regras do Banco de Horas')
                    ->schema([
                        Toggle::make('use_company_rules')
                            ->label('Usar regra da empresa')
                            ->default(true)
                            ->live(),

                        Toggle::make('time_bank_enabled')
                            ->label('Banco de horas ativo')
                            ->default(false)
                            ->disabled(fn ($get): bool => (bool) $get('use_company_rules')),

                        Select::make('overtime_destination')
                            ->label('Destino das horas extras')
                            ->options([
                                'payroll' => 'Folha de pagamento',
                                'time_bank' => 'Banco de horas',
                                'mixed' => 'Híbrido',
                            ])
                            ->default('payroll')
                            ->required()
                            ->disabled(fn ($get): bool => (bool) $get('use_company_rules')),

                        TextInput::make('monthly_bank_limit')
                            ->label('Limite mensal para banco')
                            ->numeric()
                            ->default(20)
                            ->suffix('h')
                            ->disabled(fn ($get): bool => (bool) $get('use_company_rules')),

                        Toggle::make('excess_to_payroll')
                            ->label('Excedente vai para folha')
                            ->default(true)
                            ->disabled(fn ($get): bool => (bool) $get('use_company_rules')),

                        Toggle::make('compensate_delays_with_balance')
                            ->label('Compensar atrasos com saldo')
                            ->default(true)
                            ->disabled(fn ($get): bool => (bool) $get('use_company_rules')),

                        Toggle::make('allow_negative_balance')
                            ->label('Permitir saldo negativo')
                            ->default(false)
                            ->disabled(fn ($get): bool => (bool) $get('use_company_rules')),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('employee.name')
                    ->label('Colaborador')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('company.name')
                    ->label('Empresa')
                    ->searchable()
                    ->sortable(),

                IconColumn::make('use_company_rules')
                    ->label('Regra Empresa')
                    ->boolean(),

                IconColumn::make('time_bank_enabled')
                    ->label('Banco Ativo')
                    ->boolean(),

                TextColumn::make('overtime_destination')
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
                    ->label('Compensa Atraso')
                    ->boolean(),

                TextColumn::make('updated_at')
                    ->label('Atualizado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('company_id')
                    ->label('Empresa')
                    ->relationship('company', 'name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('overtime_destination')
                    ->label('Destino HE')
                    ->options([
                        'payroll' => 'Folha',
                        'time_bank' => 'Banco',
                        'mixed' => 'Híbrido',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->defaultSort('updated_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEmployeeTimeBankSettings::route('/'),
            'create' => CreateEmployeeTimeBankSetting::route('/create'),
            'edit' => EditEmployeeTimeBankSetting::route('/{record}/edit'),
        ];
    }
}