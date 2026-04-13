<?php

namespace App\Filament\Resources\TimeClosings;

use App\Filament\Resources\TimeClosings\Pages\CreateTimeClosing;
use App\Filament\Resources\TimeClosings\Pages\EditTimeClosing;
use App\Filament\Resources\TimeClosings\Pages\ListTimeClosings;
use App\Filament\Resources\TimeClosings\Schemas\TimeClosingForm;
use App\Filament\Resources\TimeClosings\Tables\TimeClosingsTable;
use App\Models\TimeClosing;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class TimeClosingResource extends Resource
{
    protected static ?string $model = TimeClosing::class;

    protected static ?string $navigationLabel = 'Fechamentos de Ponto';
    protected static ?string $modelLabel = 'Fechamento de Ponto';
    protected static ?string $pluralModelLabel = 'Fechamentos de Ponto';

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-calendar-days';
    protected static string|UnitEnum|null $navigationGroup = 'Ponto';
    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return TimeClosingForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TimeClosingsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTimeClosings::route('/'),
            'create' => CreateTimeClosing::route('/create'),
            'edit' => EditTimeClosing::route('/{record}/edit'),
        ];
    }
}