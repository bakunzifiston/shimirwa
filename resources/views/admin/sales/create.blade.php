@extends('layouts.admin')

@section('title', 'Add sale')
@section('page_title', 'Add sale')
@section('page_subtitle', 'Sales and distribution')

@section('content')
    <form method="POST" action="{{ route('admin.sales.store') }}" class="admin-card max-w-4xl">
        @csrf
        <div class="admin-card-header">
            <h2 class="admin-card-title">Sales details</h2>
        </div>
        <div class="admin-card-body">
            @include('admin.sales._form')
            <x-admin.form-actions :cancel-route="route('admin.sales.index')" submit-label="Save" />
        </div>
    </form>
@endsection
