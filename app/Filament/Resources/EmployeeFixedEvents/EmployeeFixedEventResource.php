<?php

namespace App\Filament\Resources\EmployeeFixedEvents;

use App\Filament\Resources\EmployeeFixedEvents\Pages\CreateEmployeeFixedEvent;
use App\Filament\Resources\EmployeeFixedEvents\Pages\EditEmployeeFixedEvent;
use App\Filament\Resources\EmployeeFixedEvents\Pages\ListEmployeeFixedEvents;
use App\Filament\Resources\EmployeeFixedEvents\Schemas\EmployeeFixedEventForm;
use App\Filament\Resources\EmployeeFixedEvents\Tables\EmployeeFixedEventsTable;
use App\Models\EmployeeFixedEvent;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class EmployeeFixedEventResource extends Resource
{
    protected static ?string $model = EmployeeFixedEvent::class;

    protected static ?string $navigationLabel = 'Eventos Fixos';
    protected static ?string $modelLabel = 'Evento Fixo';
    protected static ?string $pluralModelLabel = 'Eventos Fixos';

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-paper-clip';
    protected static string|UnitEnum|null $navigationGroup = 'Folha';
    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return EmployeeFixedEventForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EmployeeFixedEventsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEmployeeFixedEvents::route('/'),
            'create' => CreateEmployeeFixedEvent::route('/create'),
            'edit' => EditEmployeeFixedEvent::route('/{record}/edit'),
        ];
    }
}