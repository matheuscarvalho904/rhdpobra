<?php

namespace App\Filament\Widgets;

use App\Models\Employee;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class EmployeesByDepartmentChart extends ChartWidget
{
    protected static ?string $heading = 'Funcionários por Departamento';

    protected function getData(): array
    {
        $rows = Employee::query()
            ->select([
                DB::raw("COALESCE(departments.name, 'Sem Departamento') as department_name"),
                DB::raw('COUNT(employees.id) as total'),
            ])
            ->leftJoin('departments', 'departments.id', '=', 'employees.department_id')
            ->where('employees.is_active', true)
            ->where('employees.status', 'active')
            ->whereNull('employees.deleted_at')
            ->groupBy('departments.id', 'departments.name')
            ->orderByDesc('total')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Ativos',
                    'data' => $rows->pluck('total')->map(fn ($value) => (int) $value)->all(),
                ],
            ],
            'labels' => $rows->pluck('department_name')->all(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}