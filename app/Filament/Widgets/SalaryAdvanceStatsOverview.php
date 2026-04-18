<?php

namespace App\Filament\Widgets;

use App\Models\SalaryAdvance;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SalaryAdvanceStatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $start = now()->startOfMonth();
        $end = now()->endOfMonth();

        $baseQuery = SalaryAdvance::query()
            ->whereBetween('advance_date', [$start->toDateString(), $end->toDateString()]);

        $totalMonth = (clone $baseQuery)->sum('amount');
        $totalPaid = (clone $baseQuery)->where('status', 'paid')->sum('amount');
        $totalDraft = (clone $baseQuery)->where('status', 'draft')->sum('amount');
        $totalCanceled = (clone $baseQuery)->where('status', 'canceled')->sum('amount');

        return [
            Stat::make('Total no mês', 'R$ ' . number_format((float) $totalMonth, 2, ',', '.'))
                ->description('Adiantamentos do mês atual'),

            Stat::make('Pagos', 'R$ ' . number_format((float) $totalPaid, 2, ',', '.'))
                ->description('Total já pago')
                ->color('success'),

            Stat::make('Em rascunho', 'R$ ' . number_format((float) $totalDraft, 2, ',', '.'))
                ->description('Aguardando processamento')
                ->color('warning'),

            Stat::make('Cancelados', 'R$ ' . number_format((float) $totalCanceled, 2, ',', '.'))
                ->description('Total cancelado')
                ->color('danger'),
        ];
    }
}