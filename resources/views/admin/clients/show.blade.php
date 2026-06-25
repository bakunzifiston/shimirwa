@extends('layouts.admin')
@section('title', $client->full_name)
@section('page_title', $client->full_name)
@section('header_actions')
    <a href="{{ route('admin.clients.edit', $client) }}" class="admin-btn-primary rounded-md px-4 py-2 text-sm font-medium no-underline">Edit</a>
@endsection
@section('content')
    <div class="flex items-center justify-between mb-4">
        <div class="flex gap-2">
            <a href="{{ route('admin.clients.edit', $client) }}"
               data-drawer-src="{{ route('admin.clients.edit', $client) }}"
               data-drawer-title="Edit"
               class="admin-btn admin-btn-primary admin-btn-sm">Edit</a>
        </div>
    </div>
    <div class="admin-card max-w-2xl p-6">
        <dl class="grid gap-4 sm:grid-cols-2">
            <div><dt class="text-sm text-slate-500">Type</dt><dd class="font-medium">{{ $client->client_type }}</dd></div>
            <div><dt class="text-sm text-slate-500">Role</dt><dd class="font-medium capitalize">{{ $client->role }}</dd></div>
            @if ($client->supplier_code)
                <div><dt class="text-sm text-slate-500">Supplier code</dt><dd class="font-medium">{{ $client->supplier_code }}</dd></div>
            @endif
            <div><dt class="text-sm text-slate-500">Phone</dt><dd class="font-medium">{{ $client->phone ?? '—' }}</dd></div>
            <div><dt class="text-sm text-slate-500">Email</dt><dd class="font-medium">{{ $client->email ?? '—' }}</dd></div>
            <div><dt class="text-sm text-slate-500">District</dt><dd class="font-medium">{{ $client->address }}</dd></div>
        </dl>
        <form method="POST" action="{{ route('admin.clients.destroy', $client) }}" class="mt-8 border-t border-slate-200 pt-6"
              onsubmit="return confirm('Delete this record?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="rounded-md bg-red-600 px-4 py-2 text-sm font-medium text-white">Delete</button>
        </form>
    </div>
@endsection
