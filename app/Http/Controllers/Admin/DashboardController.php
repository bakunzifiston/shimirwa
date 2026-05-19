<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Emballage;
use App\Models\Employee;
use App\Models\RawMaterialStock;
use App\Models\Sale;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $iconMap = config('admin.stat_icons', []);

        $stats = [
            [
                'key' => 'employees',
                'label' => 'Employees',
                'value' => number_format(Employee::count()),
                'hint' => 'Active staff members',
                'tone' => 'primary',
            ],
            [
                'key' => 'clients',
                'label' => 'Clients',
                'value' => number_format(Client::where('role', 'client')->count()),
                'hint' => 'Registered buyers',
                'tone' => 'secondary',
            ],
            [
                'key' => 'suppliers',
                'label' => 'Suppliers',
                'value' => number_format(Client::where('role', 'supplier')->count()),
                'hint' => 'Active suppliers',
                'tone' => 'secondary',
            ],
            [
                'key' => 'raw',
                'label' => 'Raw material',
                'value' => number_format((float) RawMaterialStock::sum('quantity_in'), 1) . ' kg',
                'hint' => 'Total in stock',
                'tone' => 'primary',
            ],
            [
                'key' => 'rejected',
                'label' => 'Rejected material',
                'value' => number_format((float) RawMaterialStock::sum('rejected'), 1) . ' kg',
                'hint' => 'Quality rejections',
                'tone' => 'warning',
            ],
            [
                'key' => 'sales',
                'label' => 'Revenue',
                'value' => number_format((float) Sale::sum('total_price'), 0) . ' RWF',
                'hint' => 'All-time sales',
                'tone' => 'success',
            ],
            [
                'key' => 'quantity',
                'label' => 'Units sold',
                'value' => number_format((float) Sale::sum('quantity'), 0),
                'hint' => 'Total quantity',
                'tone' => 'primary',
            ],
            [
                'key' => 'packaging',
                'label' => 'Packaging runs',
                'value' => number_format(Emballage::count()),
                'hint' => 'Emballage entries',
                'tone' => 'secondary',
            ],
            [
                'key' => 'damaged',
                'label' => 'Damaged units',
                'value' => number_format((float) Emballage::sum('damaged'), 0),
                'hint' => 'Reported damaged',
                'tone' => 'warning',
            ],
        ];

        foreach ($stats as &$stat) {
            $stat['icon'] = $iconMap[$stat['key']] ?? 'chart';
        }
        unset($stat);

        $recentSales = Sale::with(['client', 'employee'])
            ->latest('date')
            ->latest('id')
            ->limit(6)
            ->get();

        $recentStock = RawMaterialStock::with('client')
            ->latest('date')
            ->latest('id')
            ->limit(6)
            ->get();

        return view('admin.dashboard', compact('stats', 'recentSales', 'recentStock'));
    }
}
