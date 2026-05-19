<?php

return [

    'name' => env('APP_NAME', 'Shimirwa IMS'),

    /*
    | Logo shown in sidebar and login (path under /public, or full URL).
    | Default: images/shimirwa-logo.jpg (from WordPress media library).
    */
    'logo' => env('ADMIN_LOGO', 'images/shimirwa-logo.jpg'),

    'brand' => [
        'primary' => '#10498C',
        'secondary' => '#A66B3B',
    ],

    /*
    |--------------------------------------------------------------------------
    | Sidebar navigation (grouped)
    |--------------------------------------------------------------------------
    */
    'navigation' => [
        [
            'group' => 'Overview',
            'items' => [
                ['label' => 'Dashboard', 'route' => 'admin.dashboard', 'icon' => 'home'],
            ],
        ],
        [
            'group' => 'People',
            'items' => [
                ['label' => 'Employees', 'route' => 'admin.employees.index', 'icon' => 'users'],
                ['label' => 'Clients & Suppliers', 'route' => 'admin.clients.index', 'icon' => 'building'],
                ['label' => 'Users', 'route' => 'admin.users.index', 'icon' => 'shield'],
            ],
        ],
        [
            'group' => 'Production',
            'items' => [
                ['label' => 'Reception of materials', 'route' => 'admin.raw-material-stocks.index', 'icon' => 'box'],
                ['label' => 'Roasting', 'route' => 'admin.roastings.index', 'icon' => 'fire'],
                ['label' => 'Sorting', 'route' => 'admin.sortings.index', 'icon' => 'filter'],
                ['label' => 'Milling', 'route' => 'admin.millings.index', 'icon' => 'cog'],
                ['label' => 'Packaging', 'route' => 'admin.emballages.index', 'icon' => 'package'],
            ],
        ],
        [
            'group' => 'Sales',
            'items' => [
                ['label' => 'Sales', 'route' => 'admin.sales.index', 'icon' => 'cart'],
            ],
        ],
    ],

    'stat_icons' => [
        'employees' => 'users',
        'clients' => 'building',
        'suppliers' => 'truck',
        'raw' => 'box',
        'rejected' => 'alert',
        'sales' => 'cart',
        'quantity' => 'chart',
        'packaging' => 'package',
        'damaged' => 'alert',
    ],

];
