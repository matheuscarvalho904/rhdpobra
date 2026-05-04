<?php

namespace App\Filament\Resources\WorkSchedules;

use App\Filament\Resources\WorkSchedules\Pages\CreateWorkSchedule;
use App\Filament\Resources\WorkSchedules\Pages\EditWorkSchedule;
use App\Filament\Resources\WorkSchedules\Pages\ListWorkSchedules;
use App\Filament\Resources\WorkSchedules\RelationManagers\DaysRelationManager;
use App\Filament\Resources\WorkSchedules\Schemas\WorkScheduleForm;
use App\Filament\Resources\WorkSchedules\Tables\WorkSchedulesTable;
use App\Models\WorkSchedule;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class WorkScheduleResource extends Resource
{
    protected static ?string $model = WorkSchedule::class;

    protected static ?string $navigationLabel = 'Jornadas';
    protected static ?string $modelLabel = 'Jornada';
    protected static ?string $pluralModelLabel = 'Jornadas';

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-clock';
    protected static string|UnitEnum|null $navigationGroup = 'Ponto';
    protected static ?int $navigationSort = 20;

    public static function form(Schema $schema): Schema
    {
        return WorkScheduleForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return WorkSchedulesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            DaysRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListWorkSchedules::route('/'),
            'create' => CreateWorkSchedule::route('/create'),
            'edit' => EditWorkSchedule::route('/{record}/edit'),
        ];
    }
}