<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\RawMaterialStock;
use Carbon\Carbon;

class StockChart extends ChartWidget
{
    protected static ?string $heading = 'Raw Material Stock (Monthly)';

    protected function getData(): array
    {
        // Get last 6 months stock data grouped by month
        $stockData = RawMaterialStock::selectRaw('MONTH(date) as month, SUM(quantity_in) as total')
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
            $data[] = $stockData[$month] ?? 0;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Stock Received (kg)',
                    'data' => $data,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar'; // can also be 'line' if you prefer
    }
}
