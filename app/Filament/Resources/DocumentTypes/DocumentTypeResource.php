<?php

namespace App\Filament\Resources\DocumentTypes;

use App\Filament\Resources\DocumentTypes\Pages\CreateDocumentType;
use App\Filament\Resources\DocumentTypes\Pages\EditDocumentType;
use App\Filament\Resources\DocumentTypes\Pages\ListDocumentTypes;
use App\Filament\Resources\DocumentTypes\Schemas\DocumentTypeForm;
use App\Filament\Resources\DocumentTypes\Tables\DocumentTypesTable;
use App\Models\DocumentType;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class DocumentTypeResource extends Resource
{
    protected static ?string $model = DocumentType::class;

    protected static ?string $navigationLabel = 'Tipos de Documento';
    protected static ?string $modelLabel = 'Tipo de Documento';
    protected static ?string $pluralModelLabel = 'Tipos de Documento';

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-document-text';
    protected static string|UnitEnum|null $navigationGroup = 'Cadastros';
    protected static ?int $navigationSort = 6;

    public static function form(Schema $schema): Schema
    {
        return DocumentTypeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DocumentTypesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDocumentTypes::route('/'),
            'create' => CreateDocumentType::route('/create'),
            'edit' => EditDocumentType::route('/{record}/edit'),
        ];
    }
}