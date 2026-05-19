@extends('layouts.admin')

@section('title', $employee->full_name)
@section('page_title', $employee->full_name)
@section('page_subtitle', $employee->position)

@section('header_actions')
    <a href="{{ route('admin.employees.edit', $employee) }}" class="admin-btn admin-btn-primary admin-btn-sm">
        <x-admin.icon name="pencil" class="!h-4 !w-4" />
        Edit
    </a>
@endsection

@section('content')
    <div class="admin-card max-w-2xl">
        <div class="admin-card-header">
            <h2 class="admin-card-title">Profile</h2>
        </div>
        <div class="admin-card-body">
            <dl class="admin-detail-grid">
                <div class="admin-detail-item"><dt>National ID</dt><dd>{{ $employee->national_id }}</dd></div>
                <div class="admin-detail-item"><dt>Phone</dt><dd>{{ $employee->phone_number }}</dd></div>
                <div class="admin-detail-item"><dt>Gender</dt><dd>{{ $employee->gender }}</dd></div>
                <div class="admin-detail-item"><dt>Position</dt><dd>{{ $employee->position }}</dd></div>
                <div class="admin-detail-item"><dt>Province</dt><dd>{{ $employee->province }}</dd></div>
                <div class="admin-detail-item"><dt>District</dt><dd>{{ $employee->district }}</dd></div>
                <div class="admin-detail-item"><dt>Start date</dt><dd>{{ optional($employee->start_date)->format('M j, Y') }}</dd></div>
            </dl>

            <form method="POST" action="{{ route('admin.employees.destroy', $employee) }}" class="admin-form-actions"
                  onsubmit="return confirm('Delete this employee?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="admin-btn admin-btn-danger admin-btn-sm">Delete employee</button>
            </form>
        </div>
    </div>
@endsection
