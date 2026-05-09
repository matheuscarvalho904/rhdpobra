<?php

namespace App\Filament\Widgets;

use App\Models\TimeBank;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TimeBankOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 5;

    protected function getStats(): array
    {
        $positive = (float) TimeBank::query()
            ->sum('positive_balance_hours');

        $negative = (float) TimeBank::query()
            ->sum('negative_balance_hours');

        $net = (float) TimeBank::query()
            ->sum('net_balance_hours');

        $employeesPositive = TimeBank::query()
            ->where('net_balance_hours', '>', 0)
            ->count();

        $employeesNegative = TimeBank::query()
            ->where('net_balance_hours', '<', 0)
            ->count();

        return [
            Stat::make(
                'Total Positivo',
                number_format($positive, 2, ',', '.') . ' h'
            )
                ->description('Horas positivas acumuladas')
                ->color('success'),

            Stat::make(
                'Total Negativo',
                number_format($negative, 2, ',', '.') . ' h'
            )
                ->description('Horas negativas acumuladas')
                ->color('danger'),

            Stat::make(
                'Saldo Geral',
                number_format($net, 2, ',', '.') . ' h'
            )
                ->description('Saldo consolidado')
                ->color(
                    $net >= 0
                        ? 'success'
                        : 'danger'
                ),

            Stat::make(
                'Colaboradores Positivos',
                $employeesPositive
            )
                ->description('Com saldo positivo')
                ->color('success'),

            Stat::make(
                'Colaboradores Negativos',
                $employeesNegative
            )
                ->description('Com saldo negativo')
                ->color('danger'),
        ];
    }
}