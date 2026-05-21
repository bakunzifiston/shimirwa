@extends('layouts.admin')

@section('title', $product->name)
@section('page_title', $product->name)
@section('page_subtitle', 'Product details')

@section('content')
    <div class="admin-card max-w-3xl">
        <dl class="admin-detail-list">
            <dt>Name</dt>
            <dd>{{ $product->name }}</dd>

            <dt>Description</dt>
            <dd class="whitespace-pre-wrap">{{ $product->description ?: '—' }}</dd>

            <dt>Price</dt>
            <dd>{{ number_format($product->price) }} RWF</dd>

            <dt>Discount price</dt>
            <dd>
                @if ($product->discount_price)
                    {{ number_format($product->discount_price) }} RWF
                    <span class="admin-badge admin-badge--primary ml-2">Sale price</span>
                @else
                    —
                @endif
            </dd>

            <dt>Selling price</dt>
            <dd><strong>{{ number_format($product->effectivePrice()) }} RWF</strong></dd>

            <dt>Stock quantity</dt>
            <dd>{{ number_format($product->stock_quantity) }}</dd>

            <dt>Images</dt>
            <dd>
                @if ($product->images->isNotEmpty())
                    <div class="flex flex-wrap gap-2">
                        @foreach ($product->images as $image)
                            <img src="{{ $image->url() }}" alt="{{ $product->name }}" class="h-28 w-28 rounded-lg object-cover border">
                        @endforeach
                    </div>
                @else
                    <span style="color: var(--admin-text-subtle)">No images uploaded</span>
                @endif
            </dd>

            <dt>Status</dt>
            <dd>
                <span class="admin-badge {{ $product->status === 'active' ? 'admin-badge--primary' : '' }}">
                    {{ $product->status === 'active' ? 'Active' : 'Inactive' }}
                </span>
            </dd>

            <dt>Shop URL</dt>
            <dd><code class="text-sm">{{ $product->slug }}</code></dd>
        </dl>

        <div class="flex flex-wrap gap-2 pt-6 border-t" style="border-color: var(--admin-border)">
            <a href="{{ route('admin.products.edit', $product) }}" class="admin-btn admin-btn-primary admin-btn-sm">Edit product</a>
            <a href="{{ route('shop.show', $product) }}" class="admin-btn admin-btn-secondary admin-btn-sm" target="_blank" rel="noopener">View on shop</a>
            <form method="POST" action="{{ route('admin.products.destroy', $product) }}" onsubmit="return confirm('Delete this product?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="admin-btn admin-btn-ghost admin-btn-sm" style="color:#b91c1c">Delete</button>
            </form>
        </div>
    </div>
@endsection
