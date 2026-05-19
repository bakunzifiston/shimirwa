@props(['class' => 'admin-sidebar-logo'])

@php
    $logo = config('admin.logo');
    $logoUrl = $logo && str_starts_with($logo, 'http')
        ? $logo
        : ($logo ? asset($logo) : null);
@endphp

@if ($logoUrl)
    <span {{ $attributes->merge(['class' => $class]) }}>
        <img src="{{ $logoUrl }}" alt="{{ config('admin.name') }} logo" width="40" height="40">
    </span>
@else
    <span {{ $attributes->merge(['class' => $class . ' admin-auth-logo--text']) }}>
        {{ strtoupper(substr(config('admin.name'), 0, 2)) }}
    </span>
@endif
