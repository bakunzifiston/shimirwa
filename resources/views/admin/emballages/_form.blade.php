@php
    $selectedCatalogId = old('packaging_catalog_id', $emballage->packaging_catalog_id ?? '');
    $selectedMillingId = old('milling_id', $emballage->milling_id ?? '');

    // Catalog meta for JS: {id: {name, kg_per_unit, manual_weight, has_inner_units, inner_units_per_package, inner_unit_name}}
    $catalogMeta = $packagingCatalogs->mapWithKeys(fn ($c) => [
        $c->id => [
            'name'                    => $c->name,
            'kg_per_unit'             => (float) $c->kg_per_unit,
            'manual_weight'           => (bool) $c->manual_weight,
            'has_inner_units'         => $c->hasInnerUnits(),
            'inner_units_per_package' => (int) $c->inner_units_per_package,
            'inner_unit_name'         => $c->innerUnitCatalog?->name ?? '',
        ]
    ]);

    // Milling meta for JS: {id: {batch_number, available, date}}
    // For edit: add back what this emballage already consumed on linked batches
    $millingMeta = $millings->mapWithKeys(function ($m) use ($emballage) {
        $avail = (float) $m->output_flour;
        // Restore primary batch (its share = total minus overflow)
        if (isset($emballage) && $emballage->exists && $emballage->milling_id == $m->id) {
            $avail += $emballage->primaryFlourKg();
        }
        // Restore overflow batches
        foreach ($emballage->milling_overflow ?? [] as $ov) {
            if (($ov['milling_id'] ?? null) == $m->id) {
                $avail += (float) ($ov['quantity'] ?? 0);
            }
        }
        return [$m->id => [
            'batch'     => $m->batch_number,
            'available' => round($avail, 4),
            'date'      => $m->date?->format('Y-m-d') ?? '',
        ]];
    });

    // All milling batches sorted oldest-first for overflow allocation
    $millingsSortedOldest = $millings->sortBy('date')->values();

    // Packaging stock meta for JS: {id: {item, batch, available, date}}
    // For edit: add back what this emballage already consumed on linked batches
    $stockMeta = $packagingStocks->mapWithKeys(function ($s) use ($emballage) {
        $avail = (float) $s->quantity_in;
        if ($emballage->exists) {
            if ($emballage->raw_material_stock_id == $s->id) {
                $avail += $emballage->primaryPackagingUnits();
            }
            foreach ($emballage->packaging_overflow ?? [] as $ov) {
                if (($ov['stock_id'] ?? null) == $s->id) {
                    $avail += (float) ($ov['units'] ?? 0);
                }
            }
        }
        return [$s->id => [
            'item'      => $s->item,
            'batch'     => $s->batch_number,
            'available' => round($avail, 2),
            'date'      => $s->date?->format('Y-m-d') ?? '',
        ]];
    });

    // Packaging stocks sorted oldest-first for overflow allocation
    $stocksSortedOldest = $packagingStocks->sortBy('date')->values();
@endphp

<div id="emballage-form" class="admin-form-grid"
     data-catalog-meta='@json($catalogMeta)'
     data-milling-meta='@json($millingMeta)'
     data-millings-ordered='@json($millingsSortedOldest->pluck("id"))'
     data-stock-meta='@json($stockMeta)'
     data-stocks-ordered='@json($stocksSortedOldest->pluck("id"))'>

    <div>
        <label class="admin-label" for="date">Date</label>
        <input type="date" id="date" name="date" class="admin-input"
               value="{{ old('date', optional($emballage->date)->format('Y-m-d') ?? date('Y-m-d')) }}" required>
    </div>
    <div>
        <label class="admin-label" for="packaging_batch_id">Packaging batch ID</label>
        <input type="text" id="packaging_batch_id" name="packaging_batch_id" class="admin-input"
               value="{{ old('packaging_batch_id', $emballage->packaging_batch_id) }}" required>
    </div>

    {{-- Packaging type + material batch side by side --}}
    @if ($packagingCatalogs->isEmpty())
        <div class="md:col-span-2">
            <div class="rounded-md border px-3 py-2 text-sm" style="background:#fff7ed;border-color:#fed7aa;color:#c2410c">
                No packaging types defined yet.
                <a href="{{ route('admin.settings.packaging-catalog.create') }}" class="underline font-medium ml-1">Add one in Settings → Packaging Catalog</a>
            </div>
            <input type="hidden" name="packaging_catalog_id" value="">
        </div>
    @else
        <div>
            <label class="admin-label" for="packaging_catalog_id">Packaging type</label>
            <select id="packaging_catalog_id" name="packaging_catalog_id" class="admin-input" required>
                <option value="">Select packaging type</option>
                @foreach ($packagingCatalogs as $cat)
                    <option value="{{ $cat->id }}" @selected($selectedCatalogId == $cat->id)>
                        {{ $cat->name }}{{ !$cat->manual_weight ? ' ('.$cat->kg_per_unit.' kg/unit)' : '' }}
                    </option>
                @endforeach
            </select>
            <p id="catalog-hint" class="mt-1 text-xs" style="color:var(--admin-text-muted)">Select a type to see per-unit flour consumption.</p>
        </div>
    @endif

    <div>
        <label class="admin-label" for="raw_material_stock_id">Packaging material batch</label>
        <select id="raw_material_stock_id" name="raw_material_stock_id" class="admin-input">
            <option value="">Select batch (optional)</option>
            @foreach ($packagingStocks as $stock)
                <option value="{{ $stock->id }}"
                        data-item="{{ $stock->item }}"
                        @selected(old('raw_material_stock_id', $emballage->raw_material_stock_id) == $stock->id)>
                    {{ $stock->item }} — {{ $stock->batch_number }} ({{ number_format($stockMeta[$stock->id]['available'] ?? $stock->quantity_in) }} left)
                </option>
            @endforeach
        </select>
        <p id="stock-hint" class="mt-1 text-xs" style="color:var(--admin-text-muted)"></p>
    </div>

    {{-- Packaging material overflow: shown when units > selected batch available --}}
    <div class="md:col-span-2" id="stock-overflow-wrap" style="display:none">
        <div class="rounded-lg border overflow-hidden" style="border-color:#bfdbfe">
            <div class="px-3 py-2 text-xs font-semibold" style="background:#dbeafe;color:#1e40af">
                Units exceed this packaging batch — also taking from:
            </div>
            <div id="stock-overflow-rows" class="divide-y" style="border-color:#bfdbfe"></div>
        </div>
    </div>

    <div class="md:col-span-2" id="stock-overflow-error-wrap" style="display:none">
        <p id="stock-overflow-error-msg" class="text-xs font-medium rounded p-2"
           style="background:#fef2f2;color:#dc2626;border:1px solid #fecaca"></p>
    </div>

    {{-- Hidden packaging overflow inputs (populated by JS) --}}
    <div id="stock-overflow-inputs" class="md:col-span-2" style="display:none"></div>

    {{-- Inner unit batch (e.g. bags inside a box) — shown only when catalog hasInnerUnits() --}}
    <div id="inner-stock-wrap" class="md:col-span-2" style="display:none">
        <label class="admin-label" for="inner_stock_id">Inner unit batch</label>
        <select id="inner_stock_id" name="inner_stock_id" class="admin-input">
            <option value="">Select batch</option>
            @foreach ($innerStocks as $stock)
                <option value="{{ $stock->id }}"
                        data-item="{{ $stock->item }}"
                        @selected(old('inner_stock_id', $emballage->inner_stock_id ?? '') == $stock->id)>
                    {{ $stock->item }} — {{ $stock->batch_number }} ({{ number_format($stock->quantity_in) }} units left)
                </option>
            @endforeach
        </select>
        <p id="inner-stock-hint" class="mt-1 text-xs" style="color:var(--admin-text-muted)"></p>
    </div>

    {{-- Primary milling batch --}}
    <div class="md:col-span-2">
        <label class="admin-label" for="milling_id">Milling batch (flour source)</label>
        <select id="milling_id" name="milling_id" class="admin-input" required>
            <option value="">Select milling batch</option>
            @foreach ($millings as $m)
                <option value="{{ $m->id }}" @selected(old('milling_id', $selectedMillingId) == $m->id)>
                    {{ $m->batch_number }} — {{ number_format($m->output_flour, 1) }} kg available
                </option>
            @endforeach
        </select>
        <p id="milling-hint" class="mt-1 text-xs" style="color:var(--admin-text-muted)"></p>
    </div>

    {{-- Overflow panel: shown when quantity > selected batch available --}}
    <div class="md:col-span-2" id="overflow-wrap" style="display:none">
        <div class="rounded-lg border overflow-hidden" style="border-color:#fde68a">
            <div class="px-3 py-2 text-xs font-semibold" style="background:#fef9c3;color:#854d0e">
                Quantity exceeds this batch — also drawing from:
            </div>
            <div id="overflow-rows" class="divide-y" style="border-color:#fde68a"></div>
        </div>
    </div>

    {{-- Not enough stock error --}}
    <div class="md:col-span-2" id="overflow-error-wrap" style="display:none">
        <p id="overflow-error-msg" class="text-xs font-medium rounded p-2"
           style="background:#fef2f2;color:#dc2626;border:1px solid #fecaca"></p>
    </div>

    {{-- Hidden overflow inputs (populated by JS) --}}
    <div id="overflow-inputs" class="md:col-span-2" style="display:none"></div>

    <div>
        <label class="admin-label" for="item" id="item-label">Number of units</label>
        <input type="number" step="1" min="1" id="item" name="item" class="admin-input"
               value="{{ old('item', $emballage->item) }}" required>
    </div>

    <div>
        <label class="admin-label" for="quantity">Flour quantity (kg)</label>
        <input type="number" step="0.001" min="0" id="quantity" name="quantity" class="admin-input"
               value="{{ old('quantity', $emballage->quantity) }}" required>
        <p class="mt-1 text-xs" id="qty-hint" style="color:var(--admin-text-muted)">Auto-calculated from type × units. Editable for manual weight types.</p>
    </div>

    <div>
        <label class="admin-label" for="damaged">Damaged units</label>
        <input type="number" step="1" min="0" id="damaged" name="damaged" class="admin-input"
               value="{{ old('damaged', $emballage->damaged ?? 0) }}">
    </div>
    <div>
        <label class="admin-label" for="unit_price">Unit price (RWF)</label>
        <input type="number" step="0.01" min="0" id="unit_price" name="unit_price" class="admin-input"
               value="{{ old('unit_price', $emballage->unit_price) }}">
    </div>
    <div>
        <label class="admin-label" for="total_price">Total price (RWF)</label>
        <input type="number" step="0.01" min="0" id="total_price" name="total_price" class="admin-input"
               value="{{ old('total_price', $emballage->total_price) }}">
    </div>
    <div>
        <label class="admin-label" for="expiry_date">Expiry date</label>
        <input type="date" id="expiry_date" name="expiry_date" class="admin-input"
               value="{{ old('expiry_date', optional($emballage->expiry_date)->format('Y-m-d')) }}">
    </div>
    <div>
        <label class="admin-label" for="employee_id">Employee</label>
        <select id="employee_id" name="employee_id" class="admin-input" required>
            <option value="">Select employee</option>
            @foreach ($employees as $employee)
                <option value="{{ $employee->id }}" @selected(old('employee_id', $emballage->employee_id) == $employee->id)>
                    {{ $employee->full_name }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="md:col-span-2">
        <label class="admin-label" for="comment">Comment</label>
        <input type="text" id="comment" name="comment" class="admin-input"
               value="{{ old('comment', $emballage->comment) }}">
    </div>
</div>

<script>
(function () {
    const form          = document.getElementById('emballage-form');
    if (!form) return;

    const catalogMeta    = JSON.parse(form.dataset.catalogMeta    || '{}');
    const millingMeta    = JSON.parse(form.dataset.millingMeta    || '{}');
    const millingsOrdered = JSON.parse(form.dataset.millingsOrdered || '[]'); // oldest-first ids
    const stockMeta      = JSON.parse(form.dataset.stockMeta      || '{}');
    const stocksOrdered  = JSON.parse(form.dataset.stocksOrdered  || '[]'); // oldest-first ids

    const catalogEl       = form.querySelector('#packaging_catalog_id');
    const millingEl       = form.querySelector('#milling_id');
    const itemEl          = form.querySelector('#item');
    const qtyEl           = form.querySelector('#quantity');
    const catalogHint     = form.querySelector('#catalog-hint');
    const qtyHint         = form.querySelector('#qty-hint');
    const millingHint     = form.querySelector('#milling-hint');
    const itemLabel       = form.querySelector('#item-label');
    const unitPriceEl     = form.querySelector('#unit_price');
    const totalPriceEl    = form.querySelector('#total_price');
    const overflowWrap    = form.querySelector('#overflow-wrap');
    const overflowRows    = form.querySelector('#overflow-rows');
    const overflowErrWrap = form.querySelector('#overflow-error-wrap');
    const overflowErrMsg  = form.querySelector('#overflow-error-msg');
    const overflowInputs  = form.querySelector('#overflow-inputs');
    const innerStockWrap  = form.querySelector('#inner-stock-wrap');
    const innerStockHint  = form.querySelector('#inner-stock-hint');
    const stockEl         = form.querySelector('#raw_material_stock_id');
    const stockHint       = form.querySelector('#stock-hint');
    const stockOvWrap     = form.querySelector('#stock-overflow-wrap');
    const stockOvRows     = form.querySelector('#stock-overflow-rows');
    const stockOvErrWrap  = form.querySelector('#stock-overflow-error-wrap');
    const stockOvErrMsg   = form.querySelector('#stock-overflow-error-msg');
    const stockOvInputs   = form.querySelector('#stock-overflow-inputs');

    if (!catalogEl) return;

    function getCatalog() { return catalogMeta[catalogEl?.value] || null; }
    function getMillingMeta(id) { return millingMeta[id] || null; }

    // Total flour available across all milling batches (ordered oldest-first)
    function totalFlourAvail() {
        return millingsOrdered.reduce((sum, id) => {
            return sum + (millingMeta[id]?.available ?? 0);
        }, 0);
    }

    // Compute overflow allocations from startId onward (oldest-first order)
    function computeAlloc(startId, qty) {
        const startIdx = millingsOrdered.findIndex(id => String(id) === String(startId));
        const ordered  = startIdx >= 0
            ? [...millingsOrdered.slice(startIdx), ...millingsOrdered.slice(0, startIdx)]
            : millingsOrdered;

        let rem = qty;
        const alloc = [];
        for (const id of ordered) {
            if (rem <= 0) break;
            const m = millingMeta[id];
            if (!m || m.available <= 0) continue;
            const take = Math.min(m.available, rem);
            alloc.push({ id, batch: m.batch, date: m.date, take: Math.round(take * 1000) / 1000 });
            rem = Math.round((rem - take) * 1000) / 1000;
        }
        return { alloc, unmet: Math.max(rem, 0) };
    }

    function renderOverflow() {
        const millingId = millingEl?.value;
        const qty       = parseFloat(qtyEl.value || 0);

        overflowWrap.style.display    = 'none';
        overflowErrWrap.style.display = 'none';
        overflowInputs.innerHTML      = '';
        if (millingHint) { millingHint.textContent = ''; millingHint.style.color = ''; }

        if (!millingId) return;

        const m      = getMillingMeta(millingId);
        const avail  = m?.available ?? 0;
        const total  = totalFlourAvail();

        if (millingHint) {
            millingHint.textContent = avail < total
                ? `This batch: ${avail.toFixed(2)} kg · All batches: ${total.toFixed(2)} kg`
                : `Available: ${avail.toFixed(2)} kg`;
            millingHint.style.color = 'var(--admin-text-muted)';
        }

        if (qty <= 0) return;

        if (qty > total) {
            overflowErrWrap.style.display = '';
            overflowErrMsg.textContent = `Not enough flour. Total available across all batches: ${total.toFixed(2)} kg.`;
            return;
        }

        const { alloc, unmet } = computeAlloc(millingId, qty);

        if (unmet > 0) {
            overflowErrWrap.style.display = '';
            overflowErrMsg.textContent = `Not enough flour (${unmet.toFixed(3)} kg short).`;
            return;
        }

        // Show overflow panel if more than one batch used
        const extra = alloc.slice(1);
        if (extra.length > 0) {
            overflowWrap.style.display = '';
            overflowRows.innerHTML = extra.map(a =>
                `<div class="px-3 py-2 flex items-center justify-between text-sm" style="background:#fffbeb">
                    <div>
                        <span class="font-mono text-xs font-semibold">${a.batch}</span>
                        <span class="ml-2 text-xs" style="color:#78716c">${a.date}</span>
                    </div>
                    <span class="font-semibold" style="color:#92400e">${a.take.toFixed(3)} kg</span>
                </div>`
            ).join('');

            // Hidden inputs for overflow
            extra.forEach((a, j) => {
                overflowInputs.innerHTML +=
                    `<input type="hidden" name="milling_overflow[${j}][milling_id]" value="${a.id}">` +
                    `<input type="hidden" name="milling_overflow[${j}][quantity]" value="${a.take}">`;
            });
        }

        // Update primary batch hint
        if (millingHint && alloc[0]) {
            const primaryTake = alloc[0].take;
            millingHint.textContent = extra.length > 0
                ? `Drawing ${primaryTake.toFixed(3)} kg here + ${extra.length} more batch(es)`
                : `Using ${primaryTake.toFixed(3)} kg from this batch`;
            millingHint.style.color = 'var(--admin-primary, #10498C)';
        }
    }

    // --- Packaging material batch: filter by type + multi-batch overflow ---

    // Show only batches matching the selected packaging type name (fall back to all if none match)
    function filterStockOptions() {
        if (!stockEl) return;
        const cat = getCatalog();
        const matchName = cat ? (cat.name || '').toLowerCase() : '';
        const opts = Array.from(stockEl.options).filter(o => o.value);

        let matches = 0;
        opts.forEach(opt => {
            const itemName = (opt.dataset.item || '').toLowerCase();
            if (!matchName || itemName.includes(matchName) || matchName.includes(itemName)) matches++;
        });

        opts.forEach(opt => {
            const itemName = (opt.dataset.item || '').toLowerCase();
            const visible  = !matchName || matches === 0
                || itemName.includes(matchName) || matchName.includes(itemName);
            opt.hidden   = !visible;
            opt.disabled = !visible;
        });

        if (stockEl.value && stockEl.options[stockEl.selectedIndex]?.hidden) {
            stockEl.value = '';
        }
    }

    // Batches holding the same item as the selected batch, oldest-first
    function sameItemStocks(primaryId) {
        const primaryItem = (stockMeta[primaryId]?.item || '').toLowerCase();
        return stocksOrdered.filter(id => (stockMeta[id]?.item || '').toLowerCase() === primaryItem);
    }

    function totalStockAvail(primaryId) {
        return sameItemStocks(primaryId).reduce((sum, id) => sum + (stockMeta[id]?.available ?? 0), 0);
    }

    // Allocate units: selected batch first, then other same-item batches oldest-first
    function computeStockAlloc(startId, units) {
        const pool    = sameItemStocks(startId);
        const ordered = [startId, ...pool.filter(id => String(id) !== String(startId))];

        let rem = units;
        const alloc = [];
        for (const id of ordered) {
            if (rem <= 0) break;
            const s = stockMeta[id];
            if (!s || s.available <= 0) continue;
            const take = Math.min(s.available, rem);
            alloc.push({ id, batch: s.batch, date: s.date, take: Math.round(take) });
            rem -= take;
        }
        return { alloc, unmet: Math.max(rem, 0) };
    }

    function renderStockOverflow() {
        if (!stockEl) return;
        const stockId = stockEl.value;
        const units   = parseFloat(itemEl.value || 0);

        stockOvWrap.style.display    = 'none';
        stockOvErrWrap.style.display = 'none';
        stockOvInputs.innerHTML      = '';
        if (stockHint) { stockHint.textContent = ''; stockHint.style.color = ''; }

        if (!stockId) return;

        const s     = stockMeta[stockId];
        const avail = s?.available ?? 0;
        const total = totalStockAvail(stockId);

        if (stockHint) {
            stockHint.textContent = avail < total
                ? `This batch: ${avail.toLocaleString()} units · All ${s?.item || ''} batches: ${total.toLocaleString()} units`
                : `Available: ${avail.toLocaleString()} units`;
            stockHint.style.color = 'var(--admin-text-muted)';
        }

        if (units <= 0) return;

        if (units > total) {
            stockOvErrWrap.style.display = '';
            stockOvErrMsg.textContent = `Not enough packaging material. Total available across all ${s?.item || ''} batches: ${total.toLocaleString()} units (need ${units.toLocaleString()}).`;
            return;
        }

        const { alloc, unmet } = computeStockAlloc(stockId, units);

        if (unmet > 0) {
            stockOvErrWrap.style.display = '';
            stockOvErrMsg.textContent = `Not enough packaging material (${unmet.toLocaleString()} units short).`;
            return;
        }

        const extra = alloc.slice(1);
        if (extra.length > 0) {
            stockOvWrap.style.display = '';
            stockOvRows.innerHTML = extra.map(a =>
                `<div class="px-3 py-2 flex items-center justify-between text-sm" style="background:#eff6ff">
                    <div>
                        <span class="font-mono text-xs font-semibold">${a.batch}</span>
                        <span class="ml-2 text-xs" style="color:#64748b">${a.date}</span>
                    </div>
                    <span class="font-semibold" style="color:#1e40af">${a.take.toLocaleString()} units</span>
                </div>`
            ).join('');

            extra.forEach((a, j) => {
                stockOvInputs.innerHTML +=
                    `<input type="hidden" name="packaging_overflow[${j}][stock_id]" value="${a.id}">` +
                    `<input type="hidden" name="packaging_overflow[${j}][units]" value="${a.take}">`;
            });
        }

        if (stockHint && alloc[0]) {
            stockHint.textContent = extra.length > 0
                ? `Taking ${alloc[0].take.toLocaleString()} units here + ${extra.length} more batch(es)`
                : `Using ${alloc[0].take.toLocaleString()} of ${avail.toLocaleString()} units in this batch`;
            stockHint.style.color = 'var(--admin-primary, #10498C)';
        }
    }

    function syncInnerUnits() {
        const cat   = getCatalog();
        const units = parseFloat(itemEl.value || 0);
        const innerSel = form.querySelector('#inner_stock_id');

        if (cat && cat.has_inner_units) {
            innerStockWrap.style.display = '';

            // Filter options to only show batches matching the inner unit name
            const matchName = (cat.inner_unit_name || '').toLowerCase();
            let visibleCount = 0;
            if (innerSel) {
                Array.from(innerSel.options).forEach(opt => {
                    if (!opt.value) return; // keep the placeholder
                    const itemName = (opt.dataset.item || '').toLowerCase();
                    const visible  = !matchName || itemName.includes(matchName) || matchName.includes(itemName);
                    opt.hidden   = !visible;
                    opt.disabled = !visible;
                    if (visible) visibleCount++;
                });
                // Deselect current value if it's now hidden
                if (innerSel.value && innerSel.options[innerSel.selectedIndex]?.hidden) {
                    innerSel.value = '';
                }
            }

            const totalInner = units * cat.inner_units_per_package;
            if (innerStockHint) {
                innerStockHint.textContent = units > 0
                    ? `${units} × ${cat.inner_units_per_package} = ${totalInner} ${cat.inner_unit_name || 'inner units'} will be deducted`
                    : `${cat.inner_units_per_package} × ${cat.inner_unit_name || 'inner unit'} per package`;
                innerStockHint.style.color = 'var(--admin-primary, #10498C)';
            }
        } else {
            innerStockWrap.style.display = 'none';
            // Reset all options visibility when no inner units
            if (innerSel) {
                Array.from(innerSel.options).forEach(opt => { opt.hidden = false; opt.disabled = false; });
            }
            if (innerStockHint) { innerStockHint.textContent = ''; }
        }
    }

    function sync() {
        const cat = getCatalog();
        if (!cat) {
            if (catalogHint) { catalogHint.textContent = 'Select a type to see per-unit flour consumption.'; catalogHint.style.color = ''; }
            qtyEl.readOnly = false;
            syncInnerUnits();
            filterStockOptions();
            renderStockOverflow();
            renderOverflow();
            return;
        }

        if (catalogHint) {
            catalogHint.textContent = cat.manual_weight
                ? 'Manual weight — enter flour kg directly.'
                : `${cat.kg_per_unit} kg of flour per unit.`;
            catalogHint.style.color = 'var(--admin-primary, #10498C)';
        }

        itemLabel.textContent = `Number of ${cat.name}s`;

        if (cat.manual_weight) {
            qtyEl.readOnly = false;
            qtyEl.style.background = '';
            qtyHint.textContent = 'Enter flour weight manually.';
            qtyHint.style.color = '';
        } else {
            qtyEl.readOnly = true;
            qtyEl.style.background = 'var(--admin-bg)';
            const units    = parseFloat(itemEl.value || 0);
            const computed = parseFloat((units * cat.kg_per_unit).toFixed(3));
            qtyEl.value    = computed || '';

            qtyHint.style.color = 'var(--admin-primary, #10498C)';
            qtyHint.textContent = units > 0
                ? `Auto: ${units} × ${cat.kg_per_unit} kg = ${computed} kg`
                : 'Auto-calculated from type × units.';
        }

        syncInnerUnits();
        filterStockOptions();
        renderStockOverflow();
        renderOverflow();
    }

    function updateTotal() {
        const units = parseFloat(itemEl.value || 0);
        const price = parseFloat(unitPriceEl?.value || 0);
        if (totalPriceEl && units > 0 && price > 0) {
            totalPriceEl.value = (units * price).toFixed(2);
        }
    }

    catalogEl.addEventListener('change', sync);
    millingEl?.addEventListener('change', renderOverflow);
    stockEl?.addEventListener('change', renderStockOverflow);
    itemEl.addEventListener('input', () => { sync(); updateTotal(); });
    qtyEl.addEventListener('input', () => { renderOverflow(); }); // manual-weight mode
    unitPriceEl?.addEventListener('input', updateTotal);

    sync();
    updateTotal();
})();
</script>
