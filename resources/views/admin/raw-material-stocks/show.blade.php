@extends('layouts.admin')

@section('title', 'Batch ' . $stock->batch_number)
@section('page_title', 'Reception — ' . $stock->item)
@section('page_subtitle', 'Batch ' . $stock->batch_number)

@section('content')

{{-- Action bar --}}
<div class="flex justify-end mb-4">
    <a href="{{ route('admin.raw-material-stocks.edit', $stock) }}"
       data-drawer-src="{{ route('admin.raw-material-stocks.edit', $stock) }}"
       data-drawer-title="Edit reception"
       class="admin-btn admin-btn-primary admin-btn-sm">Edit</a>
</div>

{{-- Batch info --}}
<div class="admin-card p-5 mb-4">
    <div class="grid grid-cols-2 gap-3 text-sm sm:grid-cols-3">
        <div>
            <div class="text-xs mb-0.5" style="color:var(--admin-text-subtle)">Date</div>
            <div class="font-medium">{{ $stock->date?->format('d M Y') }}</div>
        </div>
        <div>
            <div class="text-xs mb-0.5" style="color:var(--admin-text-subtle)">Item</div>
            <div class="font-medium">{{ $stock->item }}</div>
        </div>
        <div>
            <div class="text-xs mb-0.5" style="color:var(--admin-text-subtle)">Batch #</div>
            <div class="font-medium font-mono">{{ $stock->batch_number }}</div>
        </div>
        <div>
            <div class="text-xs mb-0.5" style="color:var(--admin-text-subtle)">Supplier</div>
            <div class="font-medium">{{ $stock->client?->full_name ?? '—' }}</div>
        </div>
        <div>
            <div class="text-xs mb-0.5" style="color:var(--admin-text-subtle)">Received by</div>
            <div class="font-medium">{{ $stock->employee?->full_name ?? '—' }}</div>
        </div>
        <div>
            <div class="text-xs mb-0.5" style="color:var(--admin-text-subtle)">Type</div>
            <div class="font-medium">{{ $stock->type ?? '—' }}</div>
        </div>
    </div>
    @if ($stock->comment)
        <div class="mt-3 pt-3 border-t text-sm" style="border-color:var(--admin-border)">
            <span style="color:var(--admin-text-subtle)">Comment: </span>{{ $stock->comment }}
        </div>
    @endif
</div>

{{-- Stock quantities --}}
<div class="grid grid-cols-3 gap-3 mb-4">
    <div class="admin-card p-4 text-center">
        <div class="text-xl font-bold">{{ number_format($stock->received, 1) }}</div>
        <div class="text-xs mt-0.5" style="color:var(--admin-text-subtle)">Received (kg)</div>
    </div>
    <div class="admin-card p-4 text-center">
        <div class="text-xl font-bold text-red-500">{{ number_format($stock->rejected, 1) }}</div>
        <div class="text-xs mt-0.5" style="color:var(--admin-text-subtle)">Rejected (kg)</div>
    </div>
    <div class="admin-card p-4 text-center" style="border-left:3px solid var(--admin-primary, #10498C)">
        <div class="text-xl font-bold db-revenue-today">{{ number_format($stock->quantity_in, 1) }}</div>
        <div class="text-xs mt-0.5" style="color:var(--admin-text-subtle)">Remaining (kg)</div>
    </div>
</div>

