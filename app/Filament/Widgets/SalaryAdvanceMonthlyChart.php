<?php

namespace App\Filament\Widgets;

use App\Models\SalaryAdvance;
use Filament\Widgets\ChartWidget;

class SalaryAdvanceMonthlyChart extends ChartWidget
{
    protected ?string $heading = 'Adiantamentos por Mês';


    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected function getData(): array
    {
        $labels = [];
        $values = [];

        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);

            $labels[] = $date->format('m/Y');

            $values[] = (float) SalaryAdvance::query()
                ->whereYear('advance_date', $date->year)
                ->whereMonth('advance_date', $date->month)
                ->sum('amount');
        }

        return [
            'datasets' => [
                [
                    'label' => 'Valor dos Adiantamentos',
                    'data' => $values,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}