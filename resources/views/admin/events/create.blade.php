@extends('layouts.admin')

@section('title', 'New Event')
@section('page_title', 'New Event')
@section('page_subtitle', 'Create an event post with photos and videos')

@section('content')
    <form method="POST" action="{{ route('admin.events.store') }}" enctype="multipart/form-data">
        @csrf
        @include('admin.events._form')
        <div class="admin-form-actions">
            <button type="submit" class="admin-btn admin-btn-primary">Save event</button>
            <a href="{{ route('admin.events.index') }}" class="admin-btn admin-btn-ghost">Cancel</a>
        </div>
    </form>
@endsection
