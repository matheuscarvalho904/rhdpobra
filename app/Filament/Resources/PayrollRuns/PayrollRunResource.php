<?php

namespace App\Filament\Resources\PayrollRuns;

use App\Filament\Resources\PayrollRuns\Pages\CreatePayrollRun;
use App\Filament\Resources\PayrollRuns\Pages\EditPayrollRun;
use App\Filament\Resources\PayrollRuns\Pages\ListPayrollRuns;
use App\Filament\Resources\PayrollRuns\Schemas\PayrollRunForm;
use App\Filament\Resources\PayrollRuns\Tables\PayrollRunsTable;
use App\Models\PayrollRun;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class PayrollRunResource extends Resource
{
    protected static ?string $model = PayrollRun::class;

    protected static ?string $navigationLabel = 'Processamentos da Folha';
    protected static ?string $modelLabel = 'Processamento da Folha';
    protected static ?string $pluralModelLabel = 'Processamentos da Folha';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-calculator';
    protected static string|UnitEnum|null $navigationGroup = 'Folha';
    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return PayrollRunForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PayrollRunsTable::configure($table);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return [
            'description',
            'status',
            'run_type',
            'notes',
        ];
    }

    public static function getGlobalSearchResultTitle($record): string
    {
        return (string) ($record->description ?? 'Processamento da Folha');
    }

    public static function getGlobalSearchResultDetails($record): array
    {
        return array_filter([
            'Competência' => $record->payrollCompetency?->display_name,
            'Tipo' => self::runTypeLabels()[$record->run_type ?? ''] ?? $record->run_type,
            'Empresa' => $record->company?->name,
            'Filial' => $record->branch?->name,
            'Obra' => $record->work?->name,
            'Status' => self::statusLabels()[$record->status ?? ''] ?? $record->status,
        ], fn ($value) => filled($value));
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPayrollRuns::route('/'),
            'create' => CreatePayrollRun::route('/create'),
            'edit' => EditPayrollRun::route('/{record}/edit'),
        ];
    }

    protected static function runTypeLabels(): array
    {
        return [
            'payroll_clt' => 'Folha CLT',
            'payroll_apprentice' => 'Folha Aprendiz',
            'internship_payment' => 'Folha Estágio',
            'payroll_rpa' => 'Folha RPA / PF',
            'accounts_payable' => 'Contas a Pagar / PJ',
        ];
    }

    protected static function statusLabels(): array
    {
        return [
            'open' => 'Aberta',
            'processing' => 'Processando',
            'processed' => 'Processada',
            'closed' => 'Fechada',
            'error' => 'Erro',
        ];
    }
}