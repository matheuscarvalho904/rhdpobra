<?php

namespace App\Filament\Widgets;

use App\Models\Employee;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class EmployeesByDepartmentChart extends ChartWidget
{
    protected static ?string $heading = 'Colaboradores por Departamento';

    protected function getData(): array
    {
        $data = Employee::query()
            ->selectRaw("
                COALESCE(departments.name, 'Sem Departamento') as department_name,
                COUNT(employees.id) as total
            ")
            ->leftJoin('departments', 'departments.id', '=', 'employees.department_id')
            ->where('employees.is_active', true)
            ->where('employees.status', 'active')
            ->whereNull('employees.deleted_at')
            ->groupBy('departments.name')
            ->orderByDesc('total')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Colaboradores',
                    'data' => $data->pluck('total')->toArray(),
                ],
            ],
            'labels' => $data->pluck('department_name')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}