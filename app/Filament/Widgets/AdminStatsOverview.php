<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Employee;
use App\Models\RawMaterialStock;
use App\Models\Sale;
use App\Models\Client;
use App\Models\Emballage;

class AdminStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        // Core counts
        $totalEmployees = Employee::count();
        $totalClients = Client::where('role', 'client')->count();
        $totalSuppliers = Client::where('role', 'supplier')->count();

        // Raw materials
        $totalRawMaterial = RawMaterialStock::sum('quantity_in');
        $totalRejectedRawMaterial = RawMaterialStock::sum('rejected');

        // Sales
        $totalSalesAmount = Sale::sum('total_price');
        $totalSalesQuantity = Sale::sum('quantity');

        // Packaging (emballages)
        $totalPackagingItems = Emballage::count();
        $totalDamagedPackaging = Emballage::sum('damaged');

        return [
            Stat::make('Employees', $totalEmployees)
                ->description('Active staff members')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('success'),

            Stat::make('Clients', $totalClients)
                ->description('Registered buyers')
                ->descriptionIcon('heroicon-m-user')
                ->color('primary'),

            Stat::make('Suppliers', $totalSuppliers)
                ->description('Active suppliers')
                ->descriptionIcon('heroicon-m-truck')
                ->color('primary'),

            Stat::make('Raw Material (kg)', number_format($totalRawMaterial, 2))
                ->description('Total received')
                ->descriptionIcon('heroicon-m-archive-box')
                ->color('info'),

            Stat::make('Rejected Material (kg)', number_format($totalRejectedRawMaterial, 2))
                ->description('Quality rejections')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('danger'),

            Stat::make('Sales Amount (RWF)', number_format($totalSalesAmount, 2))
                ->description('All-time revenue')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('warning'),

            Stat::make('Sales Quantity (kg)', number_format($totalSalesQuantity, 2))
                ->description('Total products sold')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('success'),

            Stat::make('Packaging Items', $totalPackagingItems)
                ->description('Packaging records tracked')
                ->descriptionIcon('heroicon-m-archive-box-arrow-down')
                ->color('secondary'),

            Stat::make('Damaged Packaging', $totalDamagedPackaging)
                ->description('Reported as damaged')
                ->descriptionIcon('heroicon-m-trash')
                ->color('danger'),
        ];
    }
}
