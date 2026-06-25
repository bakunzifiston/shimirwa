@php
    $existingItems = old('items', $milling->items ?? []);
    if (empty($existingItems)) {
        $existingItems = [['type' => '', 'source' => '', 'stock_id' => '', 'quantity' => '']];
    }

    // Roasting batches meta: {id: {label, item, qty, date, batch}}
    $roastingMeta = $roastingOptions->mapWithKeys(function ($r) {
        $item  = $r->rawMaterialStock?->item
               ?? $r->sorting?->rawMaterialStock?->item
               ?? '—';
        $avail = (float) $r->quantity_out;
        return [$r->id => [
            'item'  => $item,
            'batch' => $r->batch,
            'label' => $r->batch . ' — ' . $item . ' (' . number_format($avail, 1) . ' kg available)',
            'qty'   => $avail,
            'date'  => $r->date?->format('Y-m-d') ?? '',
        ]];
    });

    // Sorting batches meta: {id: {label, item, qty, date, batch}}
    $sortingMeta = $sortingOptions->mapWithKeys(function ($s) {
        $item  = $s->rawMaterialStock?->item ?? '—';
        $batch = $s->rawMaterialStock?->batch_number ?? "Sorting #{$s->id}";
        $avail = (float) $s->quantity_out;
        return [$s->id => [
            'item'  => $item,
            'batch' => $batch,
            'label' => $item . ' — ' . $batch . ' (' . number_format($avail, 1) . ' kg)',
            'qty'   => $avail,
            'date'  => $s->date?->format('Y-m-d') ?? '',
        ]];
    });

    // Group batches by item for overflow: {source: {item: [{id, batch, qty, date}, ...]}}
    // Oldest first so overflow goes to the next oldest batch
    $batchesByItem = ['roasting' => [], 'sorting' => []];
    foreach ($roastingOptions->sortBy('date') as $r) {
        $item = $r->rawMaterialStock?->item ?? $r->sorting?->rawMaterialStock?->item ?? '—';
        $batchesByItem['roasting'][$item][] = [
            'id' => $r->id, 'batch' => $r->batch,
            'qty' => (float) $r->quantity_out, 'date' => $r->date?->format('Y-m-d') ?? '',
        ];
    }
    foreach ($sortingOptions->sortBy('date') as $s) {
        $item = $s->rawMaterialStock?->item ?? '—';
        $batch = $s->rawMaterialStock?->batch_number ?? "Sorting #{$s->id}";
        $batchesByItem['sorting'][$item][] = [
            'id' => $s->id, 'batch' => $batch,
            'qty' => (float) $s->quantity_out, 'date' => $s->date?->format('Y-m-d') ?? '',
        ];
    }

    // Catalog items meta: {name: {source: 'roasting'|'sorting'}}
    $catalogMeta = $catalogItems->mapWithKeys(fn ($c) => [
        $c->name => ['source' => $c->requires_roasting ? 'roasting' : 'sorting']
    ]);
@endphp

