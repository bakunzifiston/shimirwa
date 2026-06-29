@extends('layouts.admin')

@section('title', 'Edit — '.$event->title)
@section('page_title', 'Edit Event')
@section('page_subtitle', $event->title)

@section('content')
    <form method="POST" action="{{ route('admin.events.update', $event) }}" enctype="multipart/form-data">
        @csrf @method('PUT')
        @include('admin.events._form')
        <div class="admin-form-actions">
            <button type="submit" class="admin-btn admin-btn-primary">Update event</button>
            <a href="{{ route('admin.events.show', $event) }}" class="admin-btn admin-btn-ghost">Cancel</a>
        </div>
    </form>
@endsection
