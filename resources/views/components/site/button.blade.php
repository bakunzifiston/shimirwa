@props([
    'href' => null,
    'type' => 'button',
    'variant' => 'primary',
    'size' => '',
])

@php
    $href = $href ?? $attributes->get('href');
    $classes = trim("site-btn site-btn-{$variant} {$size} " . ($attributes->get('class') ?? ''));
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->except('href')->merge(['class' => $classes]) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</button>
@endif
