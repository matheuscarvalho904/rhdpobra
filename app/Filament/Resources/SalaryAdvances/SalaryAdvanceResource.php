<?php

namespace App\Filament\Resources\SalaryAdvances;

use App\Filament\Resources\SalaryAdvances\Pages\CreateSalaryAdvance;
use App\Filament\Resources\SalaryAdvances\Pages\EditSalaryAdvance;
use App\Filament\Resources\SalaryAdvances\Pages\ListSalaryAdvances;
use App\Filament\Resources\SalaryAdvances\Schemas\SalaryAdvanceForm;
use App\Filament\Resources\SalaryAdvances\Tables\SalaryAdvancesTable;
use App\Models\SalaryAdvance;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class SalaryAdvanceResource extends Resource
{
    protected static ?string $model = SalaryAdvance::class;

    protected static ?string $navigationLabel = 'Adiantamentos';
    protected static ?string $modelLabel = 'Adiantamento';
    protected static ?string $pluralModelLabel = 'Adiantamentos';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-banknotes';
    protected static string|UnitEnum|null $navigationGroup = 'Folha';
    protected static ?int $navigationSort = 6;

    public static function form(Schema $schema): Schema
    {
        return SalaryAdvanceForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SalaryAdvancesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSalaryAdvances::route('/'),
            'create' => CreateSalaryAdvance::route('/create'),
            'edit' => EditSalaryAdvance::route('/{record}/edit'),
        ];
    }
}