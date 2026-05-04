<?php

namespace App\Filament\Resources\TimePayrollEventMappings;

use App\Filament\Resources\TimePayrollEventMappings\Pages\CreateTimePayrollEventMapping;
use App\Filament\Resources\TimePayrollEventMappings\Pages\EditTimePayrollEventMapping;
use App\Filament\Resources\TimePayrollEventMappings\Pages\ListTimePayrollEventMappings;
use App\Models\TimePayrollEventMapping;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class TimePayrollEventMappingResource extends Resource
{
    protected static ?string $model = TimePayrollEventMapping::class;

    protected static ?string $navigationLabel = 'Eventos do Ponto';
    protected static ?string $modelLabel = 'Mapeamento de Evento';
    protected static ?string $pluralModelLabel = 'Mapeamento de Eventos';

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-adjustments-horizontal';
    protected static string|UnitEnum|null $navigationGroup = 'Ponto';
    protected static ?int $navigationSort = 12;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Configuração')
                ->schema([
                    Select::make('company_id')
                        ->label('Empresa')
                        ->relationship('company', 'name')
                        ->searchable()
                        ->preload()
                        ->nullable(),

                    Select::make('type')
                        ->label('Tipo do Ponto')
                        ->options([
                            'overtime_50' => 'Hora Extra 50%',
                            'overtime_100' => 'Hora Extra 100%',
                            'delay' => 'Atraso',
                            'absence' => 'Falta',
                            'dsr_overtime' => 'DSR sobre Hora Extra',
                            'night_additional' => 'Adicional Noturno',
                        ])
                        ->required(),

                    Select::make('payroll_event_id')
                        ->label('Evento da Folha')
                        ->relationship('payrollEvent', 'name')
                        ->searchable()
                        ->preload()
                        ->required(),

                    Toggle::make('is_active')
                        ->label('Ativo')
                        ->default(true),

                    KeyValue::make('settings')
                        ->label('Configurações Extras')
                        ->keyLabel('Chave')
                        ->valueLabel('Valor')
                        ->columnSpanFull(),
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
                    ->placeholder('Todas')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'overtime_50' => 'Hora Extra 50%',
                        'overtime_100' => 'Hora Extra 100%',
                        'delay' => 'Atraso',
                        'absence' => 'Falta',
                        'dsr_overtime' => 'DSR HE',
                        'night_additional' => 'Adicional Noturno',
                        default => $state ?: '-',
                    }),

                TextColumn::make('payrollEvent.code')
                    ->label('Código')
                    ->badge()
                    ->placeholder('-'),

                TextColumn::make('payrollEvent.name')
                    ->label('Evento da Folha')
                    ->searchable()
                    ->placeholder('-'),

                IconColumn::make('is_active')
                    ->label('Ativo')
                    ->boolean(),

                TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([
                Action::make('editar')
                    ->label('Editar')
                    ->icon('heroicon-o-pencil')
                    ->url(fn (TimePayrollEventMapping $record): string => static::getUrl('edit', [
                        'record' => $record,
                    ])),

                Action::make('excluir')
                    ->label('Excluir')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Excluir mapeamento')
                    ->modalDescription('Deseja realmente excluir este mapeamento de evento?')
                    ->modalSubmitActionLabel('Excluir')
                    ->action(fn (TimePayrollEventMapping $record): bool => $record->delete()),
            ])
            ->defaultSort('id', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTimePayrollEventMappings::route('/'),
            'create' => CreateTimePayrollEventMapping::route('/create'),
            'edit' => EditTimePayrollEventMapping::route('/{record}/edit'),
        ];
    }
}