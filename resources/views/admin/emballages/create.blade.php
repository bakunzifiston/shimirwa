@extends('layouts.admin')

@section('title', 'Add emballage')
@section('page_title', 'Add emballage')
@section('page_subtitle', 'Packaging and emballage records')

@section('content')
    <form method="POST" action="{{ route('admin.emballages.store') }}" class="admin-card max-w-4xl">
        @csrf
        <div class="admin-card-header">
            <h2 class="admin-card-title">Packaging details</h2>
        </div>
        <div class="admin-card-body">
            @include('admin.emballages._form')
            <x-admin.form-actions :cancel-route="route('admin.emballages.index')" submit-label="Save" />
        </div>
    </form>
@endsection
