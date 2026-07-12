@extends('layouts.admin')
@section('title', 'Milling — ' . $milling->batch_number)
@section('page_title', 'Milling — ' . $milling->batch_number)
@section('page_subtitle', optional($milling->date)->format('d M Y'))

@section('content')

{{-- Action bar --}}
<div class="flex justify-end mb-4">
    <a href="{{ route('admin.millings.edit', $milling) }}"
       data-drawer-src="{{ route('admin.millings.edit', $milling) }}"
       data-drawer-title="Edit milling"
       class="admin-btn admin-btn-primary admin-btn-sm">Edit</a>
</div>

{{-- Summary cards --}}
<div class="grid grid-cols-2 gap-3 mb-4 sm:grid-cols-4">
    <div class="admin-card p-4 text-center">
        <div class="text-xl font-bold">{{ number_format($milling->total_mixed_quantity, 1) }}</div>
        <div class="text-xs mt-0.5" style="color:var(--admin-text-subtle)">Total mixed (kg)</div>
    </div>
    <div class="admin-card p-4 text-center">
        <div class="text-xl font-bold text-red-500">{{ number_format($milling->loss ?? 0, 1) }}</div>
        <div class="text-xs mt-0.5" style="color:var(--admin-text-subtle)">Loss (kg)</div>
    </div>
    <div class="admin-card p-4 text-center" style="border-left:3px solid var(--admin-primary, #10498C)">
        <div class="text-xl font-bold db-revenue-today">{{ number_format($milling->output_flour, 1) }}</div>
        <div class="text-xs mt-0.5" style="color:var(--admin-text-subtle)">Output flour (kg)</div>
    </div>
    <div class="admin-card p-4 text-center">
        <div class="text-xl font-bold">{{ $ingredients->count() }}</div>
        <div class="text-xs mt-0.5" style="color:var(--admin-text-subtle)">Ingredient batches</div>
    </div>
</div>

{{-- Batch info --}}
<div class="admin-card p-5 mb-4">
    <div class="grid grid-cols-2 gap-3 text-sm sm:grid-cols-3">
        <div>
            <div class="text-xs mb-0.5" style="color:var(--admin-text-subtle)">Date</div>
            <div class="font-medium">{{ optional($milling->date)->format('d M Y') }}</div>
        </div>
        <div>
            <div class="text-xs mb-0.5" style="color:var(--admin-text-subtle)">Batch #</div>
            <div class="font-medium font-mono">{{ $milling->batch_number }}</div>
        </div>
        <div>
            <div class="text-xs mb-0.5" style="color:var(--admin-text-subtle)">Employee</div>
            <div class="font-medium">{{ $milling->employee?->full_name ?? '—' }}</div>
        </div>
    </div>
</div>