{{-- Sorting history --}}
<div class="admin-card overflow-hidden mb-4">
    <div class="flex items-center justify-between px-4 py-2.5 border-b" style="border-color:var(--admin-border);background:var(--admin-bg)">
        <div class="flex items-center gap-2 text-sm font-semibold">
            <svg width="14" height="14" fill="none" stroke="#2563eb" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><polyline points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
            Sorting operations
        </div>
        <span class="admin-badge admin-badge--primary">{{ $stock->sortings->count() }}</span>
    </div>

    @if ($stock->sortings->isEmpty())
        <div class="px-4 py-6 text-center text-sm" style="color:var(--admin-text-subtle)">No sorting recorded.</div>
    @else
        @foreach ($stock->sortings as $s)
            <div class="px-4 py-3 border-b last:border-0 text-sm" style="border-color:var(--admin-border)">
                <div class="flex items-center justify-between mb-1">
                    <span class="font-medium">{{ $s->date?->format('d M Y') }}</span>
                    <a href="{{ route('admin.sortings.show', $s) }}" class="text-xs font-medium" style="color:#2563eb">View →</a>
                </div>
                <div class="flex gap-4 text-xs" style="color:var(--admin-text-subtle)">
                    <span>In: <strong class="font-semibold" style="color:var(--admin-text)">{{ number_format($s->quantity_in, 1) }} kg</strong></span>
                    <span>Loss: <strong class="text-red-500">{{ number_format($s->loss ?? 0, 1) }} kg</strong></span>
                    <span>Out: <strong class="text-green-600">{{ number_format($s->quantity_out, 1) }} kg</strong></span>
                </div>
                @if ($s->employee)
                    <div class="text-xs mt-1" style="color:var(--admin-text-subtle)">{{ $s->employee->full_name }}</div>
                @endif
            </div>
        @endforeach
        <div class="px-4 py-2 text-xs flex justify-between" style="background:var(--admin-bg);color:var(--admin-text-subtle)">
            <span>Total in: <strong style="color:var(--admin-text)">{{ number_format($stock->sortings->sum('quantity_in'), 1) }} kg</strong></span>
            <span>Total out: <strong class="text-green-600">{{ number_format($stock->sortings->sum('quantity_out'), 1) }} kg</strong></span>
        </div>
    @endif
</div>

{{-- Direct roasting history --}}
@php $directRoastings = $stock->roastings; @endphp
<div class="admin-card overflow-hidden mb-4">
    <div class="flex items-center justify-between px-4 py-2.5 border-b" style="border-color:var(--admin-border);background:var(--admin-bg)">
        <div class="flex items-center gap-2 text-sm font-semibold">
            <svg width="14" height="14" fill="none" stroke="#ea580c" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 0 0 2.5 2.5z"/></svg>
            Direct roasting <span class="font-normal text-xs ml-1" style="color:var(--admin-text-subtle)">raw → roast</span>
        </div>
        <span class="text-xs font-medium rounded-full px-2 py-0.5" style="background:#ffedd5;color:#c2410c">{{ $directRoastings->count() }}</span>
    </div>

    @if ($directRoastings->isEmpty())
        <div class="px-4 py-6 text-center text-sm" style="color:var(--admin-text-subtle)">No direct roasting from this batch.</div>
    @else
        @foreach ($directRoastings as $r)
            <div class="px-4 py-3 border-b last:border-0 text-sm" style="border-color:var(--admin-border)">
                <div class="flex items-center justify-between mb-1">
                    <span class="font-medium">{{ $r->date?->format('d M Y') }}</span>
                    <a href="{{ route('admin.roastings.show', $r) }}" class="text-xs font-medium" style="color:#ea580c">View →</a>
                </div>
                <div class="flex gap-4 text-xs" style="color:var(--admin-text-subtle)">
                    <span>In: <strong class="font-semibold" style="color:var(--admin-text)">{{ number_format($r->quantity_in, 1) }} kg</strong></span>
                    <span>Loss: <strong class="text-red-500">{{ number_format($r->loss ?? 0, 1) }} kg</strong></span>
                    <span>Out: <strong class="text-green-600">{{ number_format($r->quantity_out, 1) }} kg</strong></span>
                </div>
                @if ($r->chef)
                    <div class="text-xs mt-1" style="color:var(--admin-text-subtle)">Chef: {{ $r->chef->full_name }}</div>
                @endif
            </div>
        @endforeach
    @endif
</div>

