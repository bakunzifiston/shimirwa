@php
    $sourceType = old('source_type', $sourceType ?? 'raw');
@endphp

<div id="roasting-form" class="admin-form-grid"
     data-raw-batches='@json($rawStocks->mapWithKeys(fn ($s) => [$s->id => $s->batch_number]))'
     data-sorting-batches='@json($sortingStocks->mapWithKeys(fn ($s) => [$s->id => optional($s->rawMaterialStock)->batch_number ?? "Sorting #{$s->id}"]))'>

    <div>
        <label class="admin-label" for="date">Date</label>
        <input type="date" id="date" name="date" class="admin-input"
               value="{{ old('date', optional($roasting->date)->format('Y-m-d')) }}" required>
    </div>

    <div>
        <label class="admin-label" for="source_type">Source</label>
        <select id="source_type" name="source_type" class="admin-input" required>
            <option value="raw" @selected($sourceType === 'raw')>Raw material stock</option>
            <option value="sorting" @selected($sourceType === 'sorting')>Sorting batch</option>
        </select>
    </div>

    <div id="raw-source-wrap" @if($sourceType !== 'raw') style="display:none" @endif>
        <label class="admin-label" for="raw_material_stock_id">Raw material batch</label>
        <select id="raw_material_stock_id" name="raw_material_stock_id" class="admin-input">
            <option value="">Select batch</option>
            @foreach ($rawStocks as $stock)
                <option value="{{ $stock->id }}" @selected(old('raw_material_stock_id', $roasting->raw_material_stock_id) == $stock->id)>
                    {{ $stock->item }} — {{ $stock->batch_number }} ({{ number_format($stock->remainingQuantity(), 2) }} kg available)
                </option>
            @endforeach
        </select>
    </div>

    <div id="sorting-source-wrap" @if($sourceType !== 'sorting') style="display:none" @endif>
        <label class="admin-label" for="sorting_id">Sorting batch</label>
        <select id="sorting_id" name="sorting_id" class="admin-input">
            <option value="">Select sorting batch</option>
            @foreach ($sortingStocks as $sorting)
                <option value="{{ $sorting->id }}" @selected(old('sorting_id', $roasting->sorting_id) == $sorting->id)>
                    {{ $sorting->rawMaterialStock?->item }} — {{ $sorting->rawMaterialStock?->batch_number }} ({{ number_format($sorting->remainingUsable(), 2) }} kg available)
                </option>
            @endforeach
        </select>
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
        <p class="mt-1 text-xs text-slate-500">Full quantity taken from source.</p>
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
</div>

@push('scripts')
<script>
(function () {
    const form = document.getElementById('roasting-form');
    if (!form) return;

    const sourceType = document.getElementById('source_type');
    const rawWrap = document.getElementById('raw-source-wrap');
    const sortingWrap = document.getElementById('sorting-source-wrap');
    const rawSelect = document.getElementById('raw_material_stock_id');
    const sortingSelect = document.getElementById('sorting_id');
    const batchInput = document.getElementById('batch');
    const rawBatches = JSON.parse(form.dataset.rawBatches || '{}');
    const sortingBatches = JSON.parse(form.dataset.sortingBatches || '{}');

    function syncSource() {
        const isRaw = sourceType.value === 'raw';
        rawWrap.style.display = isRaw ? '' : 'none';
        sortingWrap.style.display = isRaw ? 'none' : '';
        rawSelect.required = isRaw;
        sortingSelect.required = !isRaw;
        if (!isRaw) rawSelect.value = '';
        else sortingSelect.value = '';
        syncBatch();
    }

    function syncBatch() {
        if (batchInput.value && batchInput.dataset.userEdited === '1') return;
        const id = sourceType.value === 'raw' ? rawSelect.value : sortingSelect.value;
        const map = sourceType.value === 'raw' ? rawBatches : sortingBatches;
        if (id && map[id]) batchInput.value = map[id];
    }

    batchInput.addEventListener('input', () => { batchInput.dataset.userEdited = '1'; });
    sourceType.addEventListener('change', syncSource);
    rawSelect.addEventListener('change', syncBatch);
    sortingSelect.addEventListener('change', syncBatch);

    const roastingForm = form.closest('form');
    if (roastingForm) {
        roastingForm.addEventListener('submit', () => {
            const isRaw = sourceType.value === 'raw';
            rawSelect.disabled = !isRaw;
            sortingSelect.disabled = isRaw;
        });
    }

    syncSource();
})();
</script>
@endpush
