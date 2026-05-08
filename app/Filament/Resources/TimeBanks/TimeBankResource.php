<?php

namespace App\Filament\Resources\TimeBanks;

use App\Filament\Resources\TimeBanks\Pages\ListTimeBanks;
use App\Filament\Resources\TimeBanks\Pages\ViewTimeBank;
use App\Filament\Resources\TimeBanks\RelationManagers\MovementsRelationManager;
use App\Models\TimeBank;
use Filament\Actions\ViewAction;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class TimeBankResource extends Resource
{
    protected static ?string $model = TimeBank::class;

    protected static ?string $navigationLabel = 'Banco de Horas';

protected static ?string $modelLabel = 'Banco de Horas';

protected static ?string $pluralModelLabel = 'Banco de Horas';

protected static string|\UnitEnum|null $navigationGroup = 'Ponto e Jornada';

protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clock';

protected static ?int $navigationSort = 40;

    public static function form(Schema $schema): Schema
    {
        return $schema;
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

                TextColumn::make('positive_balance_hours')
                    ->label('Horas Positivas')
                    ->numeric(decimalPlaces: 2)
                    ->sortable()
                    ->suffix(' h'),

                TextColumn::make('negative_balance_hours')
                    ->label('Horas Negativas')
                    ->numeric(decimalPlaces: 2)
                    ->sortable()
                    ->suffix(' h'),

                TextColumn::make('net_balance_hours')
                    ->label('Saldo')
                    ->numeric(decimalPlaces: 2)
                    ->sortable()
                    ->suffix(' h')
                    ->badge()
                    ->color(fn ($state): string => match (true) {
                        (float) $state > 0 => 'success',
                        (float) $state < 0 => 'danger',
                        default => 'gray',
                    }),

                IconColumn::make('is_active')
                    ->label('Ativo')
                    ->boolean(),

                TextColumn::make('last_movement_at')
                    ->label('Última Movimentação')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('updated_at')
                    ->label('Atualizado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('company_id')
                    ->label('Empresa')
                    ->relationship('company', 'name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('is_active')
                    ->label('Status')
                    ->options([
                        1 => 'Ativo',
                        0 => 'Inativo',
                    ]),
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('Ver Extrato'),
            ])
            ->defaultSort('net_balance_hours', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            MovementsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTimeBanks::route('/'),
            'view' => ViewTimeBank::route('/{record}'),
        ];
    }
}