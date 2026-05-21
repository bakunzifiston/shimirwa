@extends('layouts.admin')

@section('title', 'Edit product')
@section('page_title', 'Edit product')
@section('page_subtitle', $product->name)

@section('content')
    <form method="POST" action="{{ route('admin.products.update', $product) }}" enctype="multipart/form-data" class="admin-card admin-card--form max-w-4xl">
        @csrf
        @method('PUT')
        <div class="admin-card-header">
            <h2 class="admin-card-title">Edit product</h2>
            <span class="admin-badge {{ $product->status === 'active' ? 'admin-badge--primary' : '' }}">
                {{ $product->status === 'active' ? 'Active' : 'Inactive' }}
            </span>
        </div>
        <div class="admin-card-body">
            @include('admin.products._form', ['product' => $product])
            <x-admin.form-actions :cancel-route="route('admin.products.show', $product)" submit-label="Update product" />
        </div>
    </form>
@endsection
