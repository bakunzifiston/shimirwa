@extends('layouts.admin')

@section('title', 'Users')
@section('page_title', 'Users')
@section('page_subtitle', 'System access accounts')

@section('content')
    <x-admin.listing
        :paginator="$users"
        :search="$search"
        :clear-route="route('admin.users.index')"
        placeholder="Search users…"
    >
        <x-slot:actions>
            <a href="{{ route('admin.users.create') }}" class="admin-btn admin-btn-primary admin-btn-sm">
                <x-admin.icon name="plus" class="!h-4 !w-4" />
                Add user
            </a>
        </x-slot:actions>

        <x-slot:head>
            <th>Name</th><th>Email</th><th>Created</th><th class="text-right">Actions</th>
        </x-slot:head>

        @forelse ($users as $user)
            <tr>
                <td class="cell-primary">{{ $user->name }}</td>
                <td>{{ $user->email }}</td>
                <td>{{ $user->created_at?->format('Y-m-d') }}</td>
                <td class="text-right">
                    <x-admin.row-actions
                        :view-route="route('admin.users.show', $user)"
                        :edit-route="route('admin.users.edit', $user)"
                    />
                </td>
            </tr>
        @empty
            <x-admin.empty-state colspan="4" />
        @endforelse
    </x-admin.listing>
@endsection
