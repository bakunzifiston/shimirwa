@extends('layouts.admin')

@section('title', 'Edit milling')
@section('page_title', 'Edit milling')

@section('content')
    <form method="POST" action="{{ route('admin.millings.update', $milling) }}" class="admin-card max-w-4xl">
        @csrf
        @method('PUT')
        <div class="admin-card-header">
            <h2 class="admin-card-title">Milling details</h2>
        </div>
        <div class="admin-card-body">
            @include('admin.millings._form')
            <x-admin.form-actions :cancel-route="route('admin.millings.show', $milling)" submit-label="Update" />
        </div>
    </form>
@endsection
