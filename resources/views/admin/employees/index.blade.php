@extends('layouts.admin')

@section('title', 'Employees')
@section('page_title', 'Employees')
@section('page_subtitle', 'Manage staff records and assignments')

@section('content')
    <x-admin.page-stats :stats="$pageStats" />
    <x-admin.listing
        :paginator="$employees"
        :search="$search"
        :clear-route="route('admin.employees.index')"
        placeholder="Search by name, ID, or phoneâ€¦"
    >
        <x-slot:actions>
            <a href="{{ route('admin.employees.create') }}" data-drawer-src="{{ route('admin.employees.create') }}" data-drawer-title="Add" class="admin-btn admin-btn-primary admin-btn-sm">
                <x-admin.icon name="plus" class="!h-4 !w-4" />
                Add employee
            </a>
        </x-slot:actions>

        <x-slot:head>
            <th>Name</th>
            <th>National ID</th>
            <th>Phone</th>
            <th>Position</th>
            <th>Start date</th>
            <th class="text-right">Actions</th>
        </x-slot:head>

        @forelse ($employees as $employee)
            <tr>
                <td class="cell-primary">{{ $employee->full_name }}</td>
                <td>{{ $employee->national_id }}</td>
                <td>{{ $employee->phone_number }}</td>
                <td>{{ $employee->position }}</td>
                <td>{{ optional($employee->start_date)->format('Y-m-d') }}</td>
                <td class="text-right">
                    <x-admin.row-actions
                        :view-route="route('admin.employees.show', $employee)"
                        :edit-route="route('admin.employees.edit', $employee)"
                        :delete-route="route('admin.employees.destroy', $employee)"
                    />
                </td>
            </tr>
        @empty
            <x-admin.empty-state colspan="6" title="No employees found" />
        @endforelse
    </x-admin.listing>
@endsection