{{-- Roasting from sorted batches --}}
@if ($roastingsFromSortings->isNotEmpty())
<div class="admin-card overflow-hidden mb-4">
    <div class="flex items-center justify-between px-4 py-2.5 border-b" style="border-color:var(--admin-border);background:var(--admin-bg)">
        <div class="flex items-center gap-2 text-sm font-semibold">
            <svg width="14" height="14" fill="none" stroke="#7c3aed" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 0 0 2.5 2.5z"/></svg>
            Roasting via sorting <span class="font-normal text-xs ml-1" style="color:var(--admin-text-subtle)">sorted → roast</span>
        </div>
        <span class="text-xs font-medium rounded-full px-2 py-0.5" style="background:#ede9fe;color:#6d28d9">{{ $roastingsFromSortings->count() }}</span>
    </div>
    @foreach ($roastingsFromSortings as $r)
        <div class="px-4 py-3 border-b last:border-0 text-sm" style="border-color:var(--admin-border)">
            <div class="flex items-center justify-between mb-1">
                <span class="font-medium">{{ $r->date?->format('d M Y') }}</span>
                <a href="{{ route('admin.roastings.show', $r) }}" class="text-xs font-medium" style="color:#7c3aed">View →</a>
            </div>
            <div class="flex gap-4 text-xs" style="color:var(--admin-text-subtle)">
                <span>In: <strong class="font-semibold" style="color:var(--admin-text)">{{ number_format($r->quantity_in, 1) }} kg</strong></span>
                <span>Loss: <strong class="text-red-500">{{ number_format($r->loss ?? 0, 1) }} kg</strong></span>
                <span>Out: <strong class="text-green-600">{{ number_format($r->quantity_out, 1) }} kg</strong></span>
            </div>
            @if ($r->chef)
                <div class="text-xs mt-1" style="color:var(--admin-text-subtle)">Chef: {{ $r->chef->full_name }}</div>
            @endif
        </div>
    @endforeach
</div>
@endif

{{-- Consumption bar --}}
@php
    $received    = (float) $stock->received;
    $rejected    = (float) $stock->rejected;
    $totalSorted = (float) $stock->sortings->sum('quantity_in');
    $totalRoasted= (float) ($directRoastings->sum('quantity_in') + $roastingsFromSortings->sum('quantity_in'));
@endphp
@if ($received > 0)
<div class="admin-card p-4 mb-4">
    <div class="text-xs font-semibold uppercase tracking-wide mb-3" style="color:var(--admin-text-subtle)">Consumption overview</div>
    <div class="h-3 rounded-full overflow-hidden flex mb-2" style="background:var(--admin-border)">
        @php
            $rejPct   = min(round($rejected   / $received * 100), 100);
            $sortPct  = min(round($totalSorted / $received * 100), 100 - $rejPct);
            $roastPct = min(round($totalRoasted/ $received * 100), 100 - $rejPct - $sortPct);
            $remPct   = max(100 - $rejPct - $sortPct - $roastPct, 0);
        @endphp
        @if ($rejPct)  <div class="h-full" style="width:{{ $rejPct }}%;background:#f87171"></div>  @endif
        @if ($sortPct) <div class="h-full" style="width:{{ $sortPct }}%;background:#3b82f6"></div> @endif
        @if ($roastPct)<div class="h-full" style="width:{{ $roastPct }}%;background:#f97316"></div>@endif
        @if ($remPct)  <div class="h-full" style="width:{{ $remPct }}%;background:#10498C;opacity:0.35"></div>@endif
    </div>
    <div class="flex flex-wrap gap-3 text-xs" style="color:var(--admin-text-subtle)">
        <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full inline-block" style="background:#f87171"></span>Rejected {{ number_format($rejected,1) }} kg</span>
        <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full inline-block" style="background:#3b82f6"></span>Sorted {{ number_format($totalSorted,1) }} kg</span>
        <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full inline-block" style="background:#f97316"></span>Roasted {{ number_format($totalRoasted,1) }} kg</span>
        <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full inline-block" style="background:#10498C;opacity:0.5"></span>Remaining {{ number_format($stock->quantity_in,1) }} kg</span>
    </div>
</div>
@endif

{{-- Delete --}}
<div class="admin-card p-4">
    <form method="POST" action="{{ route('admin.raw-material-stocks.destroy', $stock) }}"
          onsubmit="return confirm('Delete this reception record?')">
        @csrf
        @method('DELETE')
        <button type="submit" class="w-full rounded-md border border-red-200 bg-red-50 px-3 py-2 text-xs font-medium text-red-600 hover:bg-red-100 transition-colors">
            Delete reception
        </button>
    </form>
</div>

@endsection
