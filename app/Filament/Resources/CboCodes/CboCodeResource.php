<?php

namespace App\Filament\Resources\CboCodes;

use App\Filament\Resources\CboCodes\Pages\CreateCboCode;
use App\Filament\Resources\CboCodes\Pages\EditCboCode;
use App\Filament\Resources\CboCodes\Pages\ListCboCodes;
use App\Filament\Resources\CboCodes\Schemas\CboCodeForm;
use App\Filament\Resources\CboCodes\Tables\CboCodesTable;
use App\Models\CboCode;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class CboCodeResource extends Resource
{
    protected static ?string $model = CboCode::class;

    protected static ?string $navigationLabel = 'CBO';
    protected static ?string $modelLabel = 'CBO';
    protected static ?string $pluralModelLabel = 'CBO';

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-identification';
    protected static string|UnitEnum|null $navigationGroup = 'Cadastros';
    protected static ?int $navigationSort = 8;

    public static function form(Schema $schema): Schema
    {
        return CboCodeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CboCodesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCboCodes::route('/'),
            'create' => CreateCboCode::route('/create'),
            'edit' => EditCboCode::route('/{record}/edit'),
        ];
    }
}