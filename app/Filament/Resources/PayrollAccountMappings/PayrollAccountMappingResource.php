<?php

namespace App\Filament\Resources\PayrollAccountMappings;

use App\Filament\Resources\PayrollAccountMappings\Schemas\PayrollAccountMappingForm;
use App\Filament\Resources\PayrollAccountMappings\Tables\PayrollAccountMappingsTable;
use App\Models\PayrollAccountMapping;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class PayrollAccountMappingResource extends Resource
{
    protected static ?string $model = PayrollAccountMapping::class;

    protected static ?string $navigationLabel = 'Mapeamento Contábil';
    protected static ?string $modelLabel = 'Mapeamento Contábil';
    protected static ?string $pluralModelLabel = 'Mapeamentos Contábeis';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-banknotes';
    protected static string|UnitEnum|null $navigationGroup = 'Folha';

    public static function form(Schema $schema): Schema
    {
        return PayrollAccountMappingForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PayrollAccountMappingsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPayrollAccountMappings::route('/'),
            'create' => Pages\CreatePayrollAccountMapping::route('/create'),
            'edit' => Pages\EditPayrollAccountMapping::route('/{record}/edit'),
        ];
    }
}