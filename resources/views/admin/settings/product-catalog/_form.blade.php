@php
    $sel = $item ?? new \App\Models\ProductCatalog;
    $selCat = old('category', $sel->category ?? 'production');
@endphp

<div class="admin-form-grid" id="catalog-form" data-sub-categories='@json($subCategories)'>

    <div class="md:col-span-2">
        <label class="admin-label" for="name">Item name</label>
        <input id="name" name="name" class="admin-input"
               value="{{ old('name', $sel->name) }}" required placeholder="e.g. Maize, 5kg Bag, Shimirwa Flour 1kg">
    </div>

    <div>
        <label class="admin-label" for="category">Category</label>
        <select id="category" name="category" class="admin-input" required>
            @foreach ($categories as $val => $label)
                <option value="{{ $val }}" @selected($selCat === $val)>{{ $label }}</option>
            @endforeach
        </select>
        <p class="mt-1 text-xs" style="color:var(--admin-text-muted)">
            <strong>Production</strong> = used in factory (raw material, packaging). <strong>E-commerce</strong> = sold in shop.
        </p>
    </div>

    <div>
        <label class="admin-label" for="sub_category">Sub-category</label>
        <select id="sub_category" name="sub_category" class="admin-input">
            <option value="">— none —</option>
        </select>
    </div>

    <div>
        <label class="admin-label" for="unit">Unit</label>
        <select id="unit" name="unit" class="admin-input" required>
            @foreach ($units as $u)
                <option value="{{ $u }}" @selected(old('unit', $sel->unit ?? 'kg') === $u)>{{ $u }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="admin-label" for="sort_order">Sort order</label>
        <input id="sort_order" name="sort_order" type="number" min="0" class="admin-input"
               value="{{ old('sort_order', $sel->sort_order ?? 0) }}">
        <p class="mt-1 text-xs" style="color:var(--admin-text-muted)">Lower = appears first in dropdowns.</p>
    </div>

    <div class="md:col-span-2">
        <label class="admin-label" for="description">Description</label>
        <textarea id="description" name="description" rows="2" class="admin-input"
                  placeholder="Optional notes">{{ old('description', $sel->description) }}</textarea>
    </div>

    <div class="md:col-span-2" id="process-flags-wrap">
        <label class="admin-label">Process flags</label>
        <p class="mb-2 text-xs" style="color:var(--admin-text-muted)">
            Controls which production operations this item appears in.
        </p>
        <div class="flex flex-wrap gap-6">
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="hidden" name="requires_sorting" value="0">
                <input type="checkbox" name="requires_sorting" value="1" class="h-4 w-4 rounded"
                       @checked(old('requires_sorting', $sel->requires_sorting ?? false))>
                <div>
                    <span class="text-sm font-medium">Requires sorting</span>
                    <p class="text-xs" style="color:var(--admin-text-muted)">Batches of this item appear in the Sorting form</p>
                </div>
            </label>
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="hidden" name="requires_roasting" value="0">
                <input type="checkbox" name="requires_roasting" value="1" class="h-4 w-4 rounded"
                       @checked(old('requires_roasting', $sel->requires_roasting ?? false))>
                <div>
                    <span class="text-sm font-medium">Requires roasting</span>
                    <p class="text-xs" style="color:var(--admin-text-muted)">Batches of this item appear in the Roasting form</p>
                </div>
            </label>
        </div>
    </div>

    <div>
        <label class="admin-label">Status</label>
        <label class="flex items-center gap-2 cursor-pointer mt-1">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" value="1" class="h-4 w-4 rounded"
                   @checked(old('is_active', $sel->is_active ?? true))>
            <span class="text-sm">Active (visible in dropdowns)</span>
        </label>
    </div>

</div>

<script>
(function () {
    const form       = document.getElementById('catalog-form');
    if (!form) return;
    const subCats    = JSON.parse(form.dataset.subCategories || '{}');
    const catEl      = form.querySelector('#category');
    const subEl      = form.querySelector('#sub_category');
    const flagsWrap  = form.querySelector('#process-flags-wrap');
    const initialSub = @json(old('sub_category', $sel->sub_category ?? ''));

    // Sub-categories where production flags don't apply
    const noFlagsSubs = ['Packaging Material'];

    function syncSub() {
        const opts = subCats[catEl.value] || [];
        subEl.innerHTML = '<option value="">— none —</option>';
        opts.forEach(s => {
            const o = document.createElement('option');
            o.value = s; o.textContent = s;
            if (s === initialSub) o.selected = true;
            subEl.appendChild(o);
        });
        syncFlags();
    }

    function syncFlags() {
        if (!flagsWrap) return;
        const hide = noFlagsSubs.includes(subEl.value);
        flagsWrap.style.display = hide ? 'none' : '';
        // Uncheck + zero-out hidden checkboxes so they submit as 0
        if (hide) {
            flagsWrap.querySelectorAll('input[type="checkbox"]').forEach(cb => { cb.checked = false; });
        }
    }

    catEl.addEventListener('change', syncSub);
    subEl.addEventListener('change', syncFlags);
    syncSub();
})();
</script>
