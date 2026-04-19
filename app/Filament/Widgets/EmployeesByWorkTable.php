<?php

namespace App\Filament\Widgets;

use App\Models\Employee;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class EmployeesByWorkTable extends BaseWidget
{
    protected static ?string $heading = 'Quantidade de Ativos por Obra';

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns([
                Tables\Columns\TextColumn::make('work_name')
                    ->label('Obra')
                    ->searchable(),

                Tables\Columns\TextColumn::make('total_active')
                    ->label('Qtd. Ativos')
                    ->sortable(),
            ])
            ->defaultSort('total_active', 'desc')
            ->paginated([5, 10, 25]);
    }

    protected function getTableQuery(): Builder
    {
        return Employee::query()
            ->leftJoin('works', 'works.id', '=', 'employees.work_id')
            ->selectRaw('COALESCE(employees.work_id, 0) as record_key')
            ->selectRaw("COALESCE(works.name, 'Sem Obra') as work_name")
            ->selectRaw('COUNT(employees.id) as total_active')
            ->where('employees.is_active', true)
            ->where('employees.status', 'active')
            ->whereNull('employees.deleted_at')
            ->groupByRaw("COALESCE(employees.work_id, 0), COALESCE(works.name, 'Sem Obra')");
    }

    public function getTableRecordKey(mixed $record): string
    {
        return (string) data_get($record, 'record_key');
    }
}