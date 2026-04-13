<?php

namespace App\Filament\Widgets;

use App\Models\Employee;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class HrStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $totalEmployees = Employee::query()->count();

        $activeEmployees = Employee::query()
            ->where('is_active', true)
            ->where('status', 'active')
            ->count();

        $inactiveEmployees = Employee::query()
            ->where(function ($query) {
                $query->where('is_active', false)
                    ->orWhere('status', '!=', 'active');
            })
            ->count();

        $admittedThisMonth = Employee::query()
            ->whereMonth('admission_date', now()->month)
            ->whereYear('admission_date', now()->year)
            ->count();

        return [
            Stat::make('Total de Colaboradores', number_format($totalEmployees, 0, ',', '.')),
            Stat::make('Ativos', number_format($activeEmployees, 0, ',', '.')),
            Stat::make('Inativos', number_format($inactiveEmployees, 0, ',', '.')),
            Stat::make('Admitidos no Mês', number_format($admittedThisMonth, 0, ',', '.')),
        ];
    }
}