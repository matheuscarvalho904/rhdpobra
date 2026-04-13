<?php

namespace App\Filament\Resources\TimeEntries;

use App\Filament\Resources\TimeEntries\Pages\CreateTimeEntry;
use App\Filament\Resources\TimeEntries\Pages\EditTimeEntry;
use App\Filament\Resources\TimeEntries\Pages\ListTimeEntries;
use App\Filament\Resources\TimeEntries\Schemas\TimeEntryForm;
use App\Filament\Resources\TimeEntries\Tables\TimeEntriesTable;
use App\Models\TimeEntry;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class TimeEntryResource extends Resource
{
    protected static ?string $model = TimeEntry::class;

    protected static ?string $navigationLabel = 'Lançamentos de Ponto';
    protected static ?string $modelLabel = 'Lançamento de Ponto';
    protected static ?string $pluralModelLabel = 'Lançamentos de Ponto';

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-clock';
    protected static string|UnitEnum|null $navigationGroup = 'Ponto';
    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return TimeEntryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TimeEntriesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTimeEntries::route('/'),
            'create' => CreateTimeEntry::route('/create'),
            'edit' => EditTimeEntry::route('/{record}/edit'),
        ];
    }
}