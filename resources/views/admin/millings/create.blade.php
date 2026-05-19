@extends('layouts.admin')

@section('title', 'Add milling')
@section('page_title', 'Add milling')
@section('page_subtitle', 'Milling batches and flour output')

@section('content')
    <form method="POST" action="{{ route('admin.millings.store') }}" class="admin-card max-w-4xl">
        @csrf
        <div class="admin-card-header">
            <h2 class="admin-card-title">Milling details</h2>
        </div>
        <div class="admin-card-body">
            @include('admin.millings._form')
            <x-admin.form-actions :cancel-route="route('admin.millings.index')" submit-label="Save" />
        </div>
    </form>
@endsection
