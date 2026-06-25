@extends('layouts.admin')

@section('title', 'Edit catalog item')
@section('page_title', 'Edit catalog item')

@section('content')
    <div class="admin-card" style="max-width:640px">
        <div class="admin-card-header">
            <h2 class="admin-card-title">{{ $productCatalog->name }}</h2>
        </div>
        <div class="admin-card-body">
            <form method="POST" action="{{ route('admin.settings.product-catalog.update', $productCatalog) }}">
                @csrf
                @method('PUT')
                @include('admin.settings.product-catalog._form', ['item' => $productCatalog])
                <div class="admin-form-actions">
                    <button type="submit" class="admin-btn admin-btn-primary">Update item</button>
                    <a href="{{ route('admin.settings.product-catalog.show', $productCatalog) }}" class="admin-btn admin-btn-ghost">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection
