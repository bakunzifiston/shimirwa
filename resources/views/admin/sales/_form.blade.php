@php
    $batches = old('batches', $sale->batches ?? [['emballage_id' => '', 'quantity' => 1, 'unit_price' => 0, 'line_total' => 0]]);
    if (empty($batches)) {
        $batches = [['emballage_id' => '', 'quantity' => 1, 'unit_price' => 0, 'line_total' => 0]];
    }
    $embOpts = $emballages->map(function ($e) {
        $catName    = $e->packagingCatalog?->name ?? strtoupper($e->packaging_type ?? '—');
        $innerUnits = '';
        if ($e->packagingCatalog?->hasInnerUnits()) {
            $perPkg    = $e->packagingCatalog->inner_units_per_package;
            $innerName = $e->packagingCatalog->innerUnitCatalog?->name ?? 'inner';
            $innerUnits = "{$perPkg}×{$innerName}";
        }
        return [
            'id'         => $e->id,
            'label'      => $catName . ($innerUnits ? " ({$innerUnits})" : '') . ' — Batch ' . ($e->packaging_batch_id ?? '—') . ' (' . $e->item . ' left)',
            'price'      => (float) ($e->unit_price ?? 0),
            'inner_info' => $innerUnits,
        ];
    });
@endphp

<div class="admin-form-grid">
    <div>
        <label class="admin-label" for="date">Date</label>
        <input type="date" id="date" name="date" class="admin-input"
               value="{{ old('date', optional($sale->date)->format('Y-m-d')) }}" required>
    </div>
    <div>
        <label class="admin-label" for="item">Product name</label>
        <input type="text" id="item" name="item" class="admin-input"
               value="{{ old('item', $sale->item) }}" required>
    </div>
    <div>
        <label class="admin-label" for="client_id">Client</label>
        <select id="client_id" name="client_id" class="admin-input" required>
            <option value="">Select client</option>
            @foreach ($clients as $client)
                <option value="{{ $client->id }}" @selected(old('client_id', $sale->client_id) == $client->id)>
                    {{ $client->full_name }}
                </option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="admin-label" for="employee_id">Employee</label>
        <select id="employee_id" name="employee_id" class="admin-input" required>
            <option value="">Select employee</option>
            @foreach ($employees as $employee)
                <option value="{{ $employee->id }}" @selected(old('employee_id', $sale->employee_id) == $employee->id)>
                    {{ $employee->full_name }}
                </option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="admin-label" for="returned">Returned</label>
        <input type="number" step="0.01" min="0" id="returned" name="returned" class="admin-input"
               value="{{ old('returned', $sale->returned ?? 0) }}">
    </div>
    <div>
        <label class="admin-label" for="reason">Reason</label>
        <input type="text" id="reason" name="reason" class="admin-input"
               value="{{ old('reason', $sale->reason) }}">
    </div>
</div>

<div id="sale-batches-form" class="mt-6" data-emballages='@json($embOpts)'>
    <div class="mb-2 flex items-center justify-between">
        <h3 class="font-medium text-slate-800">Batches sold</h3>
        <button type="button" id="add-batch" class="admin-btn admin-btn-secondary admin-btn-sm">Add batch</button>
    </div>
    <div id="batches-list" class="space-y-3"></div>
</div>

<script>
(function () {
    const root = document.getElementById('sale-batches-form');
    if (!root) return;
    const emballages = JSON.parse(root.dataset.emballages || '[]');
    const list    = root.querySelector('#batches-list');
    const initial = @json($batches);

    function opts(selected) {
        return '<option value="">Select packaging batch</option>' +
            emballages.map(e => `<option value="${e.id}" data-price="${e.price}" data-inner="${e.inner_info||''}" ${String(selected)==String(e.id)?'selected':''}>${e.label}</option>`).join('');
    }

    function rowHtml(i, row) {
        // Find inner_info for the currently selected batch
        const selEmb = emballages.find(e => String(e.id) === String(row.emballage_id));
        const innerBadge = selEmb?.inner_info
            ? `<span class="ml-2 text-xs font-semibold px-1.5 py-0.5 rounded" style="background:#fef9c3;color:#854d0e">+${selEmb.inner_info} deducted</span>`
            : '';
        return `<div class="admin-repeater-row batch-row grid gap-3 md:grid-cols-5" data-index="${i}">
            <div class="md:col-span-2">
                <label class="admin-label text-xs">Packaging batch</label>
                <select name="batches[${i}][emballage_id]" class="admin-input batch-emb" required>${opts(row.emballage_id)}</select>
                <div class="batch-inner-note mt-1 text-xs">${innerBadge}</div>
            </div>
            <div><label class="admin-label text-xs">Qty</label>
            <input type="number" min="1" name="batches[${i}][quantity]" class="admin-input batch-qty" value="${row.quantity ?? 1}" required></div>
            <div><label class="admin-label text-xs">Unit price</label>
            <input type="number" step="0.01" min="0" name="batches[${i}][unit_price]" class="admin-input batch-price" value="${row.unit_price ?? 0}" required></div>
            <div><label class="admin-label text-xs">Line total</label>
            <input type="number" step="0.01" min="0" name="batches[${i}][line_total]" class="admin-input batch-total" value="${row.line_total ?? 0}" readonly style="background:var(--admin-bg)"></div>
            <div class="flex items-end"><button type="button" class="remove-batch admin-btn admin-btn-ghost admin-btn-sm" style="color:#dc2626">Remove</button></div>
        </div>`;
    }

    function reindex() {
        [...list.querySelectorAll('.batch-row')].forEach((row, i) => {
            row.querySelectorAll('[name^="batches"]').forEach(el => {
                el.name = el.name.replace(/batches\[\d+\]/, `batches[${i}]`);
            });
        });
    }

    function syncLine(row) {
        const qty = parseInt(row.querySelector('.batch-qty').value || 0, 10);
        const price = parseFloat(row.querySelector('.batch-price').value || 0);
        row.querySelector('.batch-total').value = (qty * price).toFixed(2);
    }

    function bindRow(row) {
        const emb = row.querySelector('.batch-emb');
        emb.addEventListener('change', () => {
            const opt = emb.selectedOptions[0];
            if (opt && opt.dataset.price) {
                row.querySelector('.batch-price').value = opt.dataset.price;
            }
            // Update inner units badge
            const noteEl = row.querySelector('.batch-inner-note');
            if (noteEl) {
                const inner = opt?.dataset.inner || '';
                noteEl.innerHTML = inner
                    ? `<span class="text-xs font-semibold px-1.5 py-0.5 rounded" style="background:#fef9c3;color:#854d0e">+${inner} also deducted per unit</span>`
                    : '';
            }
            syncLine(row);
        });
        row.querySelector('.batch-qty').addEventListener('input', () => syncLine(row));
        row.querySelector('.batch-price').addEventListener('input', () => syncLine(row));
        row.querySelector('.remove-batch').addEventListener('click', () => {
            if (list.children.length > 1) { row.remove(); reindex(); }
        });
    }

    function addRow(row) {
        const i = list.children.length;
        list.insertAdjacentHTML('beforeend', rowHtml(i, row || {}));
        bindRow(list.lastElementChild);
    }

    root.querySelector('#add-batch').addEventListener('click', () => addRow({}));
    initial.forEach(r => addRow(r));
    if (!list.children.length) addRow({});
})();
</script>
