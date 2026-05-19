@props([
    'colspan' => 6,
    'title' => 'No records found',
    'message' => 'Try adjusting your search or add a new record.',
])

<tr>
    <td colspan="{{ $colspan }}" class="!p-0">
        <div class="admin-empty">
            <x-admin.icon name="box" class="admin-empty-icon !mx-auto !h-12 !w-12" />
            <p class="admin-empty-title">{{ $title }}</p>
            <p class="admin-empty-text">{{ $message }}</p>
            @if (isset($action))
                <div class="mt-4">{{ $action }}</div>
            @endif
        </div>
    </td>
</tr>
