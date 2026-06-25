@php
    // Build a unified batch list keyed by "raw:ID" or "sorting:ID"
    $batchMeta = collect();

    foreach ($rawStocks as $s) {
        $batchMeta->put('raw:' . $s->id, [
            'key'    => 'raw:' . $s->id,
            'label'  => $s->item . ' — ' . $s->batch_number . ' (' . number_format($s->quantity_in, 1) . ' kg) [reception]',
            'item'   => $s->item,
            'batch'  => $s->batch_number,
            'avail'  => (float) $s->quantity_in,
            'source' => 'raw',
            'date'   => $s->date?->format('Y-m-d') ?? '',
        ]);
    }

    foreach ($sortingStocks as $s) {
        $item  = $s->rawMaterialStock?->item ?? '—';
        $batch = $s->rawMaterialStock?->batch_number ?? "Sorting #{$s->id}";
        $batchMeta->put('sorting:' . $s->id, [
            'key'    => 'sorting:' . $s->id,
            'label'  => $item . ' — ' . $batch . ' (' . number_format($s->quantity_out, 1) . ' kg) [sorted]',
            'item'   => $item,
            'batch'  => $batch,
            'avail'  => (float) $s->quantity_out,
            'source' => 'sorting',
            'date'   => $s->date?->format('Y-m-d') ?? '',
        ]);
    }

    // Group by item+source for overflow, oldest first
    $batchesByGroup = [];
    foreach ($batchMeta->sortBy('date') as $key => $m) {
        $group = $m['item'] . '|' . $m['source'];
        $batchesByGroup[$group][] = ['key' => $key, 'batch' => $m['batch'], 'avail' => $m['avail'], 'date' => $m['date']];
    }
@endphp

<div id="roasting-form" class="admin-form-grid"
     data-batch-meta='@json($batchMeta)'
     data-batches-by-group='@json($batchesByGroup)'>

    <div>
        <label class="admin-label" for="date">Date</label>
        <input type="date" id="date" name="date" class="admin-input"
               value="{{ old('date', optional($roasting->date)->format('Y-m-d') ?? date('Y-m-d')) }}" required>
    </div>

    <div>
        <label class="admin-label" for="source_batch">Source batch</label>
        <select id="source_batch" name="_source_batch_hint" class="admin-input" required>
            <option value="">Select batch</option>
            @if ($rawStocks->isNotEmpty())
                <optgroup label="From reception (not sorted)">
                    @foreach ($rawStocks as $s)
                        <option value="raw:{{ $s->id }}" @selected(old('source_batch', $selectedSource) === 'raw:'.$s->id)>
                            {{ $s->item }} — {{ $s->batch_number }} ({{ number_format($s->quantity_in, 1) }} kg)
                        </option>
                    @endforeach
                </optgroup>
            @endif
            @if ($sortingStocks->isNotEmpty())
                <optgroup label="From sorting">
                    @foreach ($sortingStocks as $s)
                        <option value="sorting:{{ $s->id }}" @selected(old('source_batch', $selectedSource) === 'sorting:'.$s->id)>
                            {{ $s->rawMaterialStock?->item }} — {{ $s->rawMaterialStock?->batch_number }} ({{ number_format($s->quantity_out, 1) }} kg)
                        </option>
                    @endforeach
                </optgroup>
            @endif
        </select>
        @error('source_batch')
            <p class="mt-1 text-xs font-medium" style="color:#dc2626">{{ $message }}</p>
        @enderror
    </div>

    {{-- Live source info --}}
    <div class="md:col-span-2" id="roast-info-wrap" style="display:none">
        <div id="roast-info" class="rounded-lg border p-3 text-sm flex flex-wrap gap-4"
             style="background:var(--admin-primary-soft);border-color:var(--admin-border);color:var(--admin-text-muted)"></div>
    </div>

    <div>
        <label class="admin-label" for="batch">Roasting batch ID</label>
        <input type="text" id="batch" name="batch" class="admin-input"
               value="{{ old('batch', $roasting->batch) }}" required>
    </div>

    <div>
        <label class="admin-label" for="quantity_in">Quantity in (kg)</label>
        <input type="number" step="0.01" min="0.01" id="quantity_in" name="quantity_in" class="admin-input"
               value="{{ old('quantity_in', $roasting->quantity_in) }}" required>
        <p class="mt-1 text-xs" id="qty-hint" style="color:var(--admin-text-muted)"></p>
        @error('quantity_in')
            <p class="mt-1 text-xs font-medium" style="color:#dc2626">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="admin-label" for="loss">Loss (kg)</label>
        <input type="number" step="0.01" min="0" id="loss" name="loss" class="admin-input"
               value="{{ old('loss', $roasting->loss ?? 0) }}" required>
    </div>

    @if ($roasting->exists)
        <div>
            <span class="admin-label">Quantity out</span>
            <p class="text-sm font-medium">{{ number_format($roasting->quantityOut(), 2) }} kg</p>
        </div>
        <div>
            <span class="admin-label">Remaining</span>
            <p class="text-sm font-medium">{{ number_format($roasting->remainingUsable(), 2) }} kg</p>
        </div>
    @endif

    <div>
        <label class="admin-label">Net out (kg)</label>
        <input type="number" step="0.01" id="net_out" class="admin-input" style="background:var(--admin-bg)" readonly value="0">
        <p class="mt-1 text-xs" style="color:var(--admin-text-muted)">Auto-calculated: quantity in − loss.</p>
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

    <div>
        <label class="admin-label" for="chef_id">Chef</label>
        <select id="chef_id" name="chef_id" class="admin-input" required>
            <option value="">Select chef</option>
            @foreach ($employees as $employee)
                <option value="{{ $employee->id }}" @selected(old('chef_id', $roasting->chef_id) == $employee->id)>
                    {{ $employee->full_name }}
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="admin-label" for="supervisor_id">Supervisor</label>
        <select id="supervisor_id" name="supervisor_id" class="admin-input" required>
            <option value="">Select supervisor</option>
            @foreach ($employees as $employee)
                <option value="{{ $employee->id }}" @selected(old('supervisor_id', $roasting->supervisor_id) == $employee->id)>
                    {{ $employee->full_name }}
                </option>
            @endforeach
        </select>
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
    const form         = document.getElementById('roasting-form');
    if (!form) return;

    const batchMeta    = JSON.parse(form.dataset.batchMeta       || '{}');
    const byGroup      = JSON.parse(form.dataset.batchesByGroup  || '{}');
    const sourceSel    = form.querySelector('#source_batch');
    const batchInput   = form.querySelector('#batch');
    const qtyInput     = form.querySelector('#quantity_in');
    const lossInput    = form.querySelector('#loss');
    const netOutEl     = form.querySelector('#net_out');
    const qtyHint      = form.querySelector('#qty-hint');
    const infoWrap     = form.querySelector('#roast-info-wrap');
    const infoBox      = form.querySelector('#roast-info');
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

    function groupKey(key) {
        const m = batchMeta[key];
        return m ? (m.item + '|' + m.source) : null;
    }

    function computeAlloc(startKey, qty) {
        const gk = groupKey(startKey);
        if (!gk) return { alloc: [], unmet: qty };

        const siblings = byGroup[gk] || [];
        const startIdx = siblings.findIndex(b => b.key === startKey);
        const ordered  = startIdx >= 0
            ? [...siblings.slice(startIdx), ...siblings.slice(0, startIdx)]
            : siblings;

        let rem = qty;
        const alloc = [];
        for (const b of ordered) {
            if (rem <= 0) break;
            const take = Math.min(b.avail, rem);
            alloc.push({ ...b, take: Math.round(take * 100) / 100 });
            rem = Math.round((rem - take) * 100) / 100;
        }
        return { alloc, unmet: Math.max(rem, 0) };
    }

    function render() {
        const key = sourceSel.value;
        const qty = parseFloat(qtyInput.value) || 0;
        const m   = batchMeta[key];

        infoWrap.style.display     = 'none';
        overflowWrap.style.display = 'none';
        errWrap.style.display      = 'none';
        allocWrap.innerHTML        = '';
        qtyHint.textContent        = '';

        if (!m) return;

        // Info card
        infoWrap.style.display = '';
        infoBox.innerHTML =
            `<span><strong>Item:</strong> ${m.item}</span>` +
            `<span><strong>Batch:</strong> ${m.batch}</span>` +
            `<span><strong>Available:</strong> ${m.avail.toFixed(1)} kg</span>` +
            `<span><strong>Source:</strong> ${m.source === 'raw' ? 'reception' : 'sorting'}</span>`;

        // Auto-fill batch ID if user hasn't overridden it
        if (!batchInput.value || !batchInput.dataset.userEdited) {
            batchInput.value = m.batch;
        }

        // Total available across sibling batches
        const gk         = groupKey(key);
        const siblings   = byGroup[gk] || [];
        const startIdx   = siblings.findIndex(b => b.key === key);
        const ordered    = startIdx >= 0
            ? [...siblings.slice(startIdx), ...siblings.slice(0, startIdx)]
            : siblings;
        const totalAvail = ordered.reduce((sum, b) => sum + b.avail, 0);

        qtyHint.textContent = m.avail < totalAvail
            ? `This batch: ${m.avail.toFixed(1)} kg · Total (all batches): ${totalAvail.toFixed(1)} kg`
            : `Available: ${m.avail.toFixed(1)} kg`;

        if (qty <= 0) return;

        const { alloc, unmet } = computeAlloc(key, qty);

        if (unmet > 0) {
            errWrap.style.display = '';
            errMsg.textContent = `Not enough stock. Max available across all batches: ${totalAvail.toFixed(1)} kg.`;
            return;
        }

        // Build hidden allocations for server
        alloc.forEach((a, i) => {
            allocWrap.innerHTML +=
                `<input type="hidden" name="allocations[${i}][source_batch]" value="${a.key}">` +
                `<input type="hidden" name="allocations[${i}][quantity_in]" value="${a.take}">`;
        });

        // Show overflow batches
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

    batchInput.addEventListener('input', () => { batchInput.dataset.userEdited = '1'; });
    sourceSel.addEventListener('change', render);
    qtyInput.addEventListener('input', () => { render(); updateNet(); });
    lossInput.addEventListener('input', updateNet);
    render();
    updateNet();
})();
</script>
