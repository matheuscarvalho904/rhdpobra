<?php

namespace App\Filament\Widgets;

use App\Models\Employee;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

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
            ->select([
                DB::raw('COALESCE(employees.work_id, 0) as record_key'),
                'employees.work_id',
                DB::raw('COALESCE(works.name, "Sem Obra") as work_name'),
                DB::raw('COUNT(employees.id) as total_active'),
            ])
            ->where('employees.is_active', true)
            ->where('employees.status', 'active')
            ->groupBy('employees.work_id', 'works.name');
    }

    public function getTableRecordKey(mixed $record): string
    {
        return (string) data_get($record, 'record_key');
    }
}