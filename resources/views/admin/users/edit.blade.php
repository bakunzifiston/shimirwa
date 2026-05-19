@extends('layouts.admin')

@section('title', 'Edit user')
@section('page_title', 'Edit user')

@section('content')
    <form method="POST" action="{{ route('admin.users.update', $user) }}" class="admin-card max-w-4xl">
        @csrf
        @method('PUT')
        <div class="admin-card-header">
            <h2 class="admin-card-title">Users details</h2>
        </div>
        <div class="admin-card-body">
            @include('admin.users._form')
            <x-admin.form-actions :cancel-route="route('admin.users.show', $user)" submit-label="Update" />
        </div>
    </form>
@endsection
