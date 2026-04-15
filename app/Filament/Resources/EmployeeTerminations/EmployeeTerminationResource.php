<?php

namespace App\Filament\Resources\EmployeeTerminations;

use App\Filament\Resources\EmployeeTerminations\Pages\CreateEmployeeTermination;
use App\Filament\Resources\EmployeeTerminations\Pages\EditEmployeeTermination;
use App\Filament\Resources\EmployeeTerminations\Pages\ListEmployeeTerminations;
use App\Filament\Resources\EmployeeTerminations\Schemas\EmployeeTerminationForm;
use App\Filament\Resources\EmployeeTerminations\Tables\EmployeeTerminationsTable;
use App\Models\EmployeeTermination;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class EmployeeTerminationResource extends Resource
{
    protected static ?string $model = EmployeeTermination::class;

    protected static ?string $navigationLabel = 'Desligamentos';
    protected static ?string $modelLabel = 'Desligamento';
    protected static ?string $pluralModelLabel = 'Desligamentos';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-minus';
    protected static string|UnitEnum|null $navigationGroup = 'RH e DP';
    protected static ?int $navigationSort = 25;

    public static function form(Schema $schema): Schema
    {
        return EmployeeTerminationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EmployeeTerminationsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEmployeeTerminations::route('/'),
            'create' => CreateEmployeeTermination::route('/create'),
            'edit' => EditEmployeeTermination::route('/{record}/edit'),
        ];
    }
}