<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Shimirwa Ltd — Monthly Report — {{ $from->format('F Y') }}</title>
    <style>
        @page { margin: 28px 32px; }
        body { font-family: Helvetica, Arial, sans-serif; font-size: 11px; color: #1a1a1a; }
        .header { width: 100%; margin-bottom: 18px; }
        .header td { border: none; padding: 0; vertical-align: middle; }
        .header .logo-cell { width: 52px; }
        .header img.logo { width: 44px; height: 44px; object-fit: contain; }
        h1 { font-size: 18px; margin: 0 0 2px; }
        .subtitle { font-size: 12px; color: #666; margin: 0; }
        h2 {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 18px 0 6px;
            padding-bottom: 4px;
            border-bottom: 1.5px solid #222;
        }
        table { width: 100%; border-collapse: collapse; margin-bottom: 4px; }
        th, td { padding: 4px 6px; border-bottom: 1px solid #ddd; text-align: left; }
        th { background: #f2f2f2; font-size: 10px; text-transform: uppercase; color: #444; }
        td.num, th.num { text-align: right; font-variant-numeric: tabular-nums; }
        tr.total td { font-weight: bold; border-top: 1.5px solid #222; border-bottom: none; }
        .cards { width: 100%; margin-bottom: 4px; }
        .cards td { border: 1px solid #ddd; padding: 8px 10px; width: 25%; }
        .cards .label { font-size: 9px; text-transform: uppercase; color: #777; margin-bottom: 2px; }
        .cards .value { font-size: 15px; font-weight: bold; }
        .empty { color: #999; font-style: italic; padding: 6px; }
        .footer-note { margin-top: 20px; font-size: 9px; color: #999; }
    </style>
</head>
<body>
    <table class="header">
        <tr>
            @if ($logoDataUri)
            <td class="logo-cell"><img class="logo" src="{{ $logoDataUri }}" alt="Shimirwa Ltd logo"></td>
            @endif
            <td>
                <h1>Shimirwa Ltd — Monthly Report</h1>
                <p class="subtitle">{{ $from->format('F Y') }} &nbsp;({{ $from->format('d M Y') }} – {{ $to->format('d M Y') }}) &nbsp;·&nbsp; Generated {{ now()->format('d M Y, H:i') }}</p>
            </td>
        </tr>
    </table>

    <table class="cards">
        <tr>
            <td>
                <div class="label">Raw material received</div>
                <div class="value">{{ number_format($monthly['rawMaterialsTotals']['received'], 1) }} kg</div>
            </td>
            <td>
                <div class="label">Flour milled</div>
                <div class="value">{{ number_format($monthly['production']['milling']['output'], 1) }} kg</div>
            </td>
            <td>
                <div class="label">Units packaged</div>
                <div class="value">{{ number_format($monthly['packagingTotals']['units']) }}</div>
            </td>
            <td>
                <div class="label">Sales revenue</div>
                <div class="value">{{ number_format($monthly['salesTotals']['revenue'], 0) }} RWF</div>
            </td>
        </tr>
    </table>

    <h2>Raw Materials Received</h2>
    @if ($monthly['rawMaterials']->isEmpty())
        <div class="empty">No raw material intake recorded this month.</div>
    @else
        <table>
            <thead>
                <tr><th>Item</th><th>Type</th><th class="num">Received (kg)</th><th class="num">Rejected (kg)</th><th class="num">Batches</th></tr>
            </thead>
            <tbody>
                @foreach ($monthly['rawMaterials'] as $row)
                <tr>
                    <td>{{ $row->item }}</td>
                    <td>{{ $row->type }}</td>
                    <td class="num">{{ number_format($row->received, 1) }}</td>
                    <td class="num">{{ number_format($row->rejected, 1) }}</td>
                    <td class="num">{{ $row->batches }}</td>
                </tr>
                @endforeach
                <tr class="total">
                    <td colspan="2">Total</td>
                    <td class="num">{{ number_format($monthly['rawMaterialsTotals']['received'], 1) }}</td>
                    <td class="num">{{ number_format($monthly['rawMaterialsTotals']['rejected'], 1) }}</td>
                    <td class="num">{{ $monthly['rawMaterialsTotals']['batches'] }}</td>
                </tr>
            </tbody>
        </table>
    @endif

    <h2>Production Pipeline</h2>
    @foreach (['sorting' => 'Sorting', 'roasting' => 'Roasting', 'milling' => 'Milling (flour)'] as $key => $label)
    @php($rows = $monthly['productionByItem'][$key]['rows'])
    @php($t = $monthly['productionByItem'][$key]['totals'])
    <table>
        <thead>
            <tr><th colspan="5">{{ $label }}</th></tr>
            <tr><th>Item</th><th class="num">Input (kg)</th><th class="num">Loss (kg)</th><th class="num">Output (kg)</th><th class="num">Batches</th></tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
            <tr>
                <td>{{ $row['item'] }}</td>
                <td class="num">{{ number_format($row['input'], 1) }}</td>
                <td class="num">{{ number_format($row['loss'], 1) }}</td>
                <td class="num">{{ number_format($row['output'], 1) }}</td>
                <td class="num">{{ $row['batches'] }}</td>
            </tr>
            @empty
            <tr><td colspan="5" class="empty">No {{ strtolower($label) }} recorded this month.</td></tr>
            @endforelse
            @if ($rows->count() > 1)
            <tr class="total">
                <td>Total</td>
                <td class="num">{{ number_format($t['input'], 1) }}</td>
                <td class="num">{{ number_format($t['loss'], 1) }}</td>
                <td class="num">{{ number_format($t['output'], 1) }}</td>
                <td class="num">{{ $t['batches'] }}</td>
            </tr>
            @endif
        </tbody>
    </table>
    @endforeach

    <h2>Packaging</h2>
    @if ($monthly['packagingByProduct']->isEmpty())
        <div class="empty">No packaging recorded this month.</div>
    @else
        <table>
            <thead>
                <tr><th>Product</th><th class="num">Units Packed</th><th class="num">Kg Packed</th><th class="num">Damaged</th><th class="num">Batches</th></tr>
            </thead>
            <tbody>
                @foreach ($monthly['packagingByProduct'] as $row)
                <tr>
                    <td>{{ $row['name'] }}</td>
                    <td class="num">{{ number_format($row['units']) }}</td>
                    <td class="num">{{ number_format($row['kg'], 1) }}</td>
                    <td class="num">{{ $row['damaged'] }}</td>
                    <td class="num">{{ $row['batches'] }}</td>
                </tr>
                @endforeach
                <tr class="total">
                    <td>Total</td>
                    <td class="num">{{ number_format($monthly['packagingTotals']['units']) }}</td>
                    <td class="num">{{ number_format($monthly['packagingTotals']['kg'], 1) }}</td>
                    <td class="num">{{ $monthly['packagingTotals']['damaged'] }}</td>
                    <td></td>
                </tr>
            </tbody>
        </table>
    @endif

    <h2>Sales</h2>
    @if ($monthly['salesByProduct']->isEmpty())
        <div class="empty">No sales recorded this month.</div>
    @else
        <table>
            <thead>
                <tr><th>Product</th><th class="num">Units Sold</th><th class="num">Revenue (RWF)</th><th class="num">Returned</th><th class="num">Transactions</th></tr>
            </thead>
            <tbody>
                @foreach ($monthly['salesByProduct'] as $row)
                <tr>
                    <td>{{ $row->item }}</td>
                    <td class="num">{{ number_format($row->units) }}</td>
                    <td class="num">{{ number_format($row->revenue, 0) }}</td>
                    <td class="num">{{ number_format($row->returned) }}</td>
                    <td class="num">{{ $row->txns }}</td>
                </tr>
                @endforeach
                <tr class="total">
                    <td>Total</td>
                    <td class="num">{{ number_format($monthly['salesTotals']['units']) }}</td>
                    <td class="num">{{ number_format($monthly['salesTotals']['revenue'], 0) }}</td>
                    <td class="num">{{ number_format($monthly['salesTotals']['returned']) }}</td>
                    <td></td>
                </tr>
            </tbody>
        </table>
    @endif

    @if ($monthly['topClients']->isNotEmpty())
    <h2>Top Clients</h2>
    <table>
        <thead>
            <tr><th>Client</th><th class="num">Units</th><th class="num">Revenue (RWF)</th></tr>
        </thead>
        <tbody>
            @foreach ($monthly['topClients'] as $row)
            <tr>
                <td>{{ $row['name'] }}</td>
                <td class="num">{{ number_format($row['units']) }}</td>
                <td class="num">{{ number_format($row['revenue'], 0) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    <h2>Online Shop Orders</h2>
    @if ($monthly['shopOrdersByStatus']->isEmpty())
        <div class="empty">No online orders recorded this month.</div>
    @else
        <table>
            <thead>
                <tr><th>Status</th><th class="num">Orders</th><th class="num">Revenue (RWF)</th></tr>
            </thead>
            <tbody>
                @foreach ($monthly['shopOrdersByStatus'] as $row)
                <tr>
                    <td>{{ ucfirst($row->order_status) }}</td>
                    <td class="num">{{ $row->count }}</td>
                    <td class="num">{{ number_format($row->revenue ?? 0, 0) }}</td>
                </tr>
                @endforeach
                <tr class="total">
                    <td>Total ({{ $monthly['shopOrderTotals']['orders'] }} orders)</td>
                    <td></td>
                    <td class="num">{{ number_format($monthly['shopOrderTotals']['revenue'], 0) }} paid</td>
                </tr>
            </tbody>
        </table>
    @endif

    <h2>Stock Levels <span style="font-weight:normal;text-transform:none;">(as of {{ $to->format('d M Y') }})</span></h2>
    <table>
        <thead>
            <tr><th>Raw material</th><th class="num">Remaining (kg)</th></tr>
        </thead>
        <tbody>
            @forelse ($monthly['rawStockAsOf'] as $row)
            <tr>
                <td>{{ $row->item }}</td>
                <td class="num">{{ number_format($row->remaining, 1) }}</td>
            </tr>
            @empty
            <tr><td colspan="2" class="empty">No raw material in stock.</td></tr>
            @endforelse
            <tr>
                <td>Unpackaged milled flour</td>
                <td class="num">{{ number_format($monthly['flourStockAsOf'], 1) }}</td>
            </tr>
        </tbody>
    </table>

    <table>
        <thead>
            <tr><th>Packaged product</th><th class="num">Units in stock</th></tr>
        </thead>
        <tbody>
            @forelse ($monthly['packagedStockAsOf'] as $row)
            <tr>
                <td>{{ $row['name'] }}</td>
                <td class="num">{{ number_format($row['units']) }}</td>
            </tr>
            @empty
            <tr><td colspan="2" class="empty">No packaged product in stock.</td></tr>
            @endforelse
        </tbody>
    </table>

    <p class="footer-note">Shimirwa Ltd — Monthly Report for {{ $from->format('F Y') }}. Generated automatically from system records on {{ now()->format('d M Y, H:i') }}.</p>
</body>
</html>
