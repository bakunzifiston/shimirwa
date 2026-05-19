#!/usr/bin/env python3
from pathlib import Path

BASE = Path(__file__).resolve().parents[1] / "resources/views/admin"
D, ED = "<div>", "</motion>"


def fix(s: str) -> str:
    return s.replace("<motion>", D).replace("</motion>", "</div>")


def save(rel: str, content: str) -> None:
    p = BASE / rel
    p.parent.mkdir(parents=True, exist_ok=True)
    p.write_text(fix(content).strip() + "\n")


save("emballages/_form.blade.php", r"""
@php
    $ptype = old('packaging_type', $emballage->packaging_type ?? '1kg');
@endphp

<div id="emballage-form" class="grid gap-4 md:grid-cols-2"
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
    <motion>
        <label class="admin-label" for="item" id="item-label">Number of units</label>
        <input type="number" step="1" min="1" id="item" name="item" class="admin-input"
               value="{{ old('item', $emballage->item) }}" required>
    </motion>
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
""")

save("sales/_form.blade.php", r"""
@php
    $batches = old('batches', $sale->batches ?? [['emballage_id' => '', 'quantity' => 1, 'unit_price' => 0, 'line_total' => 0]]);
    if (empty($batches)) {
        $batches = [['emballage_id' => '', 'quantity' => 1, 'unit_price' => 0, 'line_total' => 0]];
    }
    $embOpts = $emballages->map(fn ($e) => [
        'id' => $e->id,
        'label' => 'Batch: ' . ($e->packaging_batch_id ?? '—') . ' | ' . strtoupper($e->packaging_type ?? '') . ' | Stock: ' . $e->item,
        'price' => (float) ($e->unit_price ?? 0),
    ]);
@endphp

<div class="grid gap-4 md:grid-cols-2">
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
</motion>

<div id="sale-batches-form" class="mt-6" data-emballages='@json($embOpts)'>
    <div class="mb-2 flex items-center justify-between">
        <h3 class="font-medium text-slate-800">Batches sold</h3>
        <button type="button" id="add-batch" class="rounded border border-slate-300 px-3 py-1 text-sm">Add batch</button>
    </div>
    <div id="batches-list" class="space-y-3"></div>
</div>

@push('scripts')
<script>
(function () {
    const root = document.getElementById('sale-batches-form');
    if (!root) return;
    const emballages = JSON.parse(root.dataset.emballages || '[]');
    const list = document.getElementById('batches-list');
    const initial = @json($batches);

    function opts(selected) {
        return '<option value="">Select packaging batch</option>' +
            emballages.map(e => `<option value="${e.id}" data-price="${e.price}" ${String(selected)==String(e.id)?'selected':''}>${e.label}</option>`).join('');
    }

    function rowHtml(i, row) {
        return `<div class="batch-row grid gap-3 rounded border border-slate-200 p-3 md:grid-cols-5" data-index="${i}">
            <div class="md:col-span-2"><label class="admin-label text-xs">Packaging batch</label>
            <select name="batches[${i}][emballage_id]" class="admin-input batch-emb" required>${opts(row.emballage_id)}</select></div>
            <div><label class="admin-label text-xs">Qty</label>
            <input type="number" min="1" name="batches[${i}][quantity]" class="admin-input batch-qty" value="${row.quantity ?? 1}" required></div>
            <div><label class="admin-label text-xs">Unit price</label>
            <input type="number" step="0.01" min="0" name="batches[${i}][unit_price]" class="admin-input batch-price" value="${row.unit_price ?? 0}" required></div>
            <div><label class="admin-label text-xs">Line total</label>
            <input type="number" step="0.01" min="0" name="batches[${i}][line_total]" class="admin-input batch-total bg-slate-100" value="${row.line_total ?? 0}" readonly></motion>
            <div class="flex items-end"><button type="button" class="remove-batch rounded border px-2 py-1 text-sm text-red-600">Remove</button></div>
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

    document.getElementById('add-batch').addEventListener('click', () => addRow({}));
    initial.forEach(r => addRow(r));
    if (!list.children.length) addRow({});
})();
</script>
@endpush
""")

save("users/_form.blade.php", r"""
<div class="grid gap-4 md:grid-cols-2 max-w-xl">
    <div>
        <label class="admin-label" for="name">Name</label>
        <input type="text" id="name" name="name" class="admin-input" value="{{ old('name', $user->name) }}" required>
    </div>
    <div>
        <label class="admin-label" for="email">Email</label>
        <input type="email" id="email" name="email" class="admin-input" value="{{ old('email', $user->email) }}" required>
    </div>
    <div>
        <label class="admin-label" for="password">Password</label>
        <input type="password" id="password" name="password" class="admin-input" @if($user->exists) placeholder="Leave blank to keep current" @else required @endif>
    </div>
    <div>
        <label class="admin-label" for="password_confirmation">Confirm password</label>
        <input type="password" id="password_confirmation" name="password_confirmation" class="admin-input">
    </div>
</motion>
""")

