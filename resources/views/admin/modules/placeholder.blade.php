@extends('layouts.admin')

@section('title', $title)
@section('page_title', $title)

@section('content')
    <div class="admin-card max-w-2xl p-8">
        <h2 class="text-lg font-semibold text-slate-900">Module migration in progress</h2>
        <p class="mt-3 text-slate-600">{{ $description }}</p>
        @if ($filamentResource ?? false)
            <p class="mt-2 text-sm text-slate-500">Reference: <code>app/Filament/Resources/{{ $filamentResource }}.php</code></p>
        @endif
        <p class="mt-6 text-sm text-slate-600">
            Business logic is unchanged in <code>app/Models</code>. This screen will be replaced with Blade forms and controllers
            following the same pattern as Employees and Clients.
        </p>
        <a href="{{ route('admin.dashboard') }}" class="mt-6 inline-block text-sm font-medium text-[#10498C]">← Back to dashboard</a>
    </div>
@endsection
