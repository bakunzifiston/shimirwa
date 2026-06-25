@extends('layouts.admin')
@section('title', 'Edit — ' . $item->name)
@section('page_title', 'Edit — ' . $item->name)

@section('content')
    <div class="admin-card" style="max-width:560px">
        <div class="admin-card-header">
            <h2 class="admin-card-title">Edit packaging type</h2>
        </div>
        <div class="admin-card-body">
            <form method="POST" action="{{ route('admin.settings.packaging-catalog.update', $item) }}">
                @csrf @method('PUT')
                @include('admin.settings.packaging-catalog._form')
                <div class="admin-form-actions">
                    <button type="submit" class="admin-btn admin-btn-primary">Save changes</button>
                    <a href="{{ route('admin.settings.packaging-catalog.show', $item) }}" class="admin-btn admin-btn-ghost">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection
