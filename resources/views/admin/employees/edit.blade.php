@extends('layouts.admin')

@section('title', 'Edit employee')
@section('page_title', 'Edit employee')

@section('content')
    <form method="POST" action="{{ route('admin.employees.update', $employee) }}" class="admin-card max-w-4xl">
        @csrf
        @method('PUT')
        <div class="admin-card-header">
            <h2 class="admin-card-title">Employee details</h2>
        </div>
        <div class="admin-card-body">
            @include('admin.employees._form')
            <x-admin.form-actions :cancel-route="route('admin.employees.show', $employee)" submit-label="Update employee" />
        </div>
    </form>
@endsection
