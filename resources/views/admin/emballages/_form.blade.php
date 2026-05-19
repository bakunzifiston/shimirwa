@php
    $ptype = old('packaging_type', $emballage->packaging_type ?? '1kg');
@endphp

<div id="emballage-form" class="admin-form-grid"
     data-kg='@json(["box"=>12,"1kg"=>1,"5kg"=>5,"sack"=>0])'>

    <div>
        <label class="admin-label" for="date">Date</label>
        <input type="date" id="date" name="date" class="admin-input"
               value="{{ old('date', optional($emballage->date)->format('Y-m-d')) }}" required>
    </div>
    <div>
        <label class="admin-label" for="packaging_batch_id">Packaging batch ID</label>
        <input type="text" id="packaging_batch_id" name="packaging_batch_id" class="admin-input"
               value="{{ old('packaging_batch_id', $emballage->packaging_batch_id) }}" required>
    </div>
    <div>
        <label class="admin-label" for="packaging_type">Packaging type</label>
        <select id="packaging_type" name="packaging_type" class="admin-input" required>
            @foreach ($packagingTypes as $value => $label)
                <option value="{{ $value }}" @selected($ptype === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="admin-label" for="raw_material_stock_id" id="stock-label">Packaging material batch</label>
        <select id="raw_material_stock_id" name="raw_material_stock_id" class="admin-input" required>
            <option value="">Select batch</option>
            @foreach ($packagingStocks as $stock)
                <option value="{{ $stock->id }}" @selected(old('raw_material_stock_id', $emballage->raw_material_stock_id) == $stock->id)>
                    {{ $stock->item }} — {{ $stock->batch_number }} ({{ $stock->quantity_in }} left)
                </option>
            @endforeach
        </select>
    </div>
    <div id="envelope-wrap" style="display:none">
        <label class="admin-label" for="envelope_stock_id">Envelope batch (for boxes)</label>
        <select id="envelope_stock_id" name="envelope_stock_id" class="admin-input">
            <option value="">Select envelope batch</option>
            @foreach ($packagingStocks as $stock)
                <option value="{{ $stock->id }}" @selected(old('envelope_stock_id', $emballage->envelope_stock_id) == $stock->id)>
                    {{ $stock->item }} — {{ $stock->batch_number }} ({{ $stock->quantity_in }} left)
                </option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="admin-label" for="milling_id">Milling batch (flour)</label>
        <select id="milling_id" name="milling_id" class="admin-input">
            <option value="">Select milling batch</option>
            @foreach ($millings as $milling)
                <option value="{{ $milling->id }}" @selected(old('milling_id', $emballage->milling_id) == $milling->id)>
                    {{ $milling->batch_number }} — {{ $milling->output_flour }} kg available
                </option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="admin-label" for="item" id="item-label">Number of units</label>
        <input type="number" step="1" min="1" id="item" name="item" class="admin-input"
               value="{{ old('item', $emballage->item) }}" required>
    </div>
    <div>
        <label class="admin-label" for="quantity">Flour quantity (kg)</label>
        <input type="number" step="0.01" min="0" id="quantity" name="quantity" class="admin-input"
               value="{{ old('quantity', $emballage->quantity) }}" required>
        <p class="mt-1 text-xs text-slate-500" id="qty-hint">Auto-calculated except for sacks.</p>
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

@push('scripts')
<script>
(function () {
    const form = document.getElementById('emballage-form');
    if (!form) return;
    const kgMap = JSON.parse(form.dataset.kg || '{}');
    const typeEl = document.getElementById('packaging_type');
    const itemEl = document.getElementById('item');
    const qtyEl = document.getElementById('quantity');
    const envelopeWrap = document.getElementById('envelope-wrap');
    const envelopeEl = document.getElementById('envelope_stock_id');
    const itemLabel = document.getElementById('item-label');
    const qtyHint = document.getElementById('qty-hint');

    const labels = {
        box: 'Number of boxes',
        '1kg': 'Number of 1kg packages',
        '5kg': 'Number of 5kg packages',
        sack: 'Number of sacks',
    };

    function sync() {
        const type = typeEl.value;
        itemLabel.textContent = labels[type] || 'Number of units';
        envelopeWrap.style.display = type === 'box' ? '' : 'none';
        envelopeEl.required = type === 'box';
        if (type !== 'box') envelopeEl.value = '';
        const kg = parseFloat(kgMap[type] || 1);
        if (type === 'sack') {
            qtyEl.readOnly = false;
            qtyEl.classList.remove('bg-slate-100');
            qtyHint.textContent = 'Enter flour weight manually for sacks.';
        } else {
            qtyEl.readOnly = true;
            qtyEl.classList.add('bg-slate-100');
            qtyEl.value = (parseFloat(itemEl.value || 0) * kg).toFixed(2);
            qtyHint.textContent = `Auto: ${kg} kg per unit.`;
        }
    }

    typeEl.addEventListener('change', sync);
    itemEl.addEventListener('input', sync);
    sync();
})();
</script>
@endpush
