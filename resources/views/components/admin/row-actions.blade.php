@props(['viewRoute' => null, 'editRoute' => null])

<div {{ $attributes->merge(['class' => 'admin-row-actions']) }}>
    @if ($viewRoute)
        <a href="{{ $viewRoute }}">
            <x-admin.icon name="eye" class="!h-3.5 !w-3.5" />
            View
        </a>
    @endif
    @if ($editRoute)
        <a href="{{ $editRoute }}">
            <x-admin.icon name="pencil" class="!h-3.5 !w-3.5" />
            Edit
        </a>
    @endif
    {{ $slot }}
</div>
