<div class="admin-form-grid">
    <div><label class="admin-label" for="date">Date</label>
        <input type="date" id="date" name="date" class="admin-input" value="{{ old('date', optional($sorting->date)->format('Y-m-d')) }}" required></div>
    <div><label class="admin-label" for="raw_material_stock_id">Batch</label>
        <select id="raw_material_stock_id" name="raw_material_stock_id" class="admin-input" required>
            <option value="">Select batch</option>
            @foreach ($stocks as $stock)
                <option value="{{ $stock->id }}" @selected(old('raw_material_stock_id', $sorting->raw_material_stock_id) == $stock->id)>
                    {{ $stock->item }} — {{ $stock->batch_number }} ({{ $stock->quantity_in }} kg)
                </option>
            @endforeach
        </select></div>
    <div><label class="admin-label" for="quantity_in">Quantity (kg)</label>
        <input type="number" step="0.01" min="0.01" id="quantity_in" name="quantity_in" class="admin-input" value="{{ old('quantity_in', $sorting->quantity_in) }}" required></div>
    <div><label class="admin-label" for="loss">Loss (kg)</label>
        <input type="number" step="0.01" min="0" id="loss" name="loss" class="admin-input" value="{{ old('loss', $sorting->loss ?? 0) }}" required></div>
    <div><label class="admin-label" for="employee_id">Employee</label>
        <select id="employee_id" name="employee_id" class="admin-input" required>
            <option value="">Select employee</option>
            @foreach ($employees as $employee)
                <option value="{{ $employee->id }}" @selected(old('employee_id', $sorting->employee_id) == $employee->id)>{{ $employee->full_name }}</option>
            @endforeach
        </select></div>
</div>
