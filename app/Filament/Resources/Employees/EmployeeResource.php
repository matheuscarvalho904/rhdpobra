<?php

namespace App\Filament\Resources\Employees;

use App\Filament\Resources\Employees\Pages\CreateEmployee;
use App\Filament\Resources\Employees\Pages\EditEmployee;
use App\Filament\Resources\Employees\Pages\ListEmployees;
use App\Filament\Resources\Employees\Schemas\EmployeeForm;
use App\Filament\Resources\Employees\Tables\EmployeesTable;
use App\Models\Employee;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class EmployeeResource extends Resource
{
    protected static ?string $model = Employee::class;

    protected static ?string $navigationLabel = 'Colaboradores';
    protected static ?string $modelLabel = 'Colaborador';
    protected static ?string $pluralModelLabel = 'Colaboradores';

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-identification'; 
    protected static string|UnitEnum|null $navigationGroup = 'RH';
    protected static ?int $navigationSort = 1;

    // 🔥 FORM
    public static function form(Schema $schema): Schema
    {
        return EmployeeForm::configure($schema);
    }

    // 🔥 TABLE
    public static function table(Table $table): Table
    {
        return EmployeesTable::configure($table);
    }

    // 🔎 BUSCA GLOBAL (ESSENCIAL PRA ERP)
    public static function getGloballySearchableAttributes(): array
    {
        return [
            'name',
            'cpf',
            'code',
            'email',
            'mobile',
        ];
    }

    public static function getGlobalSearchResultTitle($record): string
    {
        return $record->name ?? 'Colaborador';
    }

    public static function getGlobalSearchResultDetails($record): array
    {
        return array_filter([
            'CPF' => $record->cpf,
            'Empresa' => $record->company?->name,
            'Obra' => $record->work?->name,
            'Cargo' => $record->jobRole?->name,
        ]);
    }

    // 📄 PÁGINAS
    public static function getPages(): array
    {
        return [
            'index' => ListEmployees::route('/'),
            'create' => CreateEmployee::route('/create'),
            'edit' => EditEmployee::route('/{record}/edit'),
        ];
    }
    public static function getRelations(): array
{
    return [
        \App\Filament\Resources\Employees\RelationManagers\FilesRelationManager::class,
    ];
}
}