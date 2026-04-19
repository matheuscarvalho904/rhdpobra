<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\EmployeesByDepartmentChart;
use App\Filament\Widgets\EmployeesByWorkTable;
use App\Filament\Widgets\HrStatsOverview;
use App\Filament\Widgets\LatestSalaryAdvancesTable;
use App\Filament\Widgets\PayrollSummaryStats;
use App\Filament\Widgets\RecentEmployeesTable;
use App\Filament\Widgets\SalaryAdvanceMonthlyChart;
use App\Filament\Widgets\SalaryAdvanceStatsOverview;
use App\Filament\Widgets\TopWorksSalaryAdvancesTable;
use BackedEnum;
use Filament\Pages\Page;
use UnitEnum;

class HrDashboard extends Page
{
    protected static ?string $navigationLabel = 'Dashboard RH/DP';
    protected static ?string $title = 'Dashboard RH/DP';
    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-chart-bar-square';
    protected static string|UnitEnum|null $navigationGroup = 'RH & DP';
    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.pages.hr-dashboard';

    public function getHeaderWidgets(): array
    {
        return [
            HrStatsOverview::class,
            PayrollSummaryStats::class,
            EmployeesByWorkTable::class,
            RecentEmployeesTable::class,
            SalaryAdvanceStatsOverview::class,
            SalaryAdvanceMonthlyChart::class,
            LatestSalaryAdvancesTable::class,
        ];
    }
}