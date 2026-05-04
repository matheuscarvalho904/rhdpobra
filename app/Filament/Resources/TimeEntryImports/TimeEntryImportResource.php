<?php

namespace App\Filament\Resources\TimeEntryImports;

use App\Filament\Resources\TimeEntryImports\Pages\ListTimeEntryImports;
use App\Filament\Resources\TimeEntryImports\Tables\TimeEntryImportsTable;
use App\Models\TimeEntryImport;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use UnitEnum;

class TimeEntryImportResource extends Resource
{
    protected static ?string $model = TimeEntryImport::class;

    protected static ?string $navigationLabel = 'Histórico de Importações';
    protected static ?string $modelLabel = 'Importação de Ponto';
    protected static ?string $pluralModelLabel = 'Histórico de Importações';

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-arrow-path';
    protected static string|UnitEnum|null $navigationGroup = 'Ponto';
    protected static ?int $navigationSort = 9;

    public static function table(Table $table): Table
    {
        return TimeEntryImportsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTimeEntryImports::route('/'),
        ];
    }
}