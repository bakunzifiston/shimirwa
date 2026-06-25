@extends('layouts.admin')

@section('title', 'Add catalog item')
@section('page_title', 'Add catalog item')

@section('content')
    <div class="admin-card" style="max-width:640px">
        <div class="admin-card-header">
            <h2 class="admin-card-title">New item</h2>
        </div>
        <div class="admin-card-body">
            <form method="POST" action="{{ route('admin.settings.product-catalog.store') }}">
                @csrf
                @include('admin.settings.product-catalog._form')
                <div class="admin-form-actions">
                    <button type="submit" class="admin-btn admin-btn-primary">Save item</button>
                    <a href="{{ route('admin.settings.product-catalog.index') }}" class="admin-btn admin-btn-ghost">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection
