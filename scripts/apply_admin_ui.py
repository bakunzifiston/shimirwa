#!/usr/bin/env python3
"""Apply redesigned UI patterns to admin CRUD views."""
from pathlib import Path

BASE = Path(__file__).resolve().parents[1] / "resources/views/admin"

FORM_WRAPPER_CREATE = """@extends('layouts.admin')

@section('title', '{title}')
@section('page_title', '{page_title}')
@section('page_subtitle', '{subtitle}')

@section('content')
    <form method="POST" action="{{{{ route('{store_route}') }}}}" class="admin-card max-w-4xl">
        @csrf
        <div class="admin-card-header">
            <h2 class="admin-card-title">{section_title}</h2>
        </div>
        <div class="admin-card-body">
            @include('admin.{folder}._form')
            <x-admin.form-actions :cancel-route="route('{cancel_index}')" submit-label="{submit}" />
        </div>
    </form>
@endsection
"""

FORM_WRAPPER_EDIT = """@extends('layouts.admin')

@section('title', '{title}')
@section('page_title', '{page_title}')

@section('content')
    <form method="POST" action="{{{{ route('{update_route}', ${singular}) }}}}" class="admin-card max-w-4xl">
        @csrf
        @method('PUT')
        <motion class="admin-card-header">
            <h2 class="admin-card-title">{section_title}</h2>
        </motion>
        <div class="admin-card-body">
            @include('admin.{folder}._form')
            <x-admin.form-actions :cancel-route="route('{cancel_show}', ${singular})" submit-label="{submit}" />
        </div>
    </form>
@endsection
"""

INDEXES = [
    {
        "folder": "clients",
        "title": "Clients & Suppliers",
        "subtitle": "Manage buyers and suppliers",
        "route": "admin.clients",
        "collection": "clients",
        "singular": "client",
        "add": "Add client",
        "placeholder": "Search clients…",
        "colspan": 6,
        "headers": "<th>Name</th><th>Role</th><th>Phone</th><th>District</th><th class=\"text-right\">Actions</th>",
        "row": """<td class="cell-primary">{{ $client->full_name }}</td>
                <td><span class="admin-badge admin-badge--primary">{{ ucfirst($client->role) }}</span></td>
                <td>{{ $client->phone_number }}</td>
                <td>{{ $client->district }}</td>""",
    },
    {
        "folder": "raw-material-stocks",
        "title": "Reception of materials",
        "subtitle": "Raw material stock intake",
        "route": "admin.raw-material-stocks",
        "collection": "stocks",
        "singular": "stock",
        "add": "Receive materials",
        "placeholder": "Search batch, item, supplier…",
        "colspan": 6,
        "headers": "<th>Date</th><th>Item</th><th>Batch</th><th>Qty in</th><th>Supplier</th><th class=\"text-right\">Actions</th>",
        "row": """<td>{{ optional($stock->date)->format('Y-m-d') }}</td>
                <td class="cell-primary">{{ $stock->item }}</td>
                <td>{{ $stock->batch_number }}</td>
                <td>{{ number_format($stock->quantity_in, 2) }} kg</td>
                <td>{{ $stock->client?->full_name }}</td>""",
    },
    {
        "folder": "roastings",
        "title": "Roasting",
        "subtitle": "Roasting production records",
        "route": "admin.roastings",
        "collection": "roastings",
        "singular": "roasting",
        "add": "Add roasting",
        "placeholder": "Search batch…",
        "colspan": 6,
        "headers": "<th>Date</th><th>Batch</th><th>Qty in</th><th>Loss</th><th>Chef</th><th class=\"text-right\">Actions</th>",
        "row": """<td>{{ optional($roasting->date)->format('Y-m-d') }}</td>
                <td class="cell-primary">{{ $roasting->batch }}</td>
                <td>{{ number_format($roasting->quantity_in, 2) }} kg</td>
                <td>{{ number_format($roasting->loss, 2) }} kg</td>
                <td>{{ $roasting->chef?->full_name }}</td>""",
    },
    {
        "folder": "sortings",
        "title": "Sorting",
        "subtitle": "Sorting production records",
        "route": "admin.sortings",
        "collection": "sortings",
        "singular": "sorting",
        "add": "Add sorting",
        "placeholder": "Search batch…",
        "colspan": 7,
        "headers": "<th>Date</th><th>Item</th><th>Batch</th><th>Qty in</th><th>Loss</th><th>Employee</th><th class=\"text-right\">Actions</th>",
        "row": """<td>{{ optional($sorting->date)->format('Y-m-d') }}</td>
                <td>{{ $sorting->rawMaterialStock?->item }}</td>
                <td class="cell-primary">{{ $sorting->rawMaterialStock?->batch_number }}</td>
                <td>{{ number_format($sorting->quantity_in, 2) }} kg</td>
                <td>{{ number_format($sorting->loss, 2) }} kg</td>
                <td>{{ $sorting->employee?->full_name }}</td>""",
    },
    {
        "folder": "millings",
        "title": "Milling",
        "subtitle": "Milling batches and flour output",
        "route": "admin.millings",
        "collection": "millings",
        "singular": "milling",
        "add": "Add milling",
        "placeholder": "Search batch…",
        "colspan": 6,
        "headers": "<th>Date</th><th>Batch</th><th>Mixed</th><th>Output</th><th>Employee</th><th class=\"text-right\">Actions</th>",
        "row": """<td>{{ optional($milling->date)->format('Y-m-d') }}</td>
                <td class="cell-primary">{{ $milling->batch_number }}</td>
                <td>{{ number_format($milling->total_mixed_quantity, 2) }} kg</td>
                <td>{{ number_format($milling->output_flour, 2) }} kg</td>
                <td>{{ $milling->employee?->full_name }}</td>""",
    },
    {
        "folder": "emballages",
        "title": "Packaging",
        "subtitle": "Packaging and emballage records",
        "route": "admin.emballages",
        "collection": "emballages",
        "singular": "emballage",
        "add": "Add packaging",
        "placeholder": "Search packaging…",
        "colspan": 6,
        "headers": "<th>Date</th><th>Batch ID</th><th>Type</th><th>Units</th><th>Flour (kg)</th><th class=\"text-right\">Actions</th>",
        "row": """<td>{{ optional($emballage->date)->format('Y-m-d') }}</td>
                <td class="cell-primary">{{ $emballage->packaging_batch_id }}</td>
                <td><span class="admin-badge admin-badge--primary">{{ strtoupper($emballage->packaging_type ?? '') }}</span></td>
                <td>{{ $emballage->item }}</td>
                <td>{{ number_format($emballage->quantity, 2) }}</td>""",
    },
    {
        "folder": "sales",
        "title": "Sales",
        "subtitle": "Sales and distribution",
        "route": "admin.sales",
        "collection": "sales",
        "singular": "sale",
        "add": "Add sale",
        "placeholder": "Search sales…",
        "colspan": 5,
        "headers": "<th>Date</th><th>Product</th><th>Client</th><th>Employee</th><th class=\"text-right\">Actions</th>",
        "row": """<td>{{ optional($sale->date)->format('Y-m-d') }}</td>
                <td class="cell-primary">{{ $sale->item }}</td>
                <td>{{ $sale->client?->full_name }}</td>
                <td>{{ $sale->employee?->full_name }}</td>""",
    },
    {
        "folder": "users",
        "title": "Users",
        "subtitle": "System access accounts",
        "route": "admin.users",
        "collection": "users",
        "singular": "user",
        "add": "Add user",
        "placeholder": "Search users…",
        "colspan": 4,
        "headers": "<th>Name</th><th>Email</th><th>Created</th><th class=\"text-right\">Actions</th>",
        "row": """<td class="cell-primary">{{ $user->name }}</td>
                <td>{{ $user->email }}</td>
                <td>{{ $user->created_at?->format('Y-m-d') }}</td>""",
    },
]