{{-- Ingredients trace --}}
<div class="admin-card overflow-hidden mb-4">
    <div class="flex items-center justify-between px-4 py-2.5 border-b" style="border-color:var(--admin-border);background:var(--admin-bg)">
        <div class="flex items-center gap-2 text-sm font-semibold">
            <svg width="14" height="14" fill="none" stroke="#7c3aed" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
            Ingredient traceability
        </div>
        <span class="text-xs font-medium rounded-full px-2 py-0.5" style="background:#ede9fe;color:#6d28d9">{{ $ingredients->count() }} batches</span>
    </div>

    @if ($ingredients->isEmpty())
        <div class="px-4 py-6 text-center text-sm" style="color:var(--admin-text-subtle)">No ingredient data recorded.</div>
    @else
        @foreach ($ingredients as $ing)
        @php
            $batch   = $ing['batch'];
            $isRoast = $ing['source'] === 'roasting';
            $isRaw   = $ing['source'] === 'raw';
        @endphp
        <div class="px-4 py-4 border-b last:border-0" style="border-color:var(--admin-border)">
            {{-- Row header --}}
            <div class="flex items-start justify-between gap-4 mb-2">
                <div class="flex items-center gap-2">
                    <span class="text-sm font-semibold">{{ $ing['item_name'] }}</span>
                    @if ($isRoast)
                        <span class="text-xs px-1.5 py-0.5 rounded font-medium" style="background:#ffedd5;color:#c2410c">from roasting</span>
                    @elseif ($isRaw)
                        <span class="text-xs px-1.5 py-0.5 rounded font-medium" style="background:#dcfce7;color:#15803d">direct / reception</span>
                    @else
                        <span class="admin-badge admin-badge--primary text-xs">from sorting</span>
                    @endif
                </div>
                <span class="text-sm font-bold db-revenue-today">{{ number_format($ing['quantity'], 1) }} kg</span>
            </div>

            {{-- Batch detail --}}
            <div class="grid grid-cols-2 gap-x-6 gap-y-1 text-xs sm:grid-cols-4" style="color:var(--admin-text-subtle)">
                <div>
                    <span>Batch ref:</span>
                    <strong class="font-mono ml-1" style="color:var(--admin-text)">{{ $ing['batch_ref'] }}</strong>
                </div>

                @if ($batch)
                    <div>
                        <span>Date:</span>
                        <strong class="ml-1" style="color:var(--admin-text)">{{ optional($batch->date)->format('d M Y') }}</strong>
                    </div>

                    @if ($isRaw)
                        {{-- Direct reception trace --}}
                        <div>
                            <span>Received:</span>
                            <strong class="ml-1" style="color:var(--admin-text)">{{ number_format((float)($batch->received ?? 0), 1) }} kg</strong>
                        </div>
                        <div>
                            <span>Supplier:</span>
                            <strong class="ml-1" style="color:var(--admin-text)">{{ $batch->client?->full_name ?? '—' }}</strong>
                        </div>

                    @elseif ($isRoast)
                        {{-- Roasting trace --}}
                        <div>
                            <span>Roasted in:</span>
                            <strong class="ml-1" style="color:var(--admin-text)">{{ number_format($batch->quantity_in, 1) }} kg</strong>
                        </div>
                        <div>
                            <span>After loss:</span>
                            <strong class="ml-1 text-green-600">{{ number_format($batch->quantity_out, 1) }} kg</strong>
                        </div>
                        @if ($batch->chef)
                        <div>
                            <span>Chef:</span>
                            <strong class="ml-1" style="color:var(--admin-text)">{{ $batch->chef->full_name }}</strong>
                        </div>
                        @endif
                        {{-- Trace back to raw material --}}
                        @php
                            $rawStock = $batch->rawMaterialStock ?? $batch->sorting?->rawMaterialStock;
                        @endphp
                        @if ($rawStock)
                        <div>
                            <span>Reception batch:</span>
                            <strong class="font-mono ml-1" style="color:var(--admin-text)">{{ $rawStock->batch_number }}</strong>
                        </div>
                        <div>
                            <span>Supplier:</span>
                            <strong class="ml-1" style="color:var(--admin-text)">{{ $rawStock->client?->full_name ?? '—' }}</strong>
                        </div>
                        @endif

                    @else
                        {{-- Sorting trace --}}
                        <div>
                            <span>Sorted in:</span>
                            <strong class="ml-1" style="color:var(--admin-text)">{{ number_format($batch->quantity_in, 1) }} kg</strong>
                        </div>
                        <div>
                            <span>After loss:</span>
                            <strong class="ml-1 text-green-600">{{ number_format($batch->quantity_out, 1) }} kg</strong>
                        </div>
                        @if ($batch->employee)
                        <div>
                            <span>Sorted by:</span>
                            <strong class="ml-1" style="color:var(--admin-text)">{{ $batch->employee->full_name }}</strong>
                        </div>
                        @endif
                        @if ($batch->rawMaterialStock)
                        <div>
                            <span>Reception batch:</span>
                            <strong class="font-mono ml-1" style="color:var(--admin-text)">{{ $batch->rawMaterialStock->batch_number }}</strong>
                        </div>
                        <div>
                            <span>Supplier:</span>
                            <strong class="ml-1" style="color:var(--admin-text)">{{ $batch->rawMaterialStock->client?->full_name ?? '—' }}</strong>
                        </div>
                        @endif
                    @endif
                @else
                    <div class="col-span-3" style="color:#dc2626">Batch record not found (may have been deleted).</div>
                @endif
            </div>

            {{-- Mini pipeline arrow --}}
            @if ($batch)
            <div class="mt-2 flex items-center gap-1 text-xs" style="color:var(--admin-text-subtle)">
                @if ($isRaw)
                    <span class="px-1.5 py-0.5 rounded" style="background:var(--admin-border)">Reception {{ $batch->batch_number }}</span>
                    <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg>
                    <span class="px-1.5 py-0.5 rounded font-medium" style="background:#dcfce7;color:#15803d">Direct → Milled {{ number_format($ing['quantity'], 1) }} kg</span>
                @else
                    @php $rawStock = $isRoast ? ($batch->rawMaterialStock ?? $batch->sorting?->rawMaterialStock) : $batch->rawMaterialStock; @endphp
                    @if ($rawStock)
                        <span class="px-1.5 py-0.5 rounded" style="background:var(--admin-border)">Reception {{ $rawStock->batch_number }}</span>
                        <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg>
                    @endif
                    @if ($isRoast && $batch->sorting)
                        <span class="admin-badge admin-badge--primary">Sorted</span>
                        <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg>
                    @endif
                    <span class="px-1.5 py-0.5 rounded font-medium" style="{{ $isRoast ? 'background:#ffedd5;color:#c2410c' : '' }}" class="{{ !$isRoast ? 'admin-badge admin-badge--primary' : '' }}">
                        {{ $isRoast ? 'Roasted' : 'Sorted' }}
                    </span>
                    <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg>
                    <span class="px-1.5 py-0.5 rounded font-medium" style="background:#f0fdf4;color:#16a34a">Milled {{ number_format($ing['quantity'], 1) }} kg</span>
                @endif
            </div>
            @endif
        </div>
        @endforeach

        {{-- Totals footer --}}
        <div class="px-4 py-2 flex justify-between text-xs border-t" style="border-color:var(--admin-border);background:var(--admin-bg);color:var(--admin-text-subtle)">
            <span>Total ingredients: <strong style="color:var(--admin-text)">{{ number_format($ingredients->sum('quantity'), 1) }} kg</strong></span>
            <span>Output flour: <strong class="db-revenue-today">{{ number_format($milling->output_flour, 1) }} kg</strong></span>
        </div>
    @endif
</div>

{{-- Delete --}}
<div class="admin-card p-4">
    <form method="POST" action="{{ route('admin.millings.destroy', $milling) }}"
          onsubmit="return confirm('Delete this milling record?')">
        @csrf @method('DELETE')
        <button type="submit" class="w-full rounded-md border border-red-200 bg-red-50 px-3 py-2 text-xs font-medium text-red-600 hover:bg-red-100 transition-colors">
            Delete milling
        </button>
    </form>
</div>

@endsection
