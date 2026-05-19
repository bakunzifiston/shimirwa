@extends('layouts.admin')

@section('title', 'User')
@section('page_title', 'User')

@section('header_actions')
    <a href="{{ route('admin.users.edit', $user) }}" class="admin-btn-primary rounded-md px-4 py-2 text-sm no-underline">Edit</a>
@endsection

@section('content')
    <div class="admin-card max-w-xl p-6">
        <dl class="grid gap-4">
            <div><dt class="text-sm text-slate-500">Name</dt><dd class="font-medium">{{ $user->name }}</dd></div>
            <div><dt class="text-sm text-slate-500">Email</dt><dd>{{ $user->email }}</dd></div>
            <div><dt class="text-sm text-slate-500">Created</dt><dd>{{ $user->created_at?->format('Y-m-d H:i') }}</dd></div>
        </dl>

        @if($user->id !== auth()->id())
            <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="mt-8 border-t pt-6" onsubmit="return confirm('Delete this user?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="rounded-md bg-red-600 px-4 py-2 text-sm text-white">Delete</button>
            </form>
        @endif
    </div>
@endsection
