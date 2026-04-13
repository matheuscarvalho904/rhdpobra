<?php

namespace App\Filament\Resources\HourBanks;

use App\Filament\Resources\HourBanks\Pages\CreateHourBank;
use App\Filament\Resources\HourBanks\Pages\EditHourBank;
use App\Filament\Resources\HourBanks\Pages\ListHourBanks;
use App\Filament\Resources\HourBanks\Schemas\HourBankForm;
use App\Filament\Resources\HourBanks\Tables\HourBanksTable;
use App\Models\HourBank;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class HourBankResource extends Resource
{
    protected static ?string $model = HourBank::class;

    protected static ?string $navigationLabel = 'Banco de Horas';
    protected static ?string $modelLabel = 'Banco de Horas';
    protected static ?string $pluralModelLabel = 'Banco de Horas';

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-clock';
    protected static string|UnitEnum|null $navigationGroup = 'Ponto';
    protected static ?int $navigationSort = 5;

    public static function form(Schema $schema): Schema
    {
        return HourBankForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return HourBanksTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListHourBanks::route('/'),
            'create' => CreateHourBank::route('/create'),
            'edit' => EditHourBank::route('/{record}/edit'),
        ];
    }
}