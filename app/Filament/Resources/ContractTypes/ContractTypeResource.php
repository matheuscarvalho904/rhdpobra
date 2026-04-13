<?php

namespace App\Filament\Resources\ContractTypes;

use App\Filament\Resources\ContractTypes\Pages\CreateContractType;
use App\Filament\Resources\ContractTypes\Pages\EditContractType;
use App\Filament\Resources\ContractTypes\Pages\ListContractTypes;
use App\Filament\Resources\ContractTypes\Schemas\ContractTypeForm;
use App\Filament\Resources\ContractTypes\Tables\ContractTypesTable;
use App\Models\ContractType;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class ContractTypeResource extends Resource
{
    protected static ?string $model = ContractType::class;

    protected static ?string $navigationLabel = 'Tipos de Contrato';
    protected static ?string $modelLabel = 'Tipo de Contrato';
    protected static ?string $pluralModelLabel = 'Tipos de Contrato';

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-document-duplicate';
    protected static string|UnitEnum|null $navigationGroup = 'Cadastros';
    protected static ?int $navigationSort = 10;

    public static function form(Schema $schema): Schema
    {
        return ContractTypeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ContractTypesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListContractTypes::route('/'),
            'create' => CreateContractType::route('/create'),
            'edit' => EditContractType::route('/{record}/edit'),
        ];
    }
}