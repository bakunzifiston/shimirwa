@extends('layouts.admin')

@section('title', 'Add client')
@section('page_title', 'Add client')
@section('page_subtitle', 'Manage buyers and suppliers')

@section('content')
    <form method="POST" action="{{ route('admin.clients.store') }}" class="admin-card max-w-4xl">
        @csrf
        <div class="admin-card-header">
            <h2 class="admin-card-title">Clients & Suppliers details</h2>
        </div>
        <div class="admin-card-body">
            @include('admin.clients._form')
            <x-admin.form-actions :cancel-route="route('admin.clients.index')" submit-label="Save" />
        </div>
    </form>
@endsection
