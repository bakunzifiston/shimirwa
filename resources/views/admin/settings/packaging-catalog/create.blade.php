@extends('layouts.admin')
@section('title', 'Add packaging type')
@section('page_title', 'Add packaging type')

@section('content')
    <div class="admin-card" style="max-width:560px">
        <div class="admin-card-header">
            <h2 class="admin-card-title">New packaging type</h2>
        </div>
        <div class="admin-card-body">
            <form method="POST" action="{{ route('admin.settings.packaging-catalog.store') }}">
                @csrf
                @include('admin.settings.packaging-catalog._form')
                <div class="admin-form-actions">
                    <button type="submit" class="admin-btn admin-btn-primary">Save type</button>
                    <a href="{{ route('admin.settings.packaging-catalog.index') }}" class="admin-btn admin-btn-ghost">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection
