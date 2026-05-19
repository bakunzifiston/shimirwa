@php
    $districts = config('rwanda.districts');
    $clientTypes = config('rwanda.client_types');
    $role = old('role', $client->role ?? 'client');
@endphp

<div class="admin-form-grid">
    <div class="md:col-span-2">
        <label class="admin-label" for="full_name">Full name</label>
        <input id="full_name" name="full_name" class="admin-input" value="{{ old('full_name', $client->full_name) }}" required>
    </div>

    <div>
        <label class="admin-label" for="client_type">Client type</label>
        <select id="client_type" name="client_type" class="admin-input" required>
            @foreach ($clientTypes as $value => $label)
                <option value="{{ $value }}" @selected(old('client_type', $client->client_type) === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="admin-label" for="role">Role</label>
        <select id="role" name="role" class="admin-input" required
                onchange="document.getElementById('supplier-code-wrap').style.display = this.value === 'supplier' ? 'block' : 'none'">
            <option value="client" @selected($role === 'client')>Client</option>
            <option value="supplier" @selected($role === 'supplier')>Supplier</option>
        </select>
    </div>

    <div id="supplier-code-wrap" style="{{ $role === 'supplier' ? '' : 'display:none' }}">
        <label class="admin-label" for="supplier_code">Supplier code</label>
        <input id="supplier_code" name="supplier_code" class="admin-input"
               value="{{ old('supplier_code', $client->supplier_code) }}">
    </div>

    <div>
        <label class="admin-label" for="phone">Phone</label>
        <input id="phone" name="phone" class="admin-input" value="{{ old('phone', $client->phone) }}">
    </div>

    <div>
        <label class="admin-label" for="email">Email</label>
        <input id="email" type="email" name="email" class="admin-input" value="{{ old('email', $client->email) }}">
    </div>

    <div class="md:col-span-2">
        <label class="admin-label" for="address">District</label>
        <select id="address" name="address" class="admin-input" required>
            <option value="">Select district</option>
            @foreach ($districts as $district)
                <option value="{{ $district }}" @selected(old('address', $client->address) === $district)>{{ $district }}</option>
            @endforeach
        </select>
    </div>
</div>
