@extends('layouts.admin')

@section('title', 'Edit roasting')
@section('page_title', 'Edit roasting')

@section('content')
    <form method="POST" action="{{ route('admin.roastings.update', $roasting) }}" class="admin-card max-w-4xl">
        @csrf
        @method('PUT')
        <div class="admin-card-header">
            <h2 class="admin-card-title">Roasting details</h2>
        </div>
        <div class="admin-card-body">
            @include('admin.roastings._form')
            <x-admin.form-actions :cancel-route="route('admin.roastings.show', $roasting)" submit-label="Update" />
        </div>
    </form>
@endsection
