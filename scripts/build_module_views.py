#!/usr/bin/env python3
from pathlib import Path

BASE = Path(__file__).resolve().parents[1] / "resources/views/admin"


def fix(html: str) -> str:
    return html.replace("<motion>", "<div>").replace("</motion>", "</div>")


def save(rel: str, html: str) -> None:
    p = BASE / rel
    p.parent.mkdir(parents=True, exist_ok=True)
    p.write_text(fix(html).strip() + "\n", encoding="utf-8")


# MILLINGS
save("millings/_form.blade.php", r"""
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

    <div class="grid gap-4 md:grid-cols-2">
        <div>
            <label class="admin-label" for="date">Date</label>
            <input type="date" id="date" name="date" class="admin-input"
                   value="{{ old('date', optional($milling->date)->format('Y-m-d')) }}" required>
        </div>
        <div>
            <label class="admin-label" for="batch_number">Batch number</label>
            <input type="text" id="batch_number" name="batch_number" class="admin-input"
                   value="{{ old('batch_number', $milling->batch_number) }}" required>
        </motion>
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
        </motion>
        <div>
            <label class="admin-label" for="loss">Loss (kg)</label>
            <input type="number" step="0.01" min="0" id="loss" name="loss" class="admin-input"
                   value="{{ old('loss', $milling->loss ?? 0) }}" required>
        </motion>
        <div>
            <label class="admin-label" for="total_mixed_quantity">Total mixed (kg)</label>
            <input type="number" step="0.01" id="total_mixed_quantity" name="total_mixed_quantity" class="admin-input bg-slate-100" readonly
                   value="{{ old('total_mixed_quantity', $milling->total_mixed_quantity ?? 0) }}">
        </motion>
        <div>
            <label class="admin-label" for="output_flour">Output flour (kg)</label>
            <input type="number" step="0.01" id="output_flour" name="output_flour" class="admin-input bg-slate-100" readonly
                   value="{{ old('output_flour', $milling->output_flour ?? 0) }}">
        </motion>
    </div>

    <div class="mt-6">
        <div class="mb-2 flex items-center justify-between">
            <h3 class="font-medium text-slate-800">Ingredients</h3>
            <button type="button" id="add-ingredient" class="rounded border border-slate-300 px-3 py-1 text-sm">Add row</button>
        </motion>
        <div id="ingredients-list" class="space-y-3"></div>
    </div>
</motion>

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
        return `<div class="ingredient-row grid gap-3 rounded border border-slate-200 p-3 md:grid-cols-4" data-index="${i}">
            <div><label class="admin-label text-xs">Ingredient</label>
            <select name="items[${i}][type]" class="admin-input ingredient-type" required>
                <option value="">—</option>
                <option value="soy" ${type==='soy'?'selected':''}>Soy</option>
                <option value="sorghum" ${type==='sorghum'?'selected':''}>Sorghum</option>
                <option value="wheat" ${type==='wheat'?'selected':''}>Wheat</option>
                <option value="maize" ${type==='maize'?'selected':''}>Maize</option>
            </select></div>
            <div><label class="admin-label text-xs">Batch</label>
            <select name="items[${i}][stock_id]" class="admin-input ingredient-stock" required>${batchOptions(type)}</select></motion>
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
""")

# CRUD index/create/edit/show helpers
def crud_index(module, title, route, columns, row_blade):
    save(f"{module}/index.blade.php", f"""@extends('layouts.admin')
@section('title', '{title}')
@section('page_title', '{title}')
@section('header_actions')
    <a href="{{{{ route('admin.{route}.create') }}}}" class="admin-btn-primary rounded-md px-4 py-2 text-sm font-medium no-underline">Add {title.lower()}</a>
@endsection
@section('content')
    <div class="admin-card overflow-hidden">
        <form method="GET" class="flex flex-wrap gap-3 border-b border-slate-200 p-4">
            <input type="search" name="search" value="{{{{ $search }}}}" placeholder="Search…" class="admin-input max-w-xs">
            <button type="submit" class="rounded-md border border-slate-300 px-4 py-2 text-sm">Search</button>
        </form>
        <div class="overflow-x-auto">
            <table class="admin-table w-full text-left text-sm">
                <thead><tr class="border-b border-slate-200">{columns}<th class="px-4 py-3 text-right">Actions</th></tr></thead>
                <tbody>{row_blade}</tbody>
            </table>
        </div>
        <div class="border-t border-slate-200 p-4">{{{{ ${module}->links() }}}}</div>
    </div>
@endsection""")


def crud_create_edit(module, title, record_var):
    for action, method, btn in [
        ("create", "POST", "Save"),
        ("edit", "PUT", "Update"),
    ]:
        extra = "@method('PUT')\n        " if method == "PUT" else ""
        route_action = "store" if action == "create" else "update"
        route_param = "" if action == "create" else f", ${record_var}"
        cancel = f"admin.{module}.index" if action == "create" else f"admin.{module}.show"
        cancel_param = "" if action == "create" else f", ${record_var}"
        save(
            f"{module}/{action}.blade.php",
            f"""@extends('layouts.admin')
@section('title', '{btn} {title.lower()}')
@section('page_title', '{btn} {title.lower()}')
@section('content')
    <form method="POST" action="{{{{ route('admin.{module}.{route_action}'{route_param}) }}}}" class="admin-card max-w-4xl p-6">
        @csrf
        {extra}@include('admin.{module}._form')
        <div class="mt-6 flex gap-3">
            <button type="submit" class="admin-btn-primary rounded-md px-4 py-2 text-sm font-medium">{btn}</button>
            <a href="{{{{ route('{cancel}'{cancel_param}) }}}}" class="rounded-md border border-slate-300 px-4 py-2 text-sm no-underline">Cancel</a>
        </div>
    </form>
@endsection""",
        )


# Millings pages
crud_index(
    "millings",
    "Milling",
    "millings",
    '<th class="px-4 py-3">Date</th><th class="px-4 py-3">Batch</th><th class="px-4 py-3">Mixed</th><th class="px-4 py-3">Output</th><th class="px-4 py-3">Employee</th>',
    """@forelse ($millings as $milling)
                        <tr class="border-b border-slate-100 hover:bg-slate-50">
                            <td class="px-4 py-3">{{ optional($milling->date)->format('Y-m-d') }}</td>
                            <td class="px-4 py-3 font-medium">{{ $milling->batch_number }}</td>
                            <td class="px-4 py-3">{{ number_format($milling->total_mixed_quantity, 2) }} kg</td>
                            <td class="px-4 py-3">{{ number_format($milling->output_flour, 2) }} kg</td>
                            <td class="px-4 py-3">{{ $milling->employee?->full_name }}</td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('admin.millings.show', $milling) }}" class="text-[#10498C]">View</a>
                                <span class="text-slate-300">|</span>
                                <a href="{{ route('admin.millings.edit', $milling) }}" class="text-[#10498C]">Edit</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-8 text-center text-slate-500">No millings found.</td></tr>
                    @endforelse""",
)

save("millings/show.blade.php", r"""
@extends('layouts.admin')
@section('title', 'Milling')
@section('page_title', 'Milling')
@section('header_actions')
    <a href="{{ route('admin.millings.edit', $milling) }}" class="admin-btn-primary rounded-md px-4 py-2 text-sm no-underline">Edit</a>
@endsection
@section('content')
    <motion class="admin-card max-w-3xl p-6">
        <dl class="grid gap-4 sm:grid-cols-2">
            <div><dt class="text-sm text-slate-500">Date</dt><dd>{{ optional($milling->date)->format('Y-m-d') }}</dd></div>
            <div><dt class="text-sm text-slate-500">Batch</dt><dd>{{ $milling->batch_number }}</dd></div>
            <div><dt class="text-sm text-slate-500">Total mixed</dt><dd>{{ number_format($milling->total_mixed_quantity, 2) }} kg</dd></div>
            <div><dt class="text-sm text-slate-500">Loss</dt><dd>{{ number_format($milling->loss, 2) }} kg</dd></motion>
            <div><dt class="text-sm text-slate-500">Output flour</dt><dd>{{ number_format($milling->output_flour, 2) }} kg</dd></div>
            <div><dt class="text-sm text-slate-500">Employee</dt><dd>{{ $milling->employee?->full_name }}</dd></div>
        </dl>
        @if(is_array($milling->items) && count($milling->items))
            <h3 class="mt-6 font-medium">Ingredients</h3>
            <ul class="mt-2 list-disc pl-5 text-sm">
                @foreach($milling->items as $item)
                    <li>{{ ucfirst($item['type'] ?? '') }} — batch #{{ $item['stock_id'] ?? '' }}: {{ $item['quantity'] ?? 0 }} kg</li>
                @endforeach
            </ul>
        @endif
        <form method="POST" action="{{ route('admin.millings.destroy', $milling) }}" class="mt-8 border-t pt-6" onsubmit="return confirm('Delete?')">
            @csrf @method('DELETE')
            <button type="submit" class="rounded-md bg-red-600 px-4 py-2 text-sm text-white">Delete</button>
        </form>
    </motion>
@endsection
""")

crud_create_edit("millings", "Milling", "milling")

print("millings done")
