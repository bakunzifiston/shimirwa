@extends('layouts.admin')

@section('title', 'Add user')
@section('page_title', 'Add user')
@section('page_subtitle', 'System access accounts')

@section('content')
    <form method="POST" action="{{ route('admin.users.store') }}" class="admin-card max-w-4xl">
        @csrf
        <div class="admin-card-header">
            <h2 class="admin-card-title">Users details</h2>
        </div>
        <div class="admin-card-body">
            @include('admin.users._form')
            <x-admin.form-actions :cancel-route="route('admin.users.index')" submit-label="Save" />
        </div>
    </form>
@endsection
