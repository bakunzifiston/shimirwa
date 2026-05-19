@extends('layouts.admin')

@section('title', 'Edit sorting')
@section('page_title', 'Edit sorting')

@section('content')
    <form method="POST" action="{{ route('admin.sortings.update', $sorting) }}" class="admin-card max-w-4xl">
        @csrf
        @method('PUT')
        <div class="admin-card-header">
            <h2 class="admin-card-title">Sorting details</h2>
        </div>
        <div class="admin-card-body">
            @include('admin.sortings._form')
            <x-admin.form-actions :cancel-route="route('admin.sortings.show', $sorting)" submit-label="Update" />
        </div>
    </form>
@endsection
