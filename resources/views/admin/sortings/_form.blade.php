<div class="admin-form-grid">
    <div><label class="admin-label" for="date">Date</label>
        <input type="date" id="date" name="date" class="admin-input" value="{{ old('date', optional($sorting->date)->format('Y-m-d')) }}" required></div>
    <div><label class="admin-label" for="raw_material_stock_id">Batch</label>
        <select id="raw_material_stock_id" name="raw_material_stock_id" class="admin-input" required>
            <option value="">Select batch</option>
            @foreach ($stocks as $stock)
                @php
                    $selected = (int) old('raw_material_stock_id', $sorting->raw_material_stock_id) === (int) $stock->id;
                    $available = $stock->hasAvailableStock();
                @endphp
                @if ($available || ($sorting->exists && $selected))
                    <option value="{{ $stock->id }}" @selected($selected) @disabled(! $available && ! $selected)>
                        {{ $stock->item }} — {{ $stock->batch_number }}
                        ({{ number_format($stock->remainingQuantity(), 2) }} kg{{ $available ? ' available' : ' — depleted' }})
                    </option>
                @endif
            @endforeach
        </select></div>
    <div><label class="admin-label" for="quantity_in">Quantity in (kg)</label>
        <input type="number" step="0.01" min="0.01" id="quantity_in" name="quantity_in" class="admin-input" value="{{ old('quantity_in', $sorting->quantity_in) }}" required>
        <p class="mt-1 text-xs text-slate-500">Full quantity taken from the source batch.</p></div>
    <div><label class="admin-label" for="loss">Loss (kg)</label>
        <input type="number" step="0.01" min="0" id="loss" name="loss" class="admin-input" value="{{ old('loss', $sorting->loss ?? 0) }}" required></div>
    @if ($sorting->exists)
        <div>
            <span class="admin-label">Quantity out</span>
            <p class="text-sm font-medium">{{ number_format($sorting->quantityOut(), 2) }} kg</p>
        </div>
        <div>
            <span class="admin-label">Remaining</span>
            <p class="text-sm font-medium">{{ number_format($sorting->remainingUsable(), 2) }} kg</p>
        </div>
    @endif
    <div><label class="admin-label" for="employee_id">Employee</label>
        <select id="employee_id" name="employee_id" class="admin-input" required>
            <option value="">Select employee</option>
            @foreach ($employees as $employee)
                <option value="{{ $employee->id }}" @selected(old('employee_id', $sorting->employee_id) == $employee->id)>{{ $employee->full_name }}</option>
            @endforeach
        </select></div>
</div>
