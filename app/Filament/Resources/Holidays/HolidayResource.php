<?php

namespace App\Filament\Resources\Holidays;

use App\Filament\Resources\Holidays\Pages\CreateHoliday;
use App\Filament\Resources\Holidays\Pages\EditHoliday;
use App\Filament\Resources\Holidays\Pages\ListHolidays;
use App\Filament\Resources\Holidays\Schemas\HolidayForm;
use App\Filament\Resources\Holidays\Tables\HolidaysTable;
use App\Models\Holiday;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class HolidayResource extends Resource
{
    protected static ?string $model = Holiday::class;

    protected static ?string $navigationLabel = 'Feriados';
    protected static ?string $modelLabel = 'Feriado';
    protected static ?string $pluralModelLabel = 'Feriados';

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-calendar-days';
    protected static string|UnitEnum|null $navigationGroup = 'Cadastros';
    protected static ?int $navigationSort = 13;

    public static function form(Schema $schema): Schema
    {
        return HolidayForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return HolidaysTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListHolidays::route('/'),
            'create' => CreateHoliday::route('/create'),
            'edit' => EditHoliday::route('/{record}/edit'),
        ];
    }
}