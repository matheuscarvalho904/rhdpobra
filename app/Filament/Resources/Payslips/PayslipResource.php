<?php

namespace App\Filament\Resources\Payslips;

use App\Filament\Resources\Payslips\Pages\CreatePayslip;
use App\Filament\Resources\Payslips\Pages\EditPayslip;
use App\Filament\Resources\Payslips\Pages\ListPayslips;
use App\Filament\Resources\Payslips\Schemas\PayslipForm;
use App\Filament\Resources\Payslips\Tables\PayslipsTable;
use App\Models\Payslip;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class PayslipResource extends Resource
{
    protected static ?string $model = Payslip::class;

    protected static ?string $navigationLabel = 'Holerites';
    protected static ?string $modelLabel = 'Holerite';
    protected static ?string $pluralModelLabel = 'Holerites';

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-document-currency-dollar';
    protected static string|UnitEnum|null $navigationGroup = 'Folha';
    protected static ?int $navigationSort = 5;

    public static function form(Schema $schema): Schema
    {
        return PayslipForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PayslipsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPayslips::route('/'),
            'create' => CreatePayslip::route('/create'),
            'edit' => EditPayslip::route('/{record}/edit'),
        ];
    }
}