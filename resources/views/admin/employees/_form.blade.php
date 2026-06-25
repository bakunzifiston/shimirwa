@php
    $savedSpecialties = old('specialties',
        isset($employee) && $employee->specialties
            ? explode(',', $employee->specialties)
            : []
    );
    if (is_string($savedSpecialties)) {
        $savedSpecialties = explode(',', $savedSpecialties);
    }
@endphp

<div class="admin-form-grid">
    <div>
        <label class="admin-label" for="full_name">Full name</label>
        <input id="full_name" name="full_name" class="admin-input" value="{{ old('full_name', $employee->full_name ?? '') }}" required>
    </div>
    <div>
        <label class="admin-label" for="national_id">National ID</label>
        <input id="national_id" name="national_id" class="admin-input" value="{{ old('national_id', $employee->national_id ?? '') }}" required>
    </div>
    <div>
        <label class="admin-label" for="phone_number">Phone</label>
        <input id="phone_number" name="phone_number" class="admin-input" value="{{ old('phone_number', $employee->phone_number ?? '') }}" required>
    </div>
    <div>
        <label class="admin-label" for="gender">Gender</label>
        <select id="gender" name="gender" class="admin-input" required>
            <option value="">Select gender</option>
            @foreach (['Male', 'Female', 'Other'] as $g)
                <option value="{{ $g }}" @selected(old('gender', $employee->gender ?? '') === $g)>{{ $g }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="admin-label" for="province">Province</label>
        <input id="province" name="province" class="admin-input" value="{{ old('province', $employee->province ?? '') }}" required>
    </div>
    <div>
        <label class="admin-label" for="district">District</label>
        <input id="district" name="district" class="admin-input" value="{{ old('district', $employee->district ?? '') }}" required>
    </div>
    <div>
        <label class="admin-label" for="position">Position / Job title</label>
        <input id="position" name="position" class="admin-input" value="{{ old('position', $employee->position ?? '') }}" required
               placeholder="e.g. Packaging Supervisor">
    </div>
    <div>
        <label class="admin-label" for="start_date">Start date</label>
        <input id="start_date" type="date" name="start_date" class="admin-input"
               value="{{ old('start_date', optional($employee->start_date ?? null)->format('Y-m-d')) }}" required>
    </div>

    <div class="md:col-span-2">
        <label class="admin-label">Work areas / Specialties</label>
        <p class="mb-2 text-xs" style="color:var(--admin-text-muted)">
            Select all production stages this employee can be assigned to. This controls which dropdowns they appear in.
        </p>
        <input type="hidden" name="specialties" value="">
        <div class="flex flex-wrap gap-3" id="specialties-wrap">
            @foreach (\App\Models\Employee::SPECIALTIES as $key => $label)
                <label class="flex items-center gap-2 cursor-pointer select-none">
                    <input type="checkbox" name="specialties_check[]" value="{{ $key }}"
                           class="h-4 w-4 rounded specialty-cb"
                           @checked(in_array($key, $savedSpecialties, true))>
                    <span class="text-sm font-medium">{{ $label }}</span>
                </label>
            @endforeach
        </div>
    </div>
</div>

<script>
(function () {
    const wrap = document.getElementById('specialties-wrap');
    if (!wrap) return;
    const hidden = wrap.closest('form').querySelector('input[name="specialties"]');
    function sync() {
        const checked = [...wrap.querySelectorAll('.specialty-cb:checked')].map(cb => cb.value);
        hidden.value = checked.join(',');
    }
    wrap.querySelectorAll('.specialty-cb').forEach(cb => cb.addEventListener('change', sync));
    sync();
})();
</script>
