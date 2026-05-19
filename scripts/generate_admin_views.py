#!/usr/bin/env python3
"""Generate admin Blade views for production modules."""
from pathlib import Path

BASE = Path(__file__).resolve().parents[1] / "resources/views/admin"


def w(rel: str, content: str) -> None:
    p = BASE / rel
    p.parent.mkdir(parents=True, exist_ok=True)
    p.write_text(content.strip() + "\n", encoding="utf-8")


# --- Roastings ---
w("roastings/index.blade.php", r"""@extends('layouts.admin')
@section('title', 'Roasting')
@section('page_title', 'Roasting')
@section('header_actions')
    <a href="{{ route('admin.roastings.create') }}" class="admin-btn-primary rounded-md px-4 py-2 text-sm font-medium no-underline">Add roasting</a>
@endsection
@section('content')
    <motion class="admin-card overflow-hidden">
        <form method="GET" class="flex flex-wrap gap-3 border-b border-slate-200 p-4">
            <input type="search" name="search" value="{{ $search }}" placeholder="Search batch…" class="admin-input max-w-xs">
            <button type="submit" class="rounded-md border border-slate-300 px-4 py-2 text-sm">Search</button>
        </form>
        <div class="overflow-x-auto">
            <table class="admin-table w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-slate-200">
                        <th class="px-4 py-3">Date</th>
                        <th class="px-4 py-3">Batch</th>
                        <th class="px-4 py-3">Qty in</th>
                        <th class="px-4 py-3">Loss</th>
                        <th class="px-4 py-3">Chef</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($roastings as $roasting)
                        <tr class="border-b border-slate-100 hover:bg-slate-50">
                            <td class="px-4 py-3">{{ optional($roasting->date)->format('Y-m-d') }}</td>
                            <td class="px-4 py-3 font-medium">{{ $roasting->batch }}</td>
                            <td class="px-4 py-3">{{ number_format($roasting->quantity_in, 2) }} kg</td>
                            <td class="px-4 py-3">{{ number_format($roasting->loss, 2) }} kg</td>
                            <td class="px-4 py-3">{{ $roasting->chef?->full_name }}</td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('admin.roastings.show', $roasting) }}" class="text-[#10498C]">View</a>
                                <span class="text-slate-300">|</span>
                                <a href="{{ route('admin.roastings.edit', $roasting) }}" class="text-[#10498C]">Edit</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-8 text-center text-slate-500">No roastings found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-200 p-4">{{ $roastings->links() }}</div>
    </motion>
@endsection""")

w("roastings/create.blade.php", r"""@extends('layouts.admin')
@section('title', 'Add roasting')
@section('page_title', 'Add roasting')
@section('content')
    <form method="POST" action="{{ route('admin.roastings.store') }}" class="admin-card max-w-4xl p-6">
        @csrf
        @include('admin.roastings._form')
        <div class="mt-6 flex gap-3">
            <button type="submit" class="admin-btn-primary rounded-md px-4 py-2 text-sm font-medium">Save</button>
            <a href="{{ route('admin.roastings.index') }}" class="rounded-md border border-slate-300 px-4 py-2 text-sm no-underline">Cancel</a>
        </div>
    </form>
@endsection""")

w("roastings/edit.blade.php", r"""@extends('layouts.admin')
@section('title', 'Edit roasting')
@section('page_title', 'Edit roasting')
@section('content')
    <form method="POST" action="{{ route('admin.roastings.update', $roasting) }}" class="admin-card max-w-4xl p-6">
        @csrf
        @method('PUT')
        @include('admin.roastings._form')
        <motion class="mt-6 flex gap-3">
            <button type="submit" class="admin-btn-primary rounded-md px-4 py-2 text-sm font-medium">Update</button>
            <a href="{{ route('admin.roastings.show', $roasting) }}" class="rounded-md border border-slate-300 px-4 py-2 text-sm no-underline">Cancel</a>
        </motion>
    </form>
@endsection""")

w("roastings/show.blade.php", r"""@extends('layouts.admin')
@section('title', 'Roasting')
@section('page_title', 'Roasting #{{ $roasting->id }}')
@section('header_actions')
    <a href="{{ route('admin.roastings.edit', $roasting) }}" class="admin-btn-primary rounded-md px-4 py-2 text-sm no-underline">Edit</a>
@endsection
@section('content')
    <div class="admin-card max-w-2xl p-6">
        <dl class="grid gap-4 sm:grid-cols-2">
            <div><dt class="text-sm text-slate-500">Date</dt><dd class="font-medium">{{ optional($roasting->date)->format('Y-m-d') }}</dd></div>
            <div><dt class="text-sm text-slate-500">Batch</dt><dd class="font-medium">{{ $roasting->batch }}</dd></div>
            <div><dt class="text-sm text-slate-500">Source</dt><dd class="font-medium">
                @if($roasting->rawMaterialStock)
                    Raw: {{ $roasting->rawMaterialStock->item }} — {{ $roasting->rawMaterialStock->batch_number }}
                @elseif($roasting->sorting)
                    Sorting: {{ $roasting->sorting->rawMaterialStock?->batch_number }}
                @else — @endif
            </dd></div>
            <div><dt class="text-sm text-slate-500">Quantity in</dt><dd>{{ number_format($roasting->quantity_in, 2) }} kg</dd></motion>
            <div><dt class="text-sm text-slate-500">Loss</dt><dd>{{ number_format($roasting->loss, 2) }} kg</dd></div>
            <div><dt class="text-sm text-slate-500">Chef</dt><dd>{{ $roasting->chef?->full_name }}</dd></div>
            <div><dt class="text-sm text-slate-500">Supervisor</dt><dd>{{ $roasting->supervisor?->full_name }}</dd></div>
        </dl>
        <form method="POST" action="{{ route('admin.roastings.destroy', $roasting) }}" class="mt-8 border-t pt-6" onsubmit="return confirm('Delete this roasting record?')">
            @csrf @method('DELETE')
            <button type="submit" class="rounded-md bg-red-600 px-4 py-2 text-sm text-white">Delete</button>
        </form>
    </div>
@endsection""")

print("roastings crud pages")
