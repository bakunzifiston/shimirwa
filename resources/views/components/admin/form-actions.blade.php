@props(['cancelRoute', 'submitLabel' => 'Save'])

<div {{ $attributes->merge(['class' => 'admin-form-actions']) }}>
    <button type="submit" class="admin-btn admin-btn-primary">
        {{ $submitLabel }}
    </button>
    <a href="{{ $cancelRoute }}" class="admin-btn admin-btn-secondary">Cancel</a>
    {{ $slot }}
</div>
