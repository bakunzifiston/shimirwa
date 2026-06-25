@props(['stats' => []])

@php
/* Map icon name → inline SVG path (24×24, stroke-based) */
$icons = [
    'box'     => '<path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/>',
    'scale'   => '<path d="M12 3v18M3 9l9-6 9 6M5 21h14"/><path d="M3 9h4l2 6H3zm14 0h4l-6 6h-4z"/>',
    'calendar'=> '<rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>',
    'trend'   => '<polyline points="22 7 13.5 15.5 8.5 10.5 2 17"/><polyline points="16 7 22 7 22 13"/>',
    'cog'     => '<circle cx="12" cy="12" r="3"/><path d="M19.07 4.93a10 10 0 0 1 0 14.14M4.93 4.93a10 10 0 0 0 0 14.14"/>',
    'fire'    => '<path d="M12 2c0 0-5 5-5 10a7 7 0 0 0 14 0c0-5-5-10-5-10z"/><path d="M12 12c0 0-2 2-2 4a2 2 0 0 0 4 0c0-2-2-4-2-4z"/>',
    'package' => '<path d="M16.5 9.4 7.55 4.24"/><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/>',
    'cart'    => '<circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>',
    'users'   => '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
    'alert'   => '<circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>',
    'list'    => '<line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/>',
    'check'   => '<polyline points="20 6 9 17 4 12"/>',
    'chart'   => '<line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/>',
    'default' => '<circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>',
];
@endphp

@if (!empty($stats))
<div class="page-stats-strip">
    @foreach ($stats as $stat)
        @php
            $iconKey  = $stat['icon'] ?? 'default';
            $iconPath = $icons[$iconKey] ?? $icons['default'];
            $color    = $stat['color'] ?? 'blue';
            $delta    = $stat['delta'] ?? null;
            if ($delta) {
                $deltaDir = str_starts_with($delta, '+') ? 'up' : (str_starts_with($delta, '-') ? 'down' : 'flat');
                $deltaArrow = $deltaDir === 'up' ? '▲' : ($deltaDir === 'down' ? '▼' : '→');
            }
        @endphp
        <div class="page-stat-chip">
            <div class="page-stat-chip-icon page-stat-chip-icon--{{ $color }}">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"
                     stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"
                     aria-hidden="true">{!! $iconPath !!}</svg>
            </div>
            <div class="page-stat-chip-body">
                <p class="page-stat-chip-label">{{ $stat['label'] }}</p>
                <p class="page-stat-chip-value">{{ $stat['value'] }}</p>
                @if ($delta)
                    <p class="page-stat-chip-delta page-stat-chip-delta--{{ $deltaDir }}">
                        {{ $deltaArrow }} {{ $delta }} vs last month
                    </p>
                @endif
            </div>
        </div>
    @endforeach
</div>
@endif
