<?php

namespace App\Filament\Resources\EmployeeSalaryHistories;

use App\Filament\Resources\EmployeeSalaryHistories\Pages\CreateEmployeeSalaryHistory;
use App\Filament\Resources\EmployeeSalaryHistories\Pages\EditEmployeeSalaryHistory;
use App\Filament\Resources\EmployeeSalaryHistories\Pages\ListEmployeeSalaryHistories;
use App\Filament\Resources\EmployeeSalaryHistories\Schemas\EmployeeSalaryHistoryForm;
use App\Filament\Resources\EmployeeSalaryHistories\Tables\EmployeeSalaryHistoriesTable;
use App\Models\EmployeeSalaryHistory;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class EmployeeSalaryHistoryResource extends Resource
{
    protected static ?string $model = EmployeeSalaryHistory::class;

    protected static ?string $navigationLabel = 'Histórico Salarial';
    protected static ?string $modelLabel = 'Histórico Salarial';
    protected static ?string $pluralModelLabel = 'Históricos Salariais';

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-currency-dollar';
    protected static string|UnitEnum|null $navigationGroup = 'RH';
    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return EmployeeSalaryHistoryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EmployeeSalaryHistoriesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEmployeeSalaryHistories::route('/'),
            'create' => CreateEmployeeSalaryHistory::route('/create'),
            'edit' => EditEmployeeSalaryHistory::route('/{record}/edit'),
        ];
    }
}