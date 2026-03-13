<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Sale;
use Carbon\Carbon;

class SalesChart extends ChartWidget
{
    protected static ?string $heading = 'Sales (Last 6 Months)';

    protected function getData(): array
    {
        // Get last 6 months sales data grouped by month
        $salesData = Sale::selectRaw('MONTH(date) as month, SUM(total_price) as total')
            ->where('date', '>=', now()->subMonths(6))
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month')
            ->toArray();

        // Prepare labels and data for the chart
        $labels = [];
        $data = [];

        foreach (range(0, 5) as $i) {
            $month = now()->subMonths(5 - $i)->month;
            $labels[] = Carbon::create()->month($month)->format('M');
            $data[] = $salesData[$month] ?? 0;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Total Sales (RWF)',
                    'data' => $data,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar'; // You could change to 'line' if you prefer
    }
}
