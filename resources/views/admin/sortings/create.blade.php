@extends('layouts.admin')

@section('title', 'Add sorting')
@section('page_title', 'Add sorting')
@section('page_subtitle', 'Sorting production records')

@section('content')
    <form method="POST" action="{{ route('admin.sortings.store') }}" class="admin-card max-w-4xl">
        @csrf
        <div class="admin-card-header">
            <h2 class="admin-card-title">Sorting details</h2>
        </div>
        <div class="admin-card-body">
            @include('admin.sortings._form')
            <x-admin.form-actions :cancel-route="route('admin.sortings.index')" submit-label="Save" />
        </div>
    </form>
@endsection
