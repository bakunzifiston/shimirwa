@php $sel = $item ?? new \App\Models\PackagingCatalog; @endphp

<div class="admin-form-grid">

    <div class="md:col-span-2">
        <label class="admin-label" for="name">Packaging type name</label>
        <input id="name" name="name" class="admin-input"
               value="{{ old('name', $sel->name) }}" required
               placeholder="e.g. 1kg package, 5kg sack, 25kg bag">
    </div>

    <div>
        <label class="admin-label" for="kg_per_unit">Flour per unit (kg)</label>
        <input id="kg_per_unit" name="kg_per_unit" type="number" step="0.001" min="0"
               class="admin-input"
               value="{{ old('kg_per_unit', $sel->kg_per_unit ?? '') }}" required>
        <p class="mt-1 text-xs" style="color:var(--admin-text-muted)">
            How many kg of flour one package holds. Set to 0 if using manual weight.
        </p>
    </div>

    <div>
        <label class="admin-label" for="sort_order">Sort order</label>
        <input id="sort_order" name="sort_order" type="number" min="0" class="admin-input"
               value="{{ old('sort_order', $sel->sort_order ?? 0) }}">
        <p class="mt-1 text-xs" style="color:var(--admin-text-muted)">Lower = appears first in dropdowns.</p>
    </div>

    <div class="md:col-span-2">
        <label class="admin-label" for="description">Description (optional)</label>
        <input id="description" name="description" class="admin-input"
               value="{{ old('description', $sel->description) }}"
               placeholder="e.g. Standard retail 1kg bag">
    </div>

    <div class="md:col-span-2 flex flex-wrap gap-6">
        <label class="flex items-center gap-2 cursor-pointer">
            <input type="hidden" name="manual_weight" value="0">
            <input type="checkbox" name="manual_weight" value="1" class="h-4 w-4 rounded"
                   @checked(old('manual_weight', $sel->manual_weight ?? false))>
            <div>
                <span class="text-sm font-medium">Manual weight entry</span>
                <p class="text-xs" style="color:var(--admin-text-muted)">User types flour kg manually (e.g. for bulk sacks)</p>
            </div>
        </label>
        <label class="flex items-center gap-2 cursor-pointer">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" value="1" class="h-4 w-4 rounded"
                   @checked(old('is_active', $sel->is_active ?? true))>
            <div>
                <span class="text-sm font-medium">Active</span>
                <p class="text-xs" style="color:var(--admin-text-muted)">Visible in packaging form dropdowns</p>
            </div>
        </label>
    </div>

</div>
