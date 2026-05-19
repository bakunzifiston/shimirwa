@extends('layouts.admin')

@section('title', 'Edit stock')
@section('page_title', 'Edit stock')

@section('content')
    <form method="POST" action="{{ route('admin.raw-material-stocks.update', $stock) }}" class="admin-card max-w-4xl">
        @csrf
        @method('PUT')
        <div class="admin-card-header">
            <h2 class="admin-card-title">Reception of materials details</h2>
        </div>
        <div class="admin-card-body">
            @include('admin.raw-material-stocks._form')
            <x-admin.form-actions :cancel-route="route('admin.raw-material-stocks.show', $stock)" submit-label="Update" />
        </div>
    </form>
@endsection
