@extends('layouts.admin')
@section('title', 'Milling')
@section('page_title', 'Milling')
@section('header_actions')
    <a href="{{ route('admin.millings.edit', $milling) }}" class="admin-btn-primary rounded-md px-4 py-2 text-sm no-underline">Edit</a>
@endsection
@section('content')
    <div class="admin-card max-w-3xl p-6">
        <dl class="grid gap-4 sm:grid-cols-2">
            <div><dt class="text-sm text-slate-500">Date</dt><dd>{{ optional($milling->date)->format('Y-m-d') }}</dd></div>
            <div><dt class="text-sm text-slate-500">Batch</dt><dd>{{ $milling->batch_number }}</dd></div>
            <div><dt class="text-sm text-slate-500">Total mixed</dt><dd>{{ number_format($milling->total_mixed_quantity, 2) }} kg</dd></div>
            <div><dt class="text-sm text-slate-500">Loss</dt><dd>{{ number_format($milling->loss, 2) }} kg</dd></div>
            <div><dt class="text-sm text-slate-500">Output flour</dt><dd>{{ number_format($milling->output_flour, 2) }} kg</dd></div>
            <div><dt class="text-sm text-slate-500">Employee</dt><dd>{{ $milling->employee?->full_name }}</dd></div>
        </dl>
        @if(is_array($milling->items) && count($milling->items))
            <h3 class="mt-6 font-medium">Ingredients</h3>
            <ul class="mt-2 list-disc pl-5 text-sm">
                @foreach($milling->items as $item)
                    <li>{{ ucfirst($item['type'] ?? '') }} — batch #{{ $item['stock_id'] ?? '' }}: {{ $item['quantity'] ?? 0 }} kg</li>
                @endforeach
            </ul>
        @endif
        <form method="POST" action="{{ route('admin.millings.destroy', $milling) }}" class="mt-8 border-t pt-6" onsubmit="return confirm('Delete?')">
            @csrf @method('DELETE')
            <button type="submit" class="rounded-md bg-red-600 px-4 py-2 text-sm text-white">Delete</button>
        </form>
    </div>
@endsection
