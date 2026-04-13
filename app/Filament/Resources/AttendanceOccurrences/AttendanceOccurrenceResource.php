<?php

namespace App\Filament\Resources\AttendanceOccurrences;

use App\Filament\Resources\AttendanceOccurrences\Pages\CreateAttendanceOccurrence;
use App\Filament\Resources\AttendanceOccurrences\Pages\EditAttendanceOccurrence;
use App\Filament\Resources\AttendanceOccurrences\Pages\ListAttendanceOccurrences;
use App\Filament\Resources\AttendanceOccurrences\Schemas\AttendanceOccurrenceForm;
use App\Filament\Resources\AttendanceOccurrences\Tables\AttendanceOccurrencesTable;
use App\Models\AttendanceOccurrence;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class AttendanceOccurrenceResource extends Resource
{
    protected static ?string $model = AttendanceOccurrence::class;

    protected static ?string $navigationLabel = 'Ocorrências';
    protected static ?string $modelLabel = 'Ocorrência';
    protected static ?string $pluralModelLabel = 'Ocorrências';

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-exclamation-circle';
    protected static string|UnitEnum|null $navigationGroup = 'Ponto';
    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return AttendanceOccurrenceForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AttendanceOccurrencesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAttendanceOccurrences::route('/'),
            'create' => CreateAttendanceOccurrence::route('/create'),
            'edit' => EditAttendanceOccurrence::route('/{record}/edit'),
        ];
    }
}