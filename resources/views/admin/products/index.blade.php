@extends('layouts.admin')

@section('title', 'Products')
@section('page_title', 'Product Management')
@section('page_subtitle', 'Name, description, price, stock, images & status')

@section('content')
    <x-admin.listing :paginator="$products" :show-search="false">
        <x-slot:toolbar>
            <form method="GET" class="admin-filter-bar">
                <div class="admin-search-wrap">
                    <x-admin.icon name="search" class="!absolute !left-3 !top-1/2 !h-4 !w-4 !-translate-y-1/2" style="color: var(--admin-text-subtle)" />
                    <input type="search" name="search" value="{{ $search }}" placeholder="Search products…" class="admin-input">
                </div>
                <div class="admin-filter-bar__filters">
                    <select name="status" class="admin-input" aria-label="Product status">
                        <option value="">All statuses</option>
                        <option value="active" @selected($status === 'active')>Active</option>
                        <option value="inactive" @selected($status === 'inactive')>Inactive</option>
                    </select>
                </div>
                <div class="admin-filter-bar__actions">
                    <button type="submit" class="admin-btn admin-btn-secondary admin-btn-sm">Apply</button>
                    @if ($search || $status)
                        <a href="{{ route('admin.products.index') }}" class="admin-btn admin-btn-ghost admin-btn-sm">Clear</a>
                    @endif
                </div>
            </form>
        </x-slot:toolbar>
        <x-slot:actions>
            <a href="{{ route('admin.products.create') }}" class="admin-btn admin-btn-primary admin-btn-sm">
                <x-admin.icon name="plus" class="!h-4 !w-4" />
                Add product
            </a>
        </x-slot:actions>
        <x-slot:head>
            <th>Image</th>
            <th>Name</th>
            <th>Price</th>
            <th>Discount</th>
            <th>Stock</th>
            <th>Status</th>
            <th class="text-right">Actions</th>
        </x-slot:head>
        @forelse ($products as $product)
            @php
                $product->loadMissing('images');
                $thumb = $product->primaryImageUrl();
            @endphp
            <tr>
                <td>
                    @if ($thumb)
                        <img src="{{ $thumb }}" alt="" class="h-10 w-10 rounded object-cover border">
                    @else
                        <span class="inline-flex h-10 w-10 items-center justify-center rounded border text-xs opacity-50">—</span>
                    @endif
                </td>
                <td class="cell-primary">{{ $product->name }}</td>
                <td>{{ number_format($product->price) }} RWF</td>
                <td>
                    @if ($product->discount_price)
                        {{ number_format($product->discount_price) }} RWF
                    @else
                        <span class="opacity-50">—</span>
                    @endif
                </td>
                <td>{{ number_format($product->stock_quantity) }}</td>
                <td>
                    <span class="admin-badge {{ $product->status === 'active' ? 'admin-badge--primary' : '' }}">
                        {{ $product->status === 'active' ? 'Active' : 'Inactive' }}
                    </span>
                </td>
                <td class="text-right">
                    <x-admin.row-actions
                        :view-route="route('admin.products.show', $product)"
                        :edit-route="route('admin.products.edit', $product)"
                    />
                </td>
            </tr>
        @empty
            <x-admin.empty-state colspan="7" message="No products yet. Add your first product." />
        @endforelse
    </x-admin.listing>
@endsection
