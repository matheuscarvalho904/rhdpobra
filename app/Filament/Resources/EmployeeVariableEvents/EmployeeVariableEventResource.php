<?php

namespace App\Filament\Resources\EmployeeVariableEvents;

use App\Filament\Resources\EmployeeVariableEvents\Pages\CreateEmployeeVariableEvent;
use App\Filament\Resources\EmployeeVariableEvents\Pages\EditEmployeeVariableEvent;
use App\Filament\Resources\EmployeeVariableEvents\Pages\ListEmployeeVariableEvents;
use App\Filament\Resources\EmployeeVariableEvents\Schemas\EmployeeVariableEventForm;
use App\Filament\Resources\EmployeeVariableEvents\Tables\EmployeeVariableEventsTable;
use App\Models\EmployeeVariableEvent;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class EmployeeVariableEventResource extends Resource
{
    protected static ?string $model = EmployeeVariableEvent::class;

    protected static ?string $navigationLabel = 'Eventos Variáveis';
    protected static ?string $modelLabel = 'Evento Variável';
    protected static ?string $pluralModelLabel = 'Eventos Variáveis';

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-adjustments-horizontal';
    protected static string|UnitEnum|null $navigationGroup = 'Folha';
    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return EmployeeVariableEventForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EmployeeVariableEventsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEmployeeVariableEvents::route('/'),
            'create' => CreateEmployeeVariableEvent::route('/create'),
            'edit' => EditEmployeeVariableEvent::route('/{record}/edit'),
        ];
    }
}