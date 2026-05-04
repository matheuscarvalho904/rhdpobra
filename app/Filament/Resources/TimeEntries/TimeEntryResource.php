<?php

namespace App\Filament\Resources\TimeEntries;

use App\Filament\Resources\TimeEntries\Pages\ListTimeEntries;
use App\Filament\Resources\TimeEntries\Tables\TimeEntriesTable;
use App\Models\TimeEntry;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use UnitEnum;

class TimeEntryResource extends Resource
{
    protected static ?string $model = TimeEntry::class;

    protected static ?string $navigationLabel = 'Marcações de Ponto';
    protected static ?string $modelLabel = 'Marcação de Ponto';
    protected static ?string $pluralModelLabel = 'Marcações de Ponto';

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-clock';
    protected static string|UnitEnum|null $navigationGroup = 'Ponto';
    protected static ?int $navigationSort = 10;

    public static function table(Table $table): Table
    {
        return TimeEntriesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTimeEntries::route('/'),
        ];
    }
}