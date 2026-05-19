@extends('layouts.admin')

@section('title', 'Add roasting')
@section('page_title', 'Add roasting')
@section('page_subtitle', 'Roasting production records')

@section('content')
    <form method="POST" action="{{ route('admin.roastings.store') }}" class="admin-card max-w-4xl">
        @csrf
        <div class="admin-card-header">
            <h2 class="admin-card-title">Roasting details</h2>
        </div>
        <div class="admin-card-body">
            @include('admin.roastings._form')
            <x-admin.form-actions :cancel-route="route('admin.roastings.index')" submit-label="Save" />
        </div>
    </form>
@endsection
