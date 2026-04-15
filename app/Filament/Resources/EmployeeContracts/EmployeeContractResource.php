<?php

namespace App\Filament\Resources\EmployeeContracts;

use App\Filament\Resources\EmployeeContracts\Pages\CreateEmployeeContract;
use App\Filament\Resources\EmployeeContracts\Pages\EditEmployeeContract;
use App\Filament\Resources\EmployeeContracts\Pages\ListEmployeeContracts;
use App\Filament\Resources\EmployeeContracts\Schemas\EmployeeContractForm;
use App\Filament\Resources\EmployeeContracts\Tables\EmployeeContractsTable;
use App\Models\EmployeeContract;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class EmployeeContractResource extends Resource
{
    protected static ?string $model = EmployeeContract::class;

    protected static ?string $navigationLabel = 'Contratos';
    protected static ?string $modelLabel = 'Contrato';
    protected static ?string $pluralModelLabel = 'Contratos';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-identification';
    protected static string|UnitEnum|null $navigationGroup = 'RH e DP';

    public static function form(Schema $schema): Schema
    {
        return EmployeeContractForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EmployeeContractsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEmployeeContracts::route('/'),
            'create' => CreateEmployeeContract::route('/create'),
            'edit' => EditEmployeeContract::route('/{record}/edit'),
        ];
    }
}