<div id="milling-form"
     data-roasting-meta='@json($roastingMeta)'
     data-sorting-meta='@json($sortingMeta)'
     data-catalog-meta='@json($catalogMeta)'
     data-batches-by-item='@json($batchesByItem)'>

    <div class="admin-form-grid">
        <div>
            <label class="admin-label" for="date">Date</label>
            <input type="date" id="date" name="date" class="admin-input"
                   value="{{ old('date', optional($milling->date)->format('Y-m-d') ?? date('Y-m-d')) }}" required>
        </div>
        <div>
            <label class="admin-label" for="batch_number">Batch number</label>
            <input type="text" id="batch_number" name="batch_number" class="admin-input"
                   value="{{ old('batch_number', $milling->batch_number) }}" required>
        </div>
        <div>
            <label class="admin-label" for="employee_id">Employee</label>
            <select id="employee_id" name="employee_id" class="admin-input" required>
                <option value="">Select employee</option>
                @foreach ($employees as $employee)
                    <option value="{{ $employee->id }}" @selected(old('employee_id', $milling->employee_id) == $employee->id)>
                        {{ $employee->full_name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="admin-label">Total mixed (kg)</label>
            <input type="number" step="0.01" id="total_mixed_quantity" name="total_mixed_quantity"
                   class="admin-input" style="background:var(--admin-bg)" readonly
                   value="{{ old('total_mixed_quantity', $milling->total_mixed_quantity ?? 0) }}">
            <p class="mt-1 text-xs" style="color:var(--admin-text-muted)">Auto-calculated from ingredients.</p>
        </div>
        <div>
            <label class="admin-label" for="loss">Loss (kg)</label>
            <input type="number" step="0.01" min="0" id="loss" name="loss" class="admin-input"
                   value="{{ old('loss', $milling->loss ?? 0) }}" required>
            <p class="mt-1 text-xs" style="color:var(--admin-text-muted)">Bran, dust, moisture lost during milling.</p>
        </div>
        <div>
            <label class="admin-label" for="output_flour">Output flour (kg)</label>
            <input type="number" step="0.01" id="output_flour" name="output_flour"
                   class="admin-input" style="background:var(--admin-bg)" readonly
                   value="{{ old('output_flour', $milling->output_flour ?? 0) }}" required>
            <p class="mt-1 text-xs" id="output-hint" style="color:var(--admin-text-muted)">Auto-calculated: total mixed − loss.</p>
        </div>
    </div>

    <div class="mt-6">
        <div class="mb-3 flex items-center justify-between">
            <h3 class="text-sm font-semibold" style="color:var(--admin-text)">Ingredients</h3>
            <button type="button" id="add-ingredient" class="admin-btn admin-btn-secondary admin-btn-sm">+ Add ingredient</button>
        </div>
        @error('items')
            <p class="mb-2 text-xs font-medium rounded p-2" style="background:#fef2f2;color:#dc2626;border:1px solid #fecaca">{{ $message }}</p>
        @enderror
        <div id="ingredients-list" class="space-y-3"></div>
    </div>
</div>

<script>
(function () {
    const form        = document.getElementById('milling-form');
    if (!form) return;

    const roastingMeta  = JSON.parse(form.dataset.roastingMeta  || '{}');
    const sortingMeta   = JSON.parse(form.dataset.sortingMeta   || '{}');
    const catalogMeta   = JSON.parse(form.dataset.catalogMeta   || '{}');
    const batchesByItem = JSON.parse(form.dataset.batchesByItem || '{}');
    const list          = form.querySelector('#ingredients-list');
    const lossEl        = form.querySelector('#loss');
    const totalEl       = form.querySelector('#total_mixed_quantity');
    const outputEl      = form.querySelector('#output_flour');
    const initial       = @json($existingItems);

    function getMeta(source) {
        return source === 'roasting' ? roastingMeta : sortingMeta;
    }

    // Build primary <option> list for a given source type, filtered to a specific item
    function batchOptions(source, item, selectedId) {
        const meta = getMeta(source);
        let html = '<option value="">Select batch</option>';
        for (const [id, m] of Object.entries(meta)) {
            if (item && m.item !== item) continue;
            const sel = String(id) === String(selectedId) ? 'selected' : '';
            html += `<option value="${id}" ${sel}>${m.batch} (${m.qty.toFixed(1)} kg)</option>`;
        }
        return html;
    }

    // Compute overflow allocations across batches of same item+source
    function computeAlloc(source, item, startId, qty) {
        const siblings = (batchesByItem[source] || {})[item] || [];
        const startIdx = siblings.findIndex(b => String(b.id) === String(startId));
        const ordered  = startIdx >= 0
            ? [...siblings.slice(startIdx), ...siblings.slice(0, startIdx)]
            : siblings;

        let rem = qty;
        const alloc = [];
        for (const b of ordered) {
            if (rem <= 0) break;
            const take = Math.min(b.qty, rem);
            alloc.push({ ...b, take: Math.round(take * 100) / 100 });
            rem = Math.round((rem - take) * 100) / 100;
        }
        return { alloc, unmet: Math.max(rem, 0) };
    }

    function totalAvailForItem(source, item) {
        const siblings = (batchesByItem[source] || {})[item] || [];
        return siblings.reduce((s, b) => s + b.qty, 0);
    }

    function computeTotals() {
        let total = 0;
        list.querySelectorAll('.ingredient-qty').forEach(el => {
            total += parseFloat(el.value || 0);
        });
        totalEl.value = total.toFixed(2);

        const loss   = Math.max(parseFloat(lossEl.value || 0), 0);
        const output = Math.max(total - loss, 0);
        outputEl.value = output.toFixed(2);

        const hint = form.querySelector('#output-hint');
        if (hint) {
            if (loss > total && total > 0) {
                hint.textContent = `⚠ Loss (${loss.toFixed(1)} kg) exceeds total mixed (${total.toFixed(1)} kg).`;
                hint.style.color = '#dc2626';
            } else {
                hint.textContent = 'Auto-calculated: total mixed − loss.';
                hint.style.color = '';
            }
        }
    }

    function rowHtml(i, row) {
        const type    = row.type    || '';
        const source  = row.source  || (type ? (catalogMeta[type]?.source ?? '') : '');
        const stockId = row.stock_id || '';
        const qty     = row.quantity ?? '';

        const itemOptions = Object.keys(catalogMeta).map(name =>
            `<option value="${name}" ${name === type ? 'selected' : ''}>${name}</option>`
        ).join('');

        const sourceLabel = source === 'roasting'
            ? '<span class="ingredient-src-badge text-xs px-1.5 py-0.5 rounded font-medium" style="background:#ffedd5;color:#c2410c">from roasting</span>'
            : source === 'sorting'
            ? '<span class="ingredient-src-badge text-xs px-1.5 py-0.5 rounded font-medium" style="background:#dbeafe;color:#1e40af">from sorting</span>'
            : '<span class="ingredient-src-badge"></span>';

        return `<div class="ingredient-row rounded-lg border p-3 space-y-2" style="border-color:var(--admin-border);background:var(--admin-bg)" data-index="${i}">
            <input type="hidden" name="items[${i}][source]" class="ingredient-source-hidden" value="${source}">
            <div class="grid gap-3 md:grid-cols-4 items-end">
                <div>
                    <label class="admin-label text-xs">Item ${sourceLabel}</label>
                    <select name="items[${i}][type]" class="admin-input ingredient-type" required>
                        <option value="">Select item</option>
                        ${itemOptions}
                    </select>
                </div>
                <div>
                    <label class="admin-label text-xs">Starting batch</label>
                    <select class="admin-input ingredient-stock">
                        ${batchOptions(source, type, stockId)}
                    </select>
                    <p class="ingredient-qty-hint mt-0.5 text-xs" style="color:var(--admin-text-muted)"></p>
                </div>
                <div>
                    <label class="admin-label text-xs">Qty (kg)</label>
                    <input type="number" step="0.01" min="0.01" name="items[${i}][quantity]"
                           class="admin-input ingredient-qty" value="${qty}" required>
                </div>
                <div>
                    <button type="button" class="remove-row w-full rounded border px-2 py-1.5 text-xs font-medium" style="color:#dc2626;border-color:#fecaca">Remove</button>
                </div>
            </div>
            <div class="ingredient-overflow" style="display:none">
                <div class="rounded-lg border overflow-hidden" style="border-color:#fde68a">
                    <div class="px-3 py-1.5 text-xs font-semibold" style="background:#fef9c3;color:#854d0e">
                        Exceeds this batch — also drawing from:
                    </div>
                    <div class="ingredient-overflow-rows divide-y" style="border-color:#fde68a"></div>
                </div>
            </div>
            <div class="ingredient-error" style="display:none">
                <p class="text-xs font-medium rounded p-2" style="background:#fef2f2;color:#dc2626;border:1px solid #fecaca"></p>
            </div>
            <div class="ingredient-alloc-inputs" style="display:none"></div>
        </div>`;
    }

    function bindRow(rowEl) {
        const typeEl       = rowEl.querySelector('.ingredient-type');
        const stockEl      = rowEl.querySelector('.ingredient-stock');
        const qtyEl        = rowEl.querySelector('.ingredient-qty');
        const qtyHint      = rowEl.querySelector('.ingredient-qty-hint');
        const srcHidden    = rowEl.querySelector('.ingredient-source-hidden');
        const srcBadge     = rowEl.querySelector('.ingredient-src-badge');
        const overflowWrap = rowEl.querySelector('.ingredient-overflow');
        const overflowRows = rowEl.querySelector('.ingredient-overflow-rows');
        const errWrap      = rowEl.querySelector('.ingredient-error');
        const errMsg       = errWrap.querySelector('p');
        const allocInputs  = rowEl.querySelector('.ingredient-alloc-inputs');

        function updateBatches() {
            const name   = typeEl.value;
            const source = catalogMeta[name]?.source ?? '';
            srcHidden.value = source;

            if (srcBadge) {
                if (source === 'roasting') {
                    srcBadge.style.cssText = 'background:#ffedd5;color:#c2410c';
                    srcBadge.className = 'ingredient-src-badge text-xs px-1.5 py-0.5 rounded font-medium';
                    srcBadge.textContent = 'from roasting';
                } else if (source === 'sorting') {
                    srcBadge.style.cssText = 'background:#dbeafe;color:#1e40af';
                    srcBadge.className = 'ingredient-src-badge text-xs px-1.5 py-0.5 rounded font-medium';
                    srcBadge.textContent = 'from sorting';
                } else {
                    srcBadge.textContent = '';
                }
            }

            stockEl.innerHTML = batchOptions(source, name, '');
            qtyHint.textContent = '';
            renderOverflow();
        }

        function renderOverflow() {
            const i       = rowEl.dataset.index;
            const source  = srcHidden.value;
            const item    = typeEl.value;
            const stockId = stockEl.value;
            const qty     = parseFloat(qtyEl.value) || 0;

            overflowWrap.style.display = 'none';
            errWrap.style.display      = 'none';
            allocInputs.innerHTML      = '';
            qtyHint.textContent        = '';

            if (!source || !item || !stockId) return;

            const avail      = getMeta(source)[stockId]?.qty ?? 0;
            const totalAvail = totalAvailForItem(source, item);

            qtyHint.textContent = avail < totalAvail
                ? `This batch: ${avail.toFixed(1)} kg · All batches: ${totalAvail.toFixed(1)} kg`
                : `Available: ${avail.toFixed(1)} kg`;
            qtyHint.style.color = '';

            if (qty <= 0) {
                // No overflow needed; set stock_id via hidden so it submits
                allocInputs.innerHTML =
                    `<input type="hidden" name="items[${i}][stock_id]" class="alloc-stock-id" value="${stockId}">`;
                return;
            }

            if (qty > totalAvail) {
                errWrap.style.display = '';
                errMsg.textContent = `Not enough stock. Max across all batches: ${totalAvail.toFixed(1)} kg.`;
                allocInputs.innerHTML =
                    `<input type="hidden" name="items[${i}][stock_id]" class="alloc-stock-id" value="${stockId}">`;
                return;
            }

            const { alloc } = computeAlloc(source, item, stockId, qty);

            // Primary stock_id (no overflow inputs needed when single batch)
            allocInputs.innerHTML =
                `<input type="hidden" name="items[${i}][stock_id]" class="alloc-stock-id" value="${alloc[0]?.id ?? stockId}">`;

            // Overflow batches beyond the first
            alloc.slice(1).forEach((a, j) => {
                allocInputs.innerHTML +=
                    `<input type="hidden" name="items[${i}][overflow][${j}][stock_id]" value="${a.id}">` +
                    `<input type="hidden" name="items[${i}][overflow][${j}][quantity]" value="${a.take}">`;
            });

            // Show overflow panel
            const extra = alloc.slice(1);
            if (extra.length > 0) {
                overflowWrap.style.display = '';
                overflowRows.innerHTML = extra.map(a =>
                    `<div class="px-3 py-1.5 flex items-center justify-between text-xs" style="background:#fffbeb">
                        <span class="font-mono font-semibold">${a.batch}</span>
                        <span class="ml-2" style="color:#78716c">${a.date}</span>
                        <span class="ml-auto font-semibold" style="color:#92400e">${a.take.toFixed(1)} kg</span>
                    </div>`
                ).join('');
            }
        }

        typeEl.addEventListener('change', () => { updateBatches(); computeTotals(); });
        stockEl.addEventListener('change', () => { renderOverflow(); computeTotals(); });
        qtyEl.addEventListener('input', () => { renderOverflow(); computeTotals(); });
        rowEl.querySelector('.remove-row').addEventListener('click', () => {
            if (list.children.length > 1) { rowEl.remove(); reindex(); }
        });

        renderOverflow();
    }

    function reindex() {
        [...list.querySelectorAll('.ingredient-row')].forEach((row, i) => {
            row.dataset.index = i;
            row.querySelectorAll('[name^="items"]').forEach(el => {
                el.name = el.name.replace(/items\[\d+\]/, `items[${i}]`);
            });
        });
        computeTotals();
    }

    function addRow(row) {
        list.insertAdjacentHTML('beforeend', rowHtml(list.children.length, row || {}));
        bindRow(list.lastElementChild);
        computeTotals();
    }

    form.querySelector('#add-ingredient').addEventListener('click', () => addRow({}));
    lossEl.addEventListener('input', computeTotals);
    initial.forEach(r => addRow(r));
    if (!list.children.length) addRow({});
})();
</script>
