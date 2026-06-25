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

    {{-- Inner units (e.g. Box contains 12 × 1kg bags) --}}
    <div class="md:col-span-2 rounded-lg border p-4 space-y-3" style="border-color:var(--admin-border);background:var(--admin-bg-elevated)">
        <div>
            <p class="text-sm font-semibold" style="color:var(--admin-text)">Inner units (optional)</p>
            <p class="text-xs mt-0.5" style="color:var(--admin-text-muted)">
                Use when this package contains smaller units that must also be deducted from stock.
                Example: a Box contains 12 × 1kg bags — selecting a 1kg bag catalog entry and 12 here
                will automatically deduct 12 bags from the bag stock whenever a box is packaged.
            </p>
        </div>
        <div class="grid gap-3 md:grid-cols-2">
            <div>
                <label class="admin-label text-xs" for="inner_unit_catalog_id">Inner unit type</label>
                <select id="inner_unit_catalog_id" name="inner_unit_catalog_id" class="admin-input">
                    <option value="">None — no inner units</option>
                    @foreach ($catalogs ?? [] as $cat)
                        <option value="{{ $cat->id }}"
                                @selected(old('inner_unit_catalog_id', $sel->inner_unit_catalog_id) == $cat->id)>
                            {{ $cat->name }}
                            @if (!$cat->manual_weight) ({{ $cat->kg_per_unit }} kg/unit) @endif
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="admin-label text-xs" for="inner_units_per_package">Units per package</label>
                <input id="inner_units_per_package" name="inner_units_per_package" type="number" min="0"
                       class="admin-input"
                       value="{{ old('inner_units_per_package', $sel->inner_units_per_package ?? 0) }}"
                       placeholder="e.g. 12">
                <p class="mt-1 text-xs" style="color:var(--admin-text-muted)">How many inner units are inside one package.</p>
            </div>
        </div>
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
