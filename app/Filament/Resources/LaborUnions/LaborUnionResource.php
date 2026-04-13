<?php

namespace App\Filament\Resources\LaborUnions;

use App\Filament\Resources\LaborUnions\Pages\CreateLaborUnion;
use App\Filament\Resources\LaborUnions\Pages\EditLaborUnion;
use App\Filament\Resources\LaborUnions\Pages\ListLaborUnions;
use App\Filament\Resources\LaborUnions\Schemas\LaborUnionForm;
use App\Filament\Resources\LaborUnions\Tables\LaborUnionsTable;
use App\Models\LaborUnion;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class LaborUnionResource extends Resource
{
    protected static ?string $model = LaborUnion::class;

    protected static ?string $navigationLabel = 'Sindicatos';
    protected static ?string $modelLabel = 'Sindicato';
    protected static ?string $pluralModelLabel = 'Sindicatos';

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-user-group';
    protected static string|UnitEnum|null $navigationGroup = 'Cadastros';
    protected static ?int $navigationSort = 9;

    public static function form(Schema $schema): Schema
    {
        return LaborUnionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LaborUnionsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLaborUnions::route('/'),
            'create' => CreateLaborUnion::route('/create'),
            'edit' => EditLaborUnion::route('/{record}/edit'),
        ];
    }
}