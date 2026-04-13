<?php

namespace App\Filament\Widgets;

use App\Models\PayrollRun;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PayrollSummaryStats extends BaseWidget
{
    protected function getStats(): array
    {
        $grossTotal = PayrollRun::query()->sum('total_gross');
        $deductionTotal = PayrollRun::query()->sum('total_discounts');
        $netTotal = PayrollRun::query()->sum('total_net');

        $openRuns = PayrollRun::query()
            ->whereIn('status', ['open', 'processing'])
            ->count();

        return [
            Stat::make('Total Bruto', 'R$ ' . number_format((float) $grossTotal, 2, ',', '.')),
            Stat::make('Total Descontos', 'R$ ' . number_format((float) $deductionTotal, 2, ',', '.')),
            Stat::make('Total Líquido', 'R$ ' . number_format((float) $netTotal, 2, ',', '.')),
            Stat::make('Folhas em Aberto', number_format($openRuns, 0, ',', '.')),
        ];
    }
}