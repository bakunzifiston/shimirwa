@extends('layouts.admin')

@section('title', 'Clients & Suppliers')
@section('page_title', 'Clients & Suppliers')
@section('page_subtitle', 'Manage buyers and suppliers')

@section('content')
    <x-admin.listing :paginator="$clients" :show-search="false">
        <x-slot:toolbar>
            <form method="GET" class="flex flex-1 flex-wrap items-center gap-2">
                <div class="admin-search-wrap">
                    <x-admin.icon name="search" class="!absolute !left-3 !top-1/2 !h-4 !w-4 !-translate-y-1/2" style="color: var(--admin-text-subtle)" />
                    <input type="search" name="search" value="{{ $search }}" placeholder="Search clients…" class="admin-input">
                </div>
                <select name="role" class="admin-input w-auto min-w-[8rem]">
                    <option value="">All roles</option>
                    <option value="client" @selected($role === 'client')>Clients</option>
                    <option value="supplier" @selected($role === 'supplier')>Suppliers</option>
                </select>
                <button type="submit" class="admin-btn admin-btn-secondary admin-btn-sm">Filter</button>
                @if ($search || $role)
                    <a href="{{ route('admin.clients.index') }}" class="admin-btn admin-btn-ghost admin-btn-sm">Clear</a>
                @endif
            </form>
        </x-slot:toolbar>
        <x-slot:actions>
            <a href="{{ route('admin.clients.create') }}" class="admin-btn admin-btn-primary admin-btn-sm">
                <x-admin.icon name="plus" class="!h-4 !w-4" />
                Add client
            </a>
        </x-slot:actions>

        <x-slot:head>
            <th>Name</th><th>Role</th><th>Phone</th><th>District</th><th class="text-right">Actions</th>
        </x-slot:head>

        @forelse ($clients as $client)
            <tr>
                <td class="cell-primary">{{ $client->full_name }}</td>
                <td><span class="admin-badge admin-badge--primary">{{ ucfirst($client->role) }}</span></td>
                <td>{{ $client->phone_number }}</td>
                <td>{{ $client->district }}</td>
                <td class="text-right">
                    <x-admin.row-actions
                        :view-route="route('admin.clients.show', $client)"
                        :edit-route="route('admin.clients.edit', $client)"
                    />
                </td>
            </tr>
        @empty
            <x-admin.empty-state colspan="5" />
        @endforelse
    </x-admin.listing>
@endsection
