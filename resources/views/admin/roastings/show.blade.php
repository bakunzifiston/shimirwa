@extends('layouts.admin')
@section('title', 'Roasting')
@section('page_title', 'Roasting')
@section('header_actions')
    <a href="{{ route('admin.roastings.edit', $roasting) }}" class="admin-btn-primary rounded-md px-4 py-2 text-sm no-underline">Edit</a>
@endsection
@section('content')
    <div class="admin-card max-w-2xl p-6">
        <dl class="grid gap-4 sm:grid-cols-2">
            <div><dt class="text-sm text-slate-500">Date</dt><dd>{{ optional($roasting->date)->format('Y-m-d') }}</dd></div>
            <div><dt class="text-sm text-slate-500">Batch</dt><dd>{{ $roasting->batch }}</dd></div>
            <div class="sm:col-span-2"><dt class="text-sm text-slate-500">Source</dt><dd>
                @if($roasting->rawMaterialStock)
                    Raw: {{ $roasting->rawMaterialStock->item }} — {{ $roasting->rawMaterialStock->batch_number }}
                @elseif($roasting->sorting)
                    Sorting: {{ $roasting->sorting->rawMaterialStock?->item }} — {{ $roasting->sorting->rawMaterialStock?->batch_number }}
                @else — @endif
            </dd></div>
            <div><dt class="text-sm text-slate-500">Quantity in</dt><dd>{{ number_format($roasting->quantity_in, 2) }} kg</dd></div>
            <div><dt class="text-sm text-slate-500">Quantity out</dt><dd>{{ number_format($roasting->quantityOut(), 2) }} kg</dd></div>
            <div><dt class="text-sm text-slate-500">Remaining</dt><dd>{{ number_format($roasting->remainingUsable(), 2) }} kg</dd></div>
            <div><dt class="text-sm text-slate-500">Loss</dt><dd>{{ number_format($roasting->loss, 2) }} kg</dd></div>
            <div><dt class="text-sm text-slate-500">Chef</dt><dd>{{ $roasting->chef?->full_name }}</dd></div>
            <div><dt class="text-sm text-slate-500">Supervisor</dt><dd>{{ $roasting->supervisor?->full_name }}</dd></div>
        </dl>
        <form method="POST" action="{{ route('admin.roastings.destroy', $roasting) }}" class="mt-8 border-t pt-6" onsubmit="return confirm('Delete?')">
            @csrf @method('DELETE')
            <button type="submit" class="rounded-md bg-red-600 px-4 py-2 text-sm text-white">Delete</button>
        </form>
    </div>
@endsection
