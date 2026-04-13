<?php

namespace App\Filament\Resources\UserAccessScopes;

use App\Filament\Resources\UserAccessScopes\Pages\CreateUserAccessScope;
use App\Filament\Resources\UserAccessScopes\Pages\EditUserAccessScope;
use App\Filament\Resources\UserAccessScopes\Pages\ListUserAccessScopes;
use App\Filament\Resources\UserAccessScopes\Schemas\UserAccessScopeForm;
use App\Filament\Resources\UserAccessScopes\Tables\UserAccessScopesTable;
use App\Models\UserAccessScope;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class UserAccessScopeResource extends Resource
{
    protected static ?string $model = UserAccessScope::class;

    protected static ?string $navigationLabel = 'Escopos de Acesso';
    protected static ?string $modelLabel = 'Escopo de Acesso';
    protected static ?string $pluralModelLabel = 'Escopos de Acesso';

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-shield-check';
    protected static string|UnitEnum|null $navigationGroup = 'Segurança';
    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return UserAccessScopeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UserAccessScopesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUserAccessScopes::route('/'),
            'create' => CreateUserAccessScope::route('/create'),
            'edit' => EditUserAccessScope::route('/{record}/edit'),
        ];
    }
}