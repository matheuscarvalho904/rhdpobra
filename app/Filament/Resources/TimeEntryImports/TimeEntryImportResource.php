<?php

namespace App\Filament\Resources\TimeEntryImports;

use App\Filament\Resources\TimeEntryImports\Pages\CreateTimeEntryImport;
use App\Filament\Resources\TimeEntryImports\Pages\EditTimeEntryImport;
use App\Filament\Resources\TimeEntryImports\Pages\ListTimeEntryImports;
use App\Filament\Resources\TimeEntryImports\Schemas\TimeEntryImportForm;
use App\Filament\Resources\TimeEntryImports\Tables\TimeEntryImportsTable;
use App\Models\TimeEntryImport;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class TimeEntryImportResource extends Resource
{
    protected static ?string $model = TimeEntryImport::class;

    protected static ?string $navigationLabel = 'Importações de Ponto';
    protected static ?string $modelLabel = 'Importação de Ponto';
    protected static ?string $pluralModelLabel = 'Importações de Ponto';

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-arrow-up-tray';
    protected static string|UnitEnum|null $navigationGroup = 'Ponto';
    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return TimeEntryImportForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TimeEntryImportsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTimeEntryImports::route('/'),
            'create' => CreateTimeEntryImport::route('/create'),
            'edit' => EditTimeEntryImport::route('/{record}/edit'),
        ];
    }
}