@extends('layouts.admin')

@section('title', 'Edit client')
@section('page_title', 'Edit client')

@section('content')
    <form method="POST" action="{{ route('admin.clients.update', $client) }}" class="admin-card max-w-4xl">
        @csrf
        @method('PUT')
        <div class="admin-card-header">
            <h2 class="admin-card-title">Clients & Suppliers details</h2>
        </div>
        <div class="admin-card-body">
            @include('admin.clients._form')
            <x-admin.form-actions :cancel-route="route('admin.clients.show', $client)" submit-label="Update" />
        </div>
    </form>
@endsection
