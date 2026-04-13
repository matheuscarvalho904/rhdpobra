<?php

namespace App\Filament\Resources\PayrollCompetencies;

use App\Filament\Resources\PayrollCompetencies\Pages\CreatePayrollCompetency;
use App\Filament\Resources\PayrollCompetencies\Pages\EditPayrollCompetency;
use App\Filament\Resources\PayrollCompetencies\Pages\ListPayrollCompetencies;
use App\Filament\Resources\PayrollCompetencies\Schemas\PayrollCompetencyForm;
use App\Filament\Resources\PayrollCompetencies\Tables\PayrollCompetenciesTable;
use App\Models\PayrollCompetency;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class PayrollCompetencyResource extends Resource
{
    protected static ?string $model = PayrollCompetency::class;

    protected static ?string $navigationLabel = 'Competências';
    protected static ?string $modelLabel = 'Competência';
    protected static ?string $pluralModelLabel = 'Competências';

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-calendar';
    protected static string|UnitEnum|null $navigationGroup = 'Folha';
    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return PayrollCompetencyForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PayrollCompetenciesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPayrollCompetencies::route('/'),
            'create' => CreatePayrollCompetency::route('/create'),
            'edit' => EditPayrollCompetency::route('/{record}/edit'),
        ];
    }
}