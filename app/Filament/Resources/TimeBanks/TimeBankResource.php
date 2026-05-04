<?php

namespace App\Filament\Resources\TimeBanks;

use App\Filament\Resources\TimeBanks\Pages\ListTimeBanks;
use App\Filament\Resources\TimeBanks\RelationManagers\MovementsRelationManager;
use App\Filament\Resources\TimeBanks\Tables\TimeBanksTable;
use App\Models\TimeBank;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use UnitEnum;

class TimeBankResource extends Resource
{
    protected static ?string $model = TimeBank::class;

    protected static ?string $navigationLabel = 'Banco de Horas';
    protected static ?string $modelLabel = 'Banco de Horas';
    protected static ?string $pluralModelLabel = 'Banco de Horas';

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-banknotes';
    protected static string|UnitEnum|null $navigationGroup = 'Ponto';
    protected static ?int $navigationSort = 30;

    public static function table(Table $table): Table
    {
        return TimeBanksTable::configure($table);
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
        ];
    }
}