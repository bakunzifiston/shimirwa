@php
    $items = old('items', $milling->items ?? [['type' => '', 'stock_id' => '', 'quantity' => '']]);
    if (empty($items)) {
        $items = [['type' => '', 'stock_id' => '', 'quantity' => '']];
    }
    $roastingOpts = $roastingOptions->map(fn ($r) => ['id' => $r->id, 'label' => $r->batch . ' (' . $r->quantity_in . ' kg)']);
    $sortingOpts = $sortingOptions->map(fn ($s) => [
        'id' => $s->id,
        'label' => ($s->rawMaterialStock?->item ?? 'Item') . ' — ' . ($s->rawMaterialStock?->batch_number ?? '') . ' (' . $s->quantity_in . ' kg)',
    ]);
@endphp

<div id="milling-form"
     data-roasting='@json($roastingOpts)'
     data-sorting='@json($sortingOpts)'>

    <div class="admin-form-grid">
        <div>
            <label class="admin-label" for="date">Date</label>
            <input type="date" id="date" name="date" class="admin-input"
                   value="{{ old('date', optional($milling->date)->format('Y-m-d')) }}" required>
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
            <label class="admin-label" for="loss">Loss (kg)</label>
            <input type="number" step="0.01" min="0" id="loss" name="loss" class="admin-input"
                   value="{{ old('loss', $milling->loss ?? 0) }}" required>
        </div>
        <div>
            <label class="admin-label" for="total_mixed_quantity">Total mixed (kg)</label>
            <input type="number" step="0.01" id="total_mixed_quantity" name="total_mixed_quantity" class="admin-input bg-slate-100" readonly
                   value="{{ old('total_mixed_quantity', $milling->total_mixed_quantity ?? 0) }}">
        </div>
        <div>
            <label class="admin-label" for="output_flour">Output flour (kg)</label>
            <input type="number" step="0.01" id="output_flour" name="output_flour" class="admin-input bg-slate-100" readonly
                   value="{{ old('output_flour', $milling->output_flour ?? 0) }}">
        </div>
    </div>

    <div class="mt-6">
        <div class="mb-2 flex items-center justify-between">
            <h3 class="font-medium text-slate-800">Ingredients</h3>
            <button type="button" id="add-ingredient" class="admin-btn admin-btn-secondary admin-btn-sm">Add row</button>
        </div>
        <div id="ingredients-list" class="space-y-3"></div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    const form = document.getElementById('milling-form');
    if (!form) return;
    const roasting = JSON.parse(form.dataset.roasting || '[]');
    const sorting = JSON.parse(form.dataset.sorting || '[]');
    const list = document.getElementById('ingredients-list');
    const lossEl = document.getElementById('loss');
    const totalEl = document.getElementById('total_mixed_quantity');
    const outputEl = document.getElementById('output_flour');
    const initial = @json($items);

    function batchOptions(type) {
        const src = (type === 'soy' || type === 'maize') ? roasting : sorting;
        return '<option value="">Select batch</option>' + src.map(o =>
            `<option value="${o.id}">${o.label}</option>`).join('');
    }

    function rowHtml(i, row) {
        const type = row.type || '';
        const stockId = row.stock_id || '';
        const qty = row.quantity ?? '';
        return `<div class="admin-repeater-row ingredient-row grid gap-3 md:grid-cols-4" data-index="${i}">
            <div><label class="admin-label text-xs">Ingredient</label>
            <select name="items[${i}][type]" class="admin-input ingredient-type" required>
                <option value="">—</option>
                <option value="soy" ${type==='soy'?'selected':''}>Soy</option>
                <option value="sorghum" ${type==='sorghum'?'selected':''}>Sorghum</option>
                <option value="wheat" ${type==='wheat'?'selected':''}>Wheat</option>
                <option value="maize" ${type==='maize'?'selected':''}>Maize</option>
            </select></div>
            <div><label class="admin-label text-xs">Batch</label>
            <select name="items[${i}][stock_id]" class="admin-input ingredient-stock" required>${batchOptions(type)}</select></div>
            <div><label class="admin-label text-xs">Qty (kg)</label>
            <input type="number" step="0.01" min="0.01" name="items[${i}][quantity]" class="admin-input ingredient-qty" value="${qty}" required></div>
            <div class="flex items-end"><button type="button" class="remove-row rounded border px-2 py-1 text-sm text-red-600">Remove</button></div>
        </div>`;
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

    function computeTotals() {
        let total = 0;
        list.querySelectorAll('.ingredient-qty').forEach(el => {
            total += parseFloat(el.value || 0);
        });
        const loss = parseFloat(lossEl.value || 0);
        totalEl.value = total.toFixed(2);
        outputEl.value = Math.max(total - loss, 0).toFixed(2);
    }

    function bindRow(row) {
        const typeEl = row.querySelector('.ingredient-type');
        const stockEl = row.querySelector('.ingredient-stock');
        typeEl.addEventListener('change', () => {
            const prev = stockEl.value;
            stockEl.innerHTML = batchOptions(typeEl.value);
            if ([...stockEl.options].some(o => o.value === prev)) stockEl.value = prev;
        });
        row.querySelector('.ingredient-qty').addEventListener('input', computeTotals);
        row.querySelector('.remove-row').addEventListener('click', () => {
            if (list.children.length > 1) { row.remove(); reindex(); }
        });
    }

    function addRow(row) {
        const i = list.children.length;
        list.insertAdjacentHTML('beforeend', rowHtml(i, row || {}));
        const rowEl = list.lastElementChild;
        const typeEl = rowEl.querySelector('.ingredient-type');
        const stockEl = rowEl.querySelector('.ingredient-stock');
        if (row && row.stock_id) stockEl.value = row.stock_id;
        bindRow(rowEl);
        computeTotals();
    }

    document.getElementById('add-ingredient').addEventListener('click', () => addRow({}));
    lossEl.addEventListener('input', computeTotals);
    initial.forEach(r => addRow(r));
    if (!list.children.length) addRow({});
})();
</script>
@endpush
