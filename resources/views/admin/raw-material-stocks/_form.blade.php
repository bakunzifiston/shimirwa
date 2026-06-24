@php
    $selectedType = old('type', $stock->type ?? 'Raw Material');
    $selectedItem = old('item', $stock->item ?? '');
    $isOther = $selectedType === 'Other';
@endphp

<div class="admin-form-grid"
     id="raw-material-stock-form"
     data-items='@json($itemsByType)'>

    <div>
        <label class="admin-label" for="date">Date</label>
        <input id="date" type="date" name="date" class="admin-input"
               value="{{ old('date', optional($stock->date)->format('Y-m-d')) }}" required>
    </div>

    <div>
        <label class="admin-label" for="client_id">Supplier</label>
        <select id="client_id" name="client_id" class="admin-input" required>
            <option value="">Select supplier</option>
            @foreach ($suppliers as $supplier)
                <option value="{{ $supplier->id }}" @selected(old('client_id', $stock->client_id) == $supplier->id)>
                    {{ $supplier->full_name }}
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="admin-label" for="type">Type</label>
        <select id="type" name="type" class="admin-input" required>
            @foreach ($types as $value => $label)
                <option value="{{ $value }}" @selected($selectedType === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>

    <div id="item-select-wrap" @if($isOther) style="display:none" @endif>
        <label class="admin-label" for="item_select">Item</label>
        <select id="item_select" class="admin-input">
            <option value="">Select item</option>
        </select>
    </div>

    <div id="item-custom-wrap" @if(!$isOther) style="display:none" @endif>
        <label class="admin-label" for="item_custom">Item (custom)</label>
        <input id="item_custom" type="text" class="admin-input" placeholder="Enter custom item"
               value="{{ $isOther ? $selectedItem : '' }}">
    </div>

    <input type="hidden" name="item" id="item" value="{{ $selectedItem }}">

    <div>
        <label class="admin-label" for="received">Received quantity</label>
        <input id="received" type="number" step="0.01" min="0" name="received" class="admin-input"
               value="{{ old('received', $stock->received ?? 0) }}" required>
    </div>

    <div>
        <label class="admin-label" for="rejected">Rejected quantity</label>
        <input id="rejected" type="number" step="0.01" min="0" name="rejected" class="admin-input"
               value="{{ old('rejected', $stock->rejected ?? 0) }}" required>
    </div>

    <div>
        <label class="admin-label" for="quantity_in_display">Remaining quantity</label>
        <input id="quantity_in_display" type="number" step="0.01" class="admin-input bg-slate-100" readonly
               value="{{ number_format($stock->remainingQuantity(), 2, '.', '') }}">
        <p class="mt-1 text-xs text-slate-500">Current available balance after production deductions.</p>
        <p class="mt-1 text-xs text-slate-500">Calculated: received − rejected</p>
    </div>

    <div>
        <label class="admin-label" for="batch_number">Batch number</label>
        <input id="batch_number" name="batch_number" class="admin-input"
               value="{{ old('batch_number', $stock->batch_number) }}" required>
    </div>

    <div>
        <label class="admin-label" for="employee_id">Responsible employee</label>
        <select id="employee_id" name="employee_id" class="admin-input" required>
            <option value="">Select employee</option>
            @foreach ($employees as $employee)
                <option value="{{ $employee->id }}" @selected(old('employee_id', $stock->employee_id) == $employee->id)>
                    {{ $employee->full_name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="md:col-span-2">
        <label class="admin-label" for="comment">Comment</label>
        <input id="comment" name="comment" class="admin-input"
               value="{{ old('comment', $stock->comment) }}">
    </div>
</div>

@push('scripts')
<script>
(function () {
    const form = document.getElementById('raw-material-stock-form');
    if (!form) return;

    const itemsByType = JSON.parse(form.dataset.items || '{}');
    const typeEl = document.getElementById('type');
    const itemSelectWrap = document.getElementById('item-select-wrap');
    const itemCustomWrap = document.getElementById('item-custom-wrap');
    const itemSelect = document.getElementById('item_select');
    const itemCustom = document.getElementById('item_custom');
    const itemHidden = document.getElementById('item');
    const received = document.getElementById('received');
    const rejected = document.getElementById('rejected');
    const quantityDisplay = document.getElementById('quantity_in_display');
    const initialItem = @json($selectedItem);

    function syncItemOptions() {
        const type = typeEl.value;
        const isOther = type === 'Other';

        itemSelectWrap.style.display = isOther ? 'none' : '';
        itemCustomWrap.style.display = isOther ? '' : 'none';

        itemSelect.innerHTML = '<option value="">Select item</option>';
        if (!isOther && itemsByType[type]) {
            Object.entries(itemsByType[type]).forEach(([value, label]) => {
                const opt = document.createElement('option');
                opt.value = value;
                opt.textContent = label;
                if (value === initialItem || value === itemHidden.value) opt.selected = true;
                itemSelect.appendChild(opt);
            });
        }

        syncHiddenItem();
    }

    function syncHiddenItem() {
        if (typeEl.value === 'Other') {
            itemHidden.value = itemCustom.value;
        } else {
            itemHidden.value = itemSelect.value;
        }
    }

    function syncQuantity() {
        const net = Math.max(parseFloat(received.value || 0) - parseFloat(rejected.value || 0), 0);
        quantityDisplay.value = net.toFixed(2);
    }

    typeEl.addEventListener('change', syncItemOptions);
    itemSelect.addEventListener('change', syncHiddenItem);
    itemCustom.addEventListener('input', syncHiddenItem);
    received.addEventListener('input', syncQuantity);
    rejected.addEventListener('input', syncQuantity);

    syncItemOptions();
    syncQuantity();
})();
</script>
@endpush
