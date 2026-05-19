@extends('layouts.admin')

@section('title', 'Edit sale')
@section('page_title', 'Edit sale')

@section('content')
    <form method="POST" action="{{ route('admin.sales.update', $sale) }}" class="admin-card max-w-4xl">
        @csrf
        @method('PUT')
        <div class="admin-card-header">
            <h2 class="admin-card-title">Sales details</h2>
        </div>
        <div class="admin-card-body">
            @include('admin.sales._form')
            <x-admin.form-actions :cancel-route="route('admin.sales.show', $sale)" submit-label="Update" />
        </div>
    </form>
@endsection
