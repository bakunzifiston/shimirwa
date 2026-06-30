@extends('layouts.admin')

@section('title', 'Edit — '.$training->title)
@section('page_title', 'Edit Training Module')
@section('page_subtitle', $training->title)

@section('content')
    <form method="POST" action="{{ route('admin.trainings.update', $training) }}" enctype="multipart/form-data">
        @csrf @method('PUT')

        @include('admin.trainings._form')

        <div class="admin-form-actions">
            <button type="submit" class="admin-btn admin-btn-primary">Update module</button>
            <a href="{{ route('admin.trainings.show', $training) }}" class="admin-btn admin-btn-ghost">Cancel</a>
        </div>
    </form>
@endsection
