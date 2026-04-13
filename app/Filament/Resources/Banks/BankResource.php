<?php

namespace App\Filament\Resources\Banks;

use App\Filament\Resources\Banks\Pages\CreateBank;
use App\Filament\Resources\Banks\Pages\EditBank;
use App\Filament\Resources\Banks\Pages\ListBanks;
use App\Filament\Resources\Banks\Schemas\BankForm;
use App\Filament\Resources\Banks\Tables\BanksTable;
use App\Models\Bank;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class BankResource extends Resource
{
    protected static ?string $model = Bank::class;

    protected static ?string $navigationLabel = 'Bancos';
    protected static ?string $modelLabel = 'Banco';
    protected static ?string $pluralModelLabel = 'Bancos';

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-building-library';
    protected static string|UnitEnum|null $navigationGroup = 'Cadastros';
    protected static ?int $navigationSort = 11;

    public static function form(Schema $schema): Schema
    {
        return BankForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BanksTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBanks::route('/'),
            'create' => CreateBank::route('/create'),
            'edit' => EditBank::route('/{record}/edit'),
        ];
    }
}