# Standard CRUD pages
modules = [
    ("emballages", "Packaging", "emballage", "emballages", "$emballages", "$emballage", """
        <td class="px-4 py-3">{{ optional($emballage->date)->format('Y-m-d') }}</td>
        <td class="px-4 py-3">{{ $emballage->packaging_batch_id }}</td>
        <td class="px-4 py-3">{{ strtoupper($emballage->packaging_type ?? '') }}</td>
        <td class="px-4 py-3">{{ $emballage->item }} units</td>
        <td class="px-4 py-3">{{ number_format($emballage->quantity, 2) }} kg</td>
    """, 5),
    ("sales", "Sales", "sale", "sales", "$sales", "$sale", """
        <td class="px-4 py-3">{{ optional($sale->date)->format('Y-m-d') }}</td>
        <td class="px-4 py-3">{{ $sale->item }}</td>
        <td class="px-4 py-3">{{ $sale->client?->full_name }}</td>
        <td class="px-4 py-3">{{ $sale->employee?->full_name }}</td>
    """, 4),
    ("users", "Users", "user", "users", "$users", "$user", """
        <td class="px-4 py-3 font-medium">{{ $user->name }}</td>
        <td class="px-4 py-3">{{ $user->email }}</td>
        <td class="px-4 py-3">{{ $user->created_at?->format('Y-m-d') }}</td>
    """, 3),
]

for folder, title, singular, route, coll, record, cols, colspan in modules:
    save(f"{folder}/index.blade.php", f"""@extends('layouts.admin')
@section('title', '{title}')
@section('page_title', '{title}')
@section('header_actions')
    <a href="{{{{ route('admin.{route}.create') }}}}" class="admin-btn-primary rounded-md px-4 py-2 text-sm font-medium no-underline">Add {title.lower()[:-1] if title.endswith('s') else title.lower()}</a>
@endsection
@section('content')
    <div class="admin-card overflow-hidden">
        <form method="GET" class="flex flex-wrap gap-3 border-b border-slate-200 p-4">
            <input type="search" name="search" value="{{{{ $search }}}}" class="admin-input max-w-xs" placeholder="Search…">
            <button type="submit" class="rounded-md border border-slate-300 px-4 py-2 text-sm">Search</button>
        </form>
        <div class="overflow-x-auto">
            <table class="admin-table w-full text-left text-sm">
                <thead><tr class="border-b border-slate-200">
                    {cols}
                    <th class="px-4 py-3 text-right">Actions</th>
                </tr></thead>
                <tbody>
                    @forelse ({coll[1:]} as ${singular})
                        <tr class="border-b border-slate-100 hover:bg-slate-50">
                            {cols}
                            <td class="px-4 py-3 text-right">
                                <a href="{{{{ route('admin.{route}.show', ${singular}) }}}}" class="text-[#10498C]">View</a>
                                <span class="text-slate-300">|</span>
                                <a href="{{{{ route('admin.{route}.edit', ${singular}) }}}}" class="text-[#10498C]">Edit</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="{colspan + 1}" class="px-4 py-8 text-center text-slate-500">No records found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-200 p-4">{{{{ {coll}.links() }}}}</motion>
    </div>
@endsection""")

    for act, method, btn in [("create", "", "Save"), ("edit", "@method('PUT')\n        ", "Update")]:
        r_act = "store" if act == "create" else "update"
        r_param = "" if act == "create" else f", ${singular}"
        cancel_r = f"admin.{route}.index" if act == "create" else f"admin.{route}.show"
        cancel_p = "" if act == "create" else f", ${singular}"
        save(f"{folder}/{act}.blade.php", f"""@extends('layouts.admin')
@section('title', '{btn} {singular}')
@section('page_title', '{btn} {singular}')
@section('content')
    <form method="POST" action="{{{{ route('admin.{route}.{r_act}'{r_param}) }}}}" class="admin-card max-w-4xl p-6">
        @csrf
        {method}@include('admin.{folder}._form')
        <div class="mt-6 flex gap-3">
            <button type="submit" class="admin-btn-primary rounded-md px-4 py-2 text-sm font-medium">{btn}</button>
            <a href="{{{{ route('{cancel_r}'{cancel_p}) }}}}" class="rounded-md border border-slate-300 px-4 py-2 text-sm no-underline">Cancel</a>
        </div>
    </form>
@endsection""")

print("remaining views generated")
