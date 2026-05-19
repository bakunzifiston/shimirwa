@extends('layouts.admin')

@section('title', 'Add stock')
@section('page_title', 'Add stock')
@section('page_subtitle', 'Raw material stock intake')

@section('content')
    <form method="POST" action="{{ route('admin.raw-material-stocks.store') }}" class="admin-card max-w-4xl">
        @csrf
        <div class="admin-card-header">
            <h2 class="admin-card-title">Reception of materials details</h2>
        </div>
        <div class="admin-card-body">
            @include('admin.raw-material-stocks._form')
            <x-admin.form-actions :cancel-route="route('admin.raw-material-stocks.index')" submit-label="Save" />
        </div>
    </form>
@endsection
