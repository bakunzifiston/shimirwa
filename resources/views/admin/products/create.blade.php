@extends('layouts.admin')

@section('title', 'Add product')
@section('page_title', 'Add product')
@section('page_subtitle', 'Create a new shop product')

@section('content')
    <form method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data" class="admin-card admin-card--form max-w-4xl">
        @csrf
        <div class="admin-card-header">
            <h2 class="admin-card-title">Product details</h2>
        </div>
        <div class="admin-card-body">
            @include('admin.products._form', ['product' => $product])
            <x-admin.form-actions :cancel-route="route('admin.products.index')" submit-label="Save product" />
        </div>
    </form>
@endsection
