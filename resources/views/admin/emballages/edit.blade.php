@extends('layouts.admin')

@section('title', 'Edit emballage')
@section('page_title', 'Edit emballage')

@section('content')
    <form method="POST" action="{{ route('admin.emballages.update', $emballage) }}" class="admin-card max-w-4xl">
        @csrf
        @method('PUT')
        <div class="admin-card-header">
            <h2 class="admin-card-title">Packaging details</h2>
        </div>
        <div class="admin-card-body">
            @include('admin.emballages._form')
            <x-admin.form-actions :cancel-route="route('admin.emballages.show', $emballage)" submit-label="Update" />
        </div>
    </form>
@endsection
