<?php

namespace App\Filament\Resources\PayrollEvents;

use App\Filament\Resources\PayrollEvents\Pages\CreatePayrollEvent;
use App\Filament\Resources\PayrollEvents\Pages\EditPayrollEvent;
use App\Filament\Resources\PayrollEvents\Pages\ListPayrollEvents;
use App\Filament\Resources\PayrollEvents\Schemas\PayrollEventForm;
use App\Filament\Resources\PayrollEvents\Tables\PayrollEventsTable;
use App\Models\PayrollEvent;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class PayrollEventResource extends Resource
{
    protected static ?string $model = PayrollEvent::class;

    protected static ?string $navigationLabel = 'Eventos da Folha';
    protected static ?string $modelLabel = 'Evento da Folha';
    protected static ?string $pluralModelLabel = 'Eventos da Folha';

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-calculator';
    protected static string|UnitEnum|null $navigationGroup = 'Folha';
    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return PayrollEventForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PayrollEventsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPayrollEvents::route('/'),
            'create' => CreatePayrollEvent::route('/create'),
            'edit' => EditPayrollEvent::route('/{record}/edit'),
        ];
    }
}