<?php

namespace App\Filament\Resources\EmployeeWorkSchedules;

use App\Filament\Resources\EmployeeWorkSchedules\Pages\CreateEmployeeWorkSchedule;
use App\Filament\Resources\EmployeeWorkSchedules\Pages\EditEmployeeWorkSchedule;
use App\Filament\Resources\EmployeeWorkSchedules\Pages\ListEmployeeWorkSchedules;
use App\Filament\Resources\EmployeeWorkSchedules\Schemas\EmployeeWorkScheduleForm;
use App\Filament\Resources\EmployeeWorkSchedules\Tables\EmployeeWorkSchedulesTable;
use App\Models\EmployeeWorkSchedule;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class EmployeeWorkScheduleResource extends Resource
{
    protected static ?string $model = EmployeeWorkSchedule::class;

    protected static ?string $navigationLabel = 'Jornadas dos Colaboradores';
    protected static ?string $modelLabel = 'Jornada do Colaborador';
    protected static ?string $pluralModelLabel = 'Jornadas dos Colaboradores';

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-calendar-days';
    protected static string|UnitEnum|null $navigationGroup = 'Ponto';
    protected static ?int $navigationSort = 21;

    public static function form(Schema $schema): Schema
    {
        return EmployeeWorkScheduleForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EmployeeWorkSchedulesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEmployeeWorkSchedules::route('/'),
            'create' => CreateEmployeeWorkSchedule::route('/create'),
            'edit' => EditEmployeeWorkSchedule::route('/{record}/edit'),
        ];
    }
}