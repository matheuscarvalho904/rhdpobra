<?php

namespace App\Filament\Resources\JobRoles;

use App\Filament\Resources\JobRoles\Schemas\JobRoleForm;
use App\Filament\Resources\JobRoles\Tables\JobRolesTable;
use App\Models\JobRole;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class JobRoleResource extends Resource
{
    protected static ?string $model = JobRole::class;

    protected static ?string $navigationLabel = 'Cargos';
    protected static ?string $modelLabel = 'Cargo';
    protected static ?string $pluralModelLabel = 'Cargos';

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-briefcase';
    protected static string|UnitEnum|null $navigationGroup = 'Cadastros';
    protected static ?int $navigationSort = 7;

    public static function form(Schema $schema): Schema
    {
        return JobRoleForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return JobRolesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListJobRoles::route('/'),
            'create' => Pages\CreateJobRole::route('/create'),
            'edit' => Pages\EditJobRole::route('/{record}/edit'),
        ];
    }
}