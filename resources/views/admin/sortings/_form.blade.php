@php
    $allStocks = $stocks ?? collect();

    // All batches as a flat meta map keyed by id
    $stockMeta = $allStocks->mapWithKeys(fn($s) => [
        $s->id => [
            'id'       => $s->id,
            'item'     => $s->item,
            'batch'    => $s->batch_number,
            'qty'      => (float) $s->quantity_in,
            'supplier' => $s->client?->full_name ?? '—',
            'date'     => $s->date?->format('Y-m-d') ?? '',
        ]
    ]);

    // Same-item batches grouped: { "Maize": [{id,batch,qty,...}, ...] }
    // Ordered oldest first so overflow always goes to next oldest batch
    $stocksByItem = $allStocks->sortBy('date')->groupBy('item')->map(fn($g) =>
        $g->map(fn($s) => [
            'id'    => $s->id,
            'batch' => $s->batch_number,
            'qty'   => (float) $s->quantity_in,
            'date'  => $s->date?->format('Y-m-d') ?? '',
        ])->values()
    );

    $selectedStockId = old('raw_material_stock_id', $sorting->raw_material_stock_id ?? null);
@endphp

<div class="admin-form-grid" id="sorting-form"
     data-stock-meta='@json($stockMeta)'
     data-stocks-by-item='@json($stocksByItem)'>

    <div>
        <label class="admin-label" for="date">Date</label>
        <input type="date" id="date" name="date" class="admin-input"
               value="{{ old('date', optional($sorting->date)->format('Y-m-d') ?? date('Y-m-d')) }}" required>
    </div>

    <div>
        <label class="admin-label" for="raw_material_stock_id">Raw material batch</label>
        <select id="raw_material_stock_id" name="_batch_hint" class="admin-input" required>
            <option value="">Select batch</option>
            @foreach ($allStocks as $stock)
                <option value="{{ $stock->id }}" @selected($selectedStockId == $stock->id)>
                    {{ $stock->item }} — {{ $stock->batch_number }} ({{ number_format($stock->quantity_in, 1) }} kg left)
                </option>
            @endforeach
        </select>
        @error('quantity_in')
            <p class="mt-1 text-xs font-medium" style="color:#dc2626">{{ $message }}</p>
        @enderror
    </div>

    {{-- Batch info card --}}
    <div class="md:col-span-2" id="batch-info-wrap" style="display:none">
        <div id="batch-info" class="rounded-lg border p-3 text-sm flex flex-wrap gap-4"
             style="background:#f0f9ff;border-color:#bae6fd;color:#0369a1"></div>
    </div>

    <div>
        <label class="admin-label" for="quantity_in">Quantity to sort (kg)</label>
        <input type="number" step="0.01" min="0.01" id="quantity_in" name="quantity_in" class="admin-input"
               value="{{ old('quantity_in', $sorting->quantity_in) }}" required>
        <p class="mt-1 text-xs" id="qty-hint" style="color:var(--admin-text-muted)"></p>
    </div>

    <div>
        <label class="admin-label" for="loss">Loss (kg)</label>
        <input type="number" step="0.01" min="0" id="loss" name="loss" class="admin-input"
               value="{{ old('loss', $sorting->loss ?? 0) }}" required>
    </div>

    <div>
        <label class="admin-label">Net out (kg)</label>
        <input type="number" step="0.01" id="net_out" class="admin-input" style="background:var(--admin-bg)" readonly value="0">
        <p class="mt-1 text-xs" style="color:var(--admin-text-muted)">Auto-calculated: quantity in − loss.</p>
    </div>

    <div>
        <label class="admin-label" for="employee_id">Employee</label>
        <select id="employee_id" name="employee_id" class="admin-input" required>
            <option value="">Select employee</option>
            @foreach ($employees as $employee)
                <option value="{{ $employee->id }}" @selected(old('employee_id', $sorting->employee_id) == $employee->id)>
                    {{ $employee->full_name }}
                </option>
            @endforeach
        </select>
    </div>

    {{-- Overflow: will pull from additional batches --}}
    <div class="md:col-span-2" id="overflow-wrap" style="display:none">
        <div class="rounded-lg border overflow-hidden" style="border-color:#fde68a">
            <div class="px-3 py-2 text-xs font-semibold" style="background:#fef9c3;color:#854d0e">
                Quantity exceeds this batch — will also draw from:
            </div>
            <div id="overflow-rows" class="divide-y" style="border-color:#fde68a"></div>
        </div>
    </div>

    {{-- Not enough stock error --}}
    <div class="md:col-span-2" id="qty-error-wrap" style="display:none">
        <p id="qty-error-msg" class="text-xs font-medium rounded p-2"
           style="background:#fef2f2;color:#dc2626;border:1px solid #fecaca"></p>
    </div>

    {{-- Hidden allocations sent to server --}}
    <div id="alloc-wrap" style="display:none"></div>

    @error('allocations')
        <div class="md:col-span-2">
            <p class="text-xs font-medium rounded p-2" style="background:#fef2f2;color:#dc2626;border:1px solid #fecaca">{{ $message }}</p>
        </div>
    @enderror
