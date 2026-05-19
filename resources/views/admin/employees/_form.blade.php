<div class="admin-form-grid">
    <div>
        <label class="admin-label" for="full_name">Full name</label>
        <input id="full_name" name="full_name" class="admin-input" value="{{ old('full_name', $employee->full_name) }}" required>
    </div>
    <div>
        <label class="admin-label" for="national_id">National ID</label>
        <input id="national_id" name="national_id" class="admin-input" value="{{ old('national_id', $employee->national_id) }}" required>
    </div>
    <div>
        <label class="admin-label" for="phone_number">Phone</label>
        <input id="phone_number" name="phone_number" class="admin-input" value="{{ old('phone_number', $employee->phone_number) }}" required>
    </div>
    <div>
        <label class="admin-label" for="gender">Gender</label>
        <input id="gender" name="gender" class="admin-input" value="{{ old('gender', $employee->gender) }}" required>
    </div>
    <div>
        <label class="admin-label" for="province">Province</label>
        <input id="province" name="province" class="admin-input" value="{{ old('province', $employee->province) }}" required>
    </div>
    <div>
        <label class="admin-label" for="district">District</label>
        <input id="district" name="district" class="admin-input" value="{{ old('district', $employee->district) }}" required>
    </div>
    <div>
        <label class="admin-label" for="position">Position</label>
        <input id="position" name="position" class="admin-input" value="{{ old('position', $employee->position) }}" required>
    </div>
    <div>
        <label class="admin-label" for="start_date">Start date</label>
        <input id="start_date" type="date" name="start_date" class="admin-input"
               value="{{ old('start_date', optional($employee->start_date)->format('Y-m-d')) }}" required>
    </div>
</div>
