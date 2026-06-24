@extends('layouts.admin')

@section('title', 'Batch ' . $stock->batch_number)
@section('page_title', 'Material reception')

@section('header_actions')
    <a href="{{ route('admin.raw-material-stocks.edit', $stock) }}" class="admin-btn-primary rounded-md px-4 py-2 text-sm font-medium no-underline">Edit</a>
@endsection

@section('content')
    <div class="admin-card max-w-3xl p-6">
        <dl class="grid gap-4 sm:grid-cols-2">
            <div><dt class="text-sm text-slate-500">Date</dt><dd class="font-medium">{{ $stock->date?->format('Y-m-d') }}</dd></div>
            <div><dt class="text-sm text-slate-500">Supplier</dt><dd class="font-medium">{{ $stock->client?->full_name }}</dd></div>
            <div><dt class="text-sm text-slate-500">Type</dt><dd class="font-medium">{{ $stock->type }}</dd></div>
            <div><dt class="text-sm text-slate-500">Item</dt><dd class="font-medium">{{ $stock->item }}</dd></div>
            <div><dt class="text-sm text-slate-500">Received</dt><dd class="font-medium">{{ number_format($stock->received, 2) }}</dd></div>
            <div><dt class="text-sm text-slate-500">Rejected</dt><dd class="font-medium">{{ number_format($stock->rejected, 2) }}</dd></div>
            <div><dt class="text-sm text-slate-500">Remaining quantity</dt><dd class="font-medium text-[#10498C]">{{ number_format($stock->remainingQuantity(), 2) }} kg</dd></div>
            <div><dt class="text-sm text-slate-500">Batch number</dt><dd class="font-medium">{{ $stock->batch_number }}</dd></div>
            <div><dt class="text-sm text-slate-500">Employee</dt><dd class="font-medium">{{ $stock->employee?->full_name }}</dd></div>
            @if ($stock->comment)
                <div class="sm:col-span-2"><dt class="text-sm text-slate-500">Comment</dt><dd class="font-medium">{{ $stock->comment }}</dd></div>
            @endif
        </dl>

        <form method="POST" action="{{ route('admin.raw-material-stocks.destroy', $stock) }}" class="mt-8 border-t border-slate-200 pt-6"
              onsubmit="return confirm('Delete this reception record?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="rounded-md bg-red-600 px-4 py-2 text-sm font-medium text-white">Delete</button>
        </form>
    </div>
@endsection