</div>

<script>
(function () {
    const form         = document.getElementById('sorting-form');
    if (!form) return;

    const meta         = JSON.parse(form.dataset.stockMeta      || '{}');
    const byItem       = JSON.parse(form.dataset.stocksByItem   || '{}');
    const batchSel     = form.querySelector('#raw_material_stock_id');
    const qtyInput     = form.querySelector('#quantity_in');
    const lossInput    = form.querySelector('#loss');
    const netOutEl     = form.querySelector('#net_out');
    const qtyHint      = form.querySelector('#qty-hint');
    const infoWrap     = form.querySelector('#batch-info-wrap');
    const infoBox      = form.querySelector('#batch-info');
    const overflowWrap = form.querySelector('#overflow-wrap');
    const overflowRows = form.querySelector('#overflow-rows');
    const errWrap      = form.querySelector('#qty-error-wrap');
    const errMsg       = form.querySelector('#qty-error-msg');
    const allocWrap    = form.querySelector('#alloc-wrap');

    function updateNet() {
        const qty  = Math.max(parseFloat(qtyInput.value  || 0), 0);
        const loss = Math.max(parseFloat(lossInput.value || 0), 0);
        netOutEl.value = Math.max(qty - loss, 0).toFixed(2);
    }

    // Given starting batch id + qty, compute allocation across batches of same item
    function computeAlloc(startId, qty) {
        const startBatch = meta[startId];
        if (!startBatch) return [];
        const siblings = byItem[startBatch.item] || [];

        // Start from chosen batch, then continue with subsequent ones (by date order)
        const startIdx = siblings.findIndex(b => b.id === parseInt(startId));
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

    function render() {
        const id  = batchSel.value;
        const qty = parseFloat(qtyInput.value) || 0;

        // Reset
        infoWrap.style.display    = 'none';
        overflowWrap.style.display = 'none';
        errWrap.style.display     = 'none';
        allocWrap.innerHTML       = '';
        qtyHint.textContent       = '';

        if (!id || !meta[id]) return;

        const s = meta[id];

        // Batch info card
        infoWrap.style.display = '';
        infoBox.innerHTML =
            `<span><strong>Item:</strong> ${s.item}</span>` +
            `<span><strong>Batch:</strong> ${s.batch}</span>` +
            `<span><strong>Available:</strong> ${s.qty.toFixed(1)} kg</span>` +
            `<span><strong>Supplier:</strong> ${s.supplier}</span>` +
            `<span><strong>Date:</strong> ${s.date}</span>`;

        // Total available across all same-item batches (starting from chosen)
        const siblings  = byItem[s.item] || [];
        const startIdx  = siblings.findIndex(b => b.id === parseInt(id));
        const ordered   = startIdx >= 0
            ? [...siblings.slice(startIdx), ...siblings.slice(0, startIdx)]
            : siblings;
        const totalAvail = ordered.reduce((sum, b) => sum + b.qty, 0);

        qtyHint.textContent = s.qty < totalAvail
            ? `This batch: ${s.qty.toFixed(1)} kg · Total (all batches): ${totalAvail.toFixed(1)} kg`
            : `Available: ${s.qty.toFixed(1)} kg`;

        if (qty <= 0) return;

        const { alloc, unmet } = computeAlloc(id, qty);

        if (unmet > 0) {
            // Not enough even across all batches
            errWrap.style.display = '';
            errMsg.textContent = `Not enough stock. Max available across all batches: ${totalAvail.toFixed(1)} kg.`;
            return;
        }

        // Build hidden allocations
        alloc.forEach((a, i) => {
            allocWrap.innerHTML +=
                `<input type="hidden" name="allocations[${i}][raw_material_stock_id]" value="${a.id}">` +
                `<input type="hidden" name="allocations[${i}][quantity_in]" value="${a.take}">`;
        });

        // Show overflow batches (everything after the first)
        const extra = alloc.slice(1);
        if (extra.length > 0) {
            overflowWrap.style.display = '';
            overflowRows.innerHTML = extra.map(a =>
                `<div class="px-3 py-2 flex items-center justify-between text-sm" style="background:#fffbeb">
                    <div>
                        <span class="font-mono text-xs font-semibold">${a.batch}</span>
                        <span class="ml-2 text-xs" style="color:#78716c">${a.date}</span>
                    </div>
                    <span class="font-semibold" style="color:#92400e">${a.take.toFixed(1)} kg</span>
                </div>`
            ).join('');
        }
    }

    batchSel.addEventListener('change', render);
    qtyInput.addEventListener('input', () => { render(); updateNet(); });
    lossInput.addEventListener('input', updateNet);
    render();
    updateNet();
})();
</script>
