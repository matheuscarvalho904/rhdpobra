<?php

namespace App\Filament\Resources\PointIntegrations;

use App\Filament\Resources\PointIntegrations\Pages\CreatePointIntegration;
use App\Filament\Resources\PointIntegrations\Pages\EditPointIntegration;
use App\Filament\Resources\PointIntegrations\Pages\ListPointIntegrations;
use App\Filament\Resources\PointIntegrations\Schemas\PointIntegrationForm;
use App\Filament\Resources\PointIntegrations\Tables\PointIntegrationsTable;
use App\Models\PointIntegration;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class PointIntegrationResource extends Resource
{
    protected static ?string $model = PointIntegration::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-link';

    protected static string|UnitEnum|null $navigationGroup = 'Configurações';

    protected static ?string $navigationLabel = 'Integrações de Ponto';

    protected static ?string $modelLabel = 'Integração de Ponto';

    protected static ?string $pluralModelLabel = 'Integrações de Ponto';

    protected static ?int $navigationSort = 90;

    public static function form(Schema $schema): Schema
    {
        return PointIntegrationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PointIntegrationsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPointIntegrations::route('/'),
            'create' => CreatePointIntegration::route('/create'),
            'edit' => EditPointIntegration::route('/{record}/edit'),
        ];
    }
}