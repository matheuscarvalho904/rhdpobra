<?php

namespace App\Filament\Resources\CostCenters;

use App\Filament\Resources\CostCenters\Pages\CreateCostCenter;
use App\Filament\Resources\CostCenters\Pages\EditCostCenter;
use App\Filament\Resources\CostCenters\Pages\ListCostCenters;
use App\Filament\Resources\CostCenters\Schemas\CostCenterForm;
use App\Filament\Resources\CostCenters\Tables\CostCentersTable;
use App\Models\CostCenter;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class CostCenterResource extends Resource
{
    protected static ?string $model = CostCenter::class;

    protected static ?string $navigationLabel = 'Centros de Custo';
    protected static ?string $modelLabel = 'Centro de Custo';
    protected static ?string $pluralModelLabel = 'Centros de Custo';

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-wallet';
    protected static string|UnitEnum|null $navigationGroup = 'Cadastros';
    protected static ?int $navigationSort = 5;

    public static function form(Schema $schema): Schema
    {
        return CostCenterForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CostCentersTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCostCenters::route('/'),
            'create' => CreateCostCenter::route('/create'),
            'edit' => EditCostCenter::route('/{record}/edit'),
        ];
    }
}