def fix(s: str) -> str:
    return s.replace("<motion>", "<div>").replace("</motion>", "</div>")


def write_index(m: dict) -> None:
    route = m["route"]
    coll = m["collection"]
    sing = m["singular"]
    content = f"""@extends('layouts.admin')

@section('title', '{m["title"]}')
@section('page_title', '{m["title"]}')
@section('page_subtitle', '{m["subtitle"]}')

@section('header_actions')
    <a href="{{{{ route('{route}.create') }}}}" class="admin-btn admin-btn-primary admin-btn-sm">
        <x-admin.icon name="plus" class="!h-4 !w-4" />
        {m["add"]}
    </a>
@endsection

@section('content')
    <x-admin.listing
        :paginator="${coll}"
        :search="$search"
        :clear-route="route('{route}.index')"
        placeholder="{m["placeholder"]}"
    >
        <x-slot:head>
            {m["headers"]}
        </x-slot:head>

        @forelse (${coll} as ${sing})
            <tr>
                {m["row"]}
                <td class="text-right">
                    <x-admin.row-actions
                        :view-route="route('{route}.show', ${sing})"
                        :edit-route="route('{route}.edit', ${sing})"
                    />
                </td>
            </tr>
        @empty
            <x-admin.empty-state colspan="{m["colspan"]}" />
        @endforelse
    </x-admin.listing>
@endsection
"""
    (BASE / m["folder"] / "index.blade.php").write_text(content, encoding="utf-8")


def write_form_pages(m: dict) -> None:
    folder = m["folder"]
    sing = m["singular"]
    route = m["route"]
    label = m["title"]

    create = FORM_WRAPPER_CREATE.format(
        title=f"Add {sing}",
        page_title=f"Add {sing}",
        subtitle=m["subtitle"],
        store_route=f"{route}.store",
        folder=folder,
        cancel_index=f"{route}.index",
        submit=f"Save",
        section_title=f"{label} details",
    )
    edit = FORM_WRAPPER_EDIT.format(
        title=f"Edit {sing}",
        page_title=f"Edit {sing}",
        update_route=f"{route}.update",
        folder=folder,
        singular=sing,
        cancel_show=f"{route}.show",
        submit="Update",
        section_title=f"{label} details",
    )
    (BASE / folder / "create.blade.php").write_text(fix(create), encoding="utf-8")
    (BASE / folder / "edit.blade.php").write_text(fix(edit), encoding="utf-8")


for m in INDEXES:
    write_index(m)
    write_form_pages(m)
    print("Updated", m["folder"])

# employees already done manually
print("done")
