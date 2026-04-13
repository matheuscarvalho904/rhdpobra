<?php

namespace App\Filament\Resources\WorkShifts;

use App\Filament\Resources\WorkShifts\Pages\CreateWorkShift;
use App\Filament\Resources\WorkShifts\Pages\EditWorkShift;
use App\Filament\Resources\WorkShifts\Pages\ListWorkShifts;
use App\Filament\Resources\WorkShifts\Schemas\WorkShiftForm;
use App\Filament\Resources\WorkShifts\Tables\WorkShiftsTable;
use App\Models\WorkShift;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class WorkShiftResource extends Resource
{
    protected static ?string $model = WorkShift::class;

    protected static ?string $navigationLabel = 'Jornadas';
    protected static ?string $modelLabel = 'Jornada';
    protected static ?string $pluralModelLabel = 'Jornadas';

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-clock';
    protected static string|UnitEnum|null $navigationGroup = 'Cadastros';
    protected static ?int $navigationSort = 12;

    public static function form(Schema $schema): Schema
    {
        return WorkShiftForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return WorkShiftsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListWorkShifts::route('/'),
            'create' => CreateWorkShift::route('/create'),
            'edit' => EditWorkShift::route('/{record}/edit'),
        ];
    }
}