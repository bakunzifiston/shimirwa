@extends('layouts.admin')

@section('title', 'New Training Module')
@section('page_title', 'New Training Module')
@section('page_subtitle', 'Create a new training resource for the public website')

@section('content')
    <form method="POST" action="{{ route('admin.trainings.store') }}" enctype="multipart/form-data">
        @csrf

        @include('admin.trainings._form')

        <div class="admin-form-actions">
            <button type="submit" class="admin-btn admin-btn-primary">Save module</button>
            <a href="{{ route('admin.trainings.index') }}" class="admin-btn admin-btn-ghost">Cancel</a>
        </div>
    </form>
@endsection
