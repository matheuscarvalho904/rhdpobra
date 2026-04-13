<?php

namespace App\Filament\Resources\EmployeeWorkShifts;

use App\Filament\Resources\EmployeeWorkShifts\Pages\CreateEmployeeWorkShift;
use App\Filament\Resources\EmployeeWorkShifts\Pages\EditEmployeeWorkShift;
use App\Filament\Resources\EmployeeWorkShifts\Pages\ListEmployeeWorkShifts;
use App\Filament\Resources\EmployeeWorkShifts\Schemas\EmployeeWorkShiftForm;
use App\Filament\Resources\EmployeeWorkShifts\Tables\EmployeeWorkShiftsTable;
use App\Models\EmployeeWorkShift;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class EmployeeWorkShiftResource extends Resource
{
    protected static ?string $model = EmployeeWorkShift::class;

    protected static ?string $navigationLabel = 'Jornadas do Colaborador';
    protected static ?string $modelLabel = 'Jornada do Colaborador';
    protected static ?string $pluralModelLabel = 'Jornadas do Colaborador';

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-clock';
    protected static string|UnitEnum|null $navigationGroup = 'RH';
    protected static ?int $navigationSort = 6;

    public static function form(Schema $schema): Schema
    {
        return EmployeeWorkShiftForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EmployeeWorkShiftsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEmployeeWorkShifts::route('/'),
            'create' => CreateEmployeeWorkShift::route('/create'),
            'edit' => EditEmployeeWorkShift::route('/{record}/edit'),
        ];
    }
}