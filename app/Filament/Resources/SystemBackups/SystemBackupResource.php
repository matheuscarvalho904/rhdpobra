<?php

namespace App\Filament\Resources\SystemBackups;

use App\Filament\Resources\SystemBackups\Pages\CreateSystemBackup;
use App\Filament\Resources\SystemBackups\Pages\EditSystemBackup;
use App\Filament\Resources\SystemBackups\Pages\ListSystemBackups;
use App\Filament\Resources\SystemBackups\Schemas\SystemBackupForm;
use App\Filament\Resources\SystemBackups\Tables\SystemBackupsTable;
use App\Models\SystemBackup;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class SystemBackupResource extends Resource
{
    protected static ?string $model = SystemBackup::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedServerStack;

    protected static ?string $navigationLabel = 'Backups';

    protected static ?string $modelLabel = 'Backup';

    protected static ?string $pluralModelLabel = 'Backups';

    protected static string|UnitEnum|null $navigationGroup = 'Sistema';
    


    protected static ?int $navigationSort = 99;

    public static function form(Schema $schema): Schema
    {
        return SystemBackupForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SystemBackupsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSystemBackups::route('/'),
            'create' => CreateSystemBackup::route('/create'),
            'edit' => EditSystemBackup::route('/{record}/edit'),
        ];
    }
}