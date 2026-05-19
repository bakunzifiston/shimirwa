@extends('layouts.admin')

@section('title', 'Add employee')
@section('page_title', 'Add employee')
@section('page_subtitle', 'Create a new staff record')

@section('content')
    <form method="POST" action="{{ route('admin.employees.store') }}" class="admin-card max-w-4xl">
        @csrf
        <div class="admin-card-header">
            <h2 class="admin-card-title">Employee details</h2>
        </div>
        <div class="admin-card-body">
            @include('admin.employees._form')
            <x-admin.form-actions :cancel-route="route('admin.employees.index')" submit-label="Save employee" />
        </div>
    </form>
@endsection
