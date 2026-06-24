<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Emballage;
use App\Models\Employee;
use App\Models\Order;
use App\Models\Product;
use App\Models\RawMaterialStock;
use App\Models\Sale;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $iconMap = config('admin.stat_icons', []);

        $primaryStats = [
            [
                'key' => 'sales',
                'label' => 'Total revenue',
                'value' => number_format((float) Sale::sum('total_price'), 0).' RWF',
                'tone' => 'success',
            ],
            [
                'key' => 'raw',
                'label' => 'Raw material in stock',
                'value' => number_format((float) RawMaterialStock::rawMaterialKg()->sum('quantity_in'), 1).' kg',
                'tone' => 'primary',
            ],
            [
                'key' => 'orders',
                'label' => 'Shop orders',
                'value' => number_format(Order::count()),
                'tone' => 'secondary',
            ],
            [
                'key' => 'products',
                'label' => 'Active products',
                'value' => number_format(Product::query()->where('status', Product::STATUS_ACTIVE)->count()),
                'tone' => 'primary',
            ],
        ];

        $secondaryStats = [
            ['key' => 'employees', 'label' => 'Employees', 'value' => number_format(Employee::count())],
            ['key' => 'clients', 'label' => 'Clients', 'value' => number_format(Client::where('role', 'client')->count())],
            ['key' => 'suppliers', 'label' => 'Suppliers', 'value' => number_format(Client::where('role', 'supplier')->count())],
            ['key' => 'quantity', 'label' => 'Units sold', 'value' => number_format((float) Sale::sum('quantity'), 0)],
            ['key' => 'rejected', 'label' => 'Rejected (raw material)', 'value' => number_format((float) RawMaterialStock::rawMaterialKg()->sum('rejected'), 1).' kg'],
            ['key' => 'damaged', 'label' => 'Damaged', 'value' => number_format((float) Emballage::sum('damaged'), 0)],
        ];

        foreach ($primaryStats as &$stat) {
            $stat['icon'] = $iconMap[$stat['key']] ?? 'chart';
        }
        unset($stat);

        $recentSales = Sale::with('client')
            ->latest('date')
            ->latest('id')
            ->limit(5)
            ->get();

        $recentStock = RawMaterialStock::with('client')
            ->latest('date')
            ->latest('id')
            ->limit(5)
            ->get();

        $recentOrders = Order::with('customer')
            ->latest()
            ->limit(5)
            ->get();

        $chartData = [
            'bar' => $this->monthlyRevenueChart(),
            'pie' => $this->rawMaterialPieChart(),
        ];

        return view('admin.dashboard', compact(
            'primaryStats',
            'secondaryStats',
            'recentSales',
            'recentStock',
            'recentOrders',
            'chartData',
        ));
    }

    /**
     * @return array{labels: list<string>, datasets: list<array{label: string, data: list<float>}>}
     */
    private function monthlyRevenueChart(): array
    {
        $labels = [];
        $salesData = [];
        $ordersData = [];

        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $labels[] = $month->format('M');

            $salesData[] = (float) Sale::query()
                ->whereYear('date', $month->year)
                ->whereMonth('date', $month->month)
                ->sum('total_price');

            $ordersData[] = (float) Order::query()
                ->whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->where('payment_status', '!=', Order::PAYMENT_CANCELLED)
                ->sum('total');
        }

        return [
            'labels' => $labels,
            'datasets' => [
                ['label' => 'IMS sales', 'data' => $salesData],
                ['label' => 'Shop orders', 'data' => $ordersData],
            ],
        ];
    }

    /**
     * @return array{labels: list<string>, data: list<float>}
     */
    private function rawMaterialPieChart(): array
    {
        $rows = RawMaterialStock::query()
            ->rawMaterialKg()
            ->selectRaw('item, SUM(CASE WHEN (received - rejected) > 0 THEN (received - rejected) ELSE 0 END) as total')
            ->groupBy('item')
            ->orderByDesc('total')
            ->limit(6)
            ->get();

        return [
            'labels' => $rows->pluck('item')->all(),
            'data' => $rows->pluck('total')->map(fn ($value) => (float) $value)->all(),
        ];
    }
}
