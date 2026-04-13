<?php

namespace App\Filament\Resources\EmployeeDependents;

use App\Filament\Resources\EmployeeDependents\Pages\CreateEmployeeDependent;
use App\Filament\Resources\EmployeeDependents\Pages\EditEmployeeDependent;
use App\Filament\Resources\EmployeeDependents\Pages\ListEmployeeDependents;
use App\Filament\Resources\EmployeeDependents\Schemas\EmployeeDependentForm;
use App\Filament\Resources\EmployeeDependents\Tables\EmployeeDependentsTable;
use App\Models\EmployeeDependent;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class EmployeeDependentResource extends Resource
{
    protected static ?string $model = EmployeeDependent::class;

    protected static ?string $navigationLabel = 'Dependentes';
    protected static ?string $modelLabel = 'Dependente';
    protected static ?string $pluralModelLabel = 'Dependentes';

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-user-group';
    protected static string|UnitEnum|null $navigationGroup = 'RH';
    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return EmployeeDependentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EmployeeDependentsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEmployeeDependents::route('/'),
            'create' => CreateEmployeeDependent::route('/create'),
            'edit' => EditEmployeeDependent::route('/{record}/edit'),
        ];
    }
}