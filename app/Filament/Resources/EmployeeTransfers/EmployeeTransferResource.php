<?php

namespace App\Filament\Resources\EmployeeTransfers;

use App\Filament\Resources\EmployeeTransfers\Pages\CreateEmployeeTransfer;
use App\Filament\Resources\EmployeeTransfers\Pages\EditEmployeeTransfer;
use App\Filament\Resources\EmployeeTransfers\Pages\ListEmployeeTransfers;
use App\Filament\Resources\EmployeeTransfers\Schemas\EmployeeTransferForm;
use App\Filament\Resources\EmployeeTransfers\Tables\EmployeeTransfersTable;
use App\Models\EmployeeTransfer;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class EmployeeTransferResource extends Resource
{
    protected static ?string $model = EmployeeTransfer::class;

    protected static ?string $navigationLabel = 'Transferências';
    protected static ?string $modelLabel = 'Transferência';
    protected static ?string $pluralModelLabel = 'Transferências';

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-arrow-right-circle';
    protected static string|UnitEnum|null $navigationGroup = 'RH';
    protected static ?int $navigationSort = 5;

    public static function form(Schema $schema): Schema
    {
        return EmployeeTransferForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EmployeeTransfersTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEmployeeTransfers::route('/'),
            'create' => CreateEmployeeTransfer::route('/create'),
            'edit' => EditEmployeeTransfer::route('/{record}/edit'),
        ];
    }
}