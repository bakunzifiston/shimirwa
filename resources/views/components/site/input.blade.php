@props([
    'label',
    'name',
    'type' => 'text',
    'required' => false,
    'value' => null,
])

<div class="site-field">
    <label for="{{ $name }}">{{ $label }}@if($required) <span aria-hidden="true">*</span>@endif</label>
    @if ($type === 'textarea')
        <textarea
            id="{{ $name }}"
            name="{{ $name }}"
            class="site-textarea @error($name) site-input-error @enderror"
            @if($required) required @endif
            {{ $attributes->except(['value', 'label', 'name', 'type', 'required']) }}
        >{{ old($name, $value) }}</textarea>
    @else
        <input
            type="{{ $type }}"
            id="{{ $name }}"
            name="{{ $name }}"
            value="{{ old($name, $value) }}"
            class="site-input @error($name) site-input-error @enderror"
            @if($required) required @endif
            {{ $attributes->except(['value', 'label', 'name', 'type', 'required']) }}
        >
    @endif
    @error($name)
        <p class="site-field-error" role="alert">{{ $message }}</p>
    @enderror
</div>
