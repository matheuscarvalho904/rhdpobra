<?php

namespace App\Filament\Resources\EmployeeExternalMappings;

use App\Filament\Resources\EmployeeExternalMappings\Pages\CreateEmployeeExternalMapping;
use App\Filament\Resources\EmployeeExternalMappings\Pages\EditEmployeeExternalMapping;
use App\Filament\Resources\EmployeeExternalMappings\Pages\ListEmployeeExternalMappings;
use App\Filament\Resources\EmployeeExternalMappings\Schemas\EmployeeExternalMappingForm;
use App\Filament\Resources\EmployeeExternalMappings\Tables\EmployeeExternalMappingsTable;
use App\Models\EmployeeExternalMapping;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class EmployeeExternalMappingResource extends Resource
{
    protected static ?string $model = EmployeeExternalMapping::class;

    protected static ?string $navigationLabel = 'Vínculos Sólides';
    protected static ?string $modelLabel = 'Vínculo Externo';
    protected static ?string $pluralModelLabel = 'Vínculos Externos';

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-link';
    protected static string|UnitEnum|null $navigationGroup = 'Ponto';
    protected static ?int $navigationSort = 8;

    public static function form(Schema $schema): Schema
    {
        return EmployeeExternalMappingForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EmployeeExternalMappingsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEmployeeExternalMappings::route('/'),
            'create' => CreateEmployeeExternalMapping::route('/create'),
            'edit' => EditEmployeeExternalMapping::route('/{record}/edit'),
        ];
    }
}