<?php

namespace App\Filament\Resources\FinancialCategories;

use App\Filament\Resources\FinancialCategories\Pages\CreateFinancialCategory;
use App\Filament\Resources\FinancialCategories\Pages\EditFinancialCategory;
use App\Filament\Resources\FinancialCategories\Pages\ListFinancialCategories;
use App\Filament\Resources\FinancialCategories\Schemas\FinancialCategoryForm;
use App\Filament\Resources\FinancialCategories\Tables\FinancialCategoriesTable;
use App\Models\FinancialCategory;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class FinancialCategoryResource extends Resource
{
    protected static ?string $model = FinancialCategory::class;

    protected static ?string $navigationLabel = 'Categorias Financeiras';
    protected static ?string $modelLabel = 'Categoria Financeira';
    protected static ?string $pluralModelLabel = 'Categorias Financeiras';

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-wallet';
    protected static string|UnitEnum|null $navigationGroup = 'Cadastros';
    protected static ?int $navigationSort = 14;

    public static function form(Schema $schema): Schema
    {
        return FinancialCategoryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FinancialCategoriesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListFinancialCategories::route('/'),
            'create' => CreateFinancialCategory::route('/create'),
            'edit' => EditFinancialCategory::route('/{record}/edit'),
        ];
    }
}