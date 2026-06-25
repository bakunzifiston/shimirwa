@extends('layouts.admin')@section('title','Sorting')@section('page_title','Sorting')
@section('header_actions')<a href="{{ route('admin.sortings.edit',$sorting) }}" class="admin-btn-primary rounded px-4 py-2 text-sm no-underline">Edit</a>@endsection
@section('content')
    <div class="flex items-center justify-between mb-4">
        <div class="flex gap-2">
            <a href="{{ route('admin.sortings.edit', $sorting) }}"
               data-drawer-src="{{ route('admin.sortings.edit', $sorting) }}"
               data-drawer-title="Edit"
               class="admin-btn admin-btn-primary admin-btn-sm">Edit</a>
        </div>
    </div>
    <div class="admin-card max-w-2xl p-6"><dl class="grid gap-3 sm:grid-cols-2">
<div><dt class="text-slate-500 text-sm">Date</dt><dd>{{ $sorting->date?->format('Y-m-d') }}</dd></div>
<div><dt class="text-slate-500 text-sm">Batch</dt><dd>{{ $sorting->rawMaterialStock?->item }} — {{ $sorting->rawMaterialStock?->batch_number }}</dd></div>
<div><dt class="text-slate-500 text-sm">Quantity in</dt><dd>{{ number_format($sorting->quantity_in,2) }} kg</dd></div>
<div><dt class="text-slate-500 text-sm">Loss</dt><dd>{{ number_format($sorting->loss,2) }} kg</dd></div>
<div><dt class="text-slate-500 text-sm">Employee</dt><dd>{{ $sorting->employee?->full_name }}</dd></div>
</dl><form method="POST" action="{{ route('admin.sortings.destroy',$sorting) }}" class="mt-8 border-t pt-6" onsubmit="return confirm('Delete?')">@csrf @method('DELETE')<button class="rounded bg-red-600 px-4 py-2 text-sm text-white">Delete</button></form></div>@endsection
