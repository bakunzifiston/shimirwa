@extends('layouts.site')

@section('title', $product->name)
@section('meta_description', $product->shortDescription(160))

@section('content')

{{-- ── Breadcrumb bar ── --}}
<div style="background:var(--slate-50);border-bottom:1px solid var(--border);padding:.9rem 0">
    <div class="sc">
        <nav class="breadcrumb" aria-label="Breadcrumb">
            <a href="{{ route('home') }}">Home</a>
            <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" d="m9 18 6-6-6-6"/></svg>
            <a href="{{ route('shop.index') }}">Shop</a>
            <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" d="m9 18 6-6-6-6"/></svg>
            <span>{{ $product->name }}</span>
        </nav>
    </div>
</div>

<section class="section" style="padding-top:2.5rem">
    <div class="sc">

        {{-- Alerts --}}
        @if(session('error'))
            <div class="alert alert-error">
                <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/></svg>
                {{ session('error') }}
            </div>
        @endif
        @if(session('success'))
            <div class="alert alert-success">
                <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                {{ session('success') }}
            </div>
        @endif

        {{-- ── Product Layout ── --}}
        <div style="display:grid;grid-template-columns:1fr;gap:2.5rem;align-items:start" class="product-detail-grid reveal">

            {{-- ── Gallery ── --}}
            <div>
                {{-- Main image --}}
                <div id="main-img-wrap"
                     style="border-radius:var(--radius-xl);overflow:hidden;border:1px solid var(--border);
                            background:linear-gradient(135deg,var(--blue-light),var(--copper-light));
                            aspect-ratio:1;position:relative;box-shadow:var(--shadow)">
                    @if($product->images->isNotEmpty())
                        <img id="main-img" src="{{ $product->images->first()->url() }}" alt="{{ $product->name }}"
                             style="width:100%;height:100%;object-fit:cover;transition:opacity .3s">
                    @else
                        <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center">
                            <svg width="90" height="90" viewBox="0 0 64 64" fill="none" style="opacity:.2" aria-hidden="true">
                                <circle cx="32" cy="32" r="24" fill="var(--blue)"/>
                                <circle cx="32" cy="32" r="14" fill="var(--copper)"/>
                                <path d="M32 22v20M22 32h20" stroke="white" stroke-width="3" stroke-linecap="round"/>
                            </svg>
                        </div>
                    @endif

                    {{-- Badge --}}
                    @unless($product->isInStock())
                        <span style="position:absolute;top:1rem;left:1rem;font-size:.72rem;font-weight:700;
                                     padding:.35rem .85rem;border-radius:99px;background:rgba(15,23,42,.75);
                                     color:white;backdrop-filter:blur(8px)">Out of stock</span>
                    @endunless
                    @if($product->discount_price && (float)$product->discount_price < (float)$product->price)
                        <span style="position:absolute;top:1rem;left:1rem;font-size:.72rem;font-weight:700;
                                     padding:.35rem .85rem;border-radius:99px;background:#ef4444;color:white">Sale</span>
                    @endif
                </div>

                {{-- Thumbnails --}}
                @if($product->images->count() > 1)
                    <div style="display:flex;gap:.6rem;margin-top:.85rem;overflow-x:auto;padding-bottom:.25rem">
                        @foreach($product->images as $img)
                            <button type="button"
                                    onclick="swapImg('{{ $img->url() }}', this)"
                                    style="flex-shrink:0;width:4.5rem;height:4.5rem;border-radius:var(--radius);
                                           overflow:hidden;border:2.5px solid {{ $loop->first ? 'var(--blue)' : 'var(--border)' }};
                                           transition:border-color .15s;cursor:pointer;background:none;padding:0"
                                    class="thumb-btn {{ $loop->first ? 'active-thumb' : '' }}">
                                <img src="{{ $img->url() }}" alt="" style="width:100%;height:100%;object-fit:cover">
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- ── Product info ── --}}
            <div style="display:flex;flex-direction:column;gap:1.5rem">

                {{-- Name & price --}}
                <div>
                    <h1 style="font-size:clamp(1.5rem,3.5vw,2rem);font-weight:900;color:var(--slate-900);
                               letter-spacing:-.02em;margin-bottom:.75rem;line-height:1.15">
                        {{ $product->name }}
                    </h1>

                    <div style="display:flex;align-items:baseline;flex-wrap:wrap;gap:.75rem;margin-bottom:1rem">
                        <span style="font-size:1.75rem;font-weight:900;color:var(--blue);letter-spacing:-.02em">
                            {{ number_format($product->effectivePrice()) }} RWF
                        </span>
                        @if($product->discount_price && (float)$product->discount_price < (float)$product->price)
                            <span style="font-size:1rem;color:var(--slate-400);text-decoration:line-through">
                                {{ number_format($product->price) }} RWF
                            </span>
                            <span style="font-size:.72rem;font-weight:700;padding:.25rem .65rem;border-radius:99px;background:#fef2f2;color:#dc2626">
                                Save {{ number_format((float)$product->price - (float)$product->discount_price) }} RWF
                            </span>
                        @endif
                    </div>

                    {{-- Stock badge --}}
                    @if($product->isInStock())
                        <span style="display:inline-flex;align-items:center;gap:.45rem;font-size:.8rem;font-weight:600;
                                     padding:.4rem .9rem;border-radius:99px;background:#f0fdf4;color:#16a34a;border:1px solid #bbf7d0">
                            <span style="width:.5rem;height:.5rem;border-radius:50%;background:#22c55e;flex-shrink:0"></span>
                            In stock — {{ $product->stock_quantity }} available
                        </span>
                    @else
                        <span style="display:inline-flex;align-items:center;gap:.45rem;font-size:.8rem;font-weight:600;
                                     padding:.4rem .9rem;border-radius:99px;background:var(--slate-100);color:var(--slate-500);border:1px solid var(--border)">
                            <span style="width:.5rem;height:.5rem;border-radius:50%;background:var(--slate-400);flex-shrink:0"></span>
                            Out of stock
                        </span>
                    @endif
                </div>

                {{-- Description --}}
                <div style="padding:1.25rem;background:var(--slate-50);border:1px solid var(--border);border-radius:var(--radius-lg)">
                    <p style="font-size:.9375rem;color:var(--slate-600);line-height:1.75">
                        {!! nl2br(e($product->description)) !!}
                    </p>
                </div>

                {{-- Add to cart / Out of stock ── --}}
                @if($product->isInStock())
                    <form method="POST" action="{{ route('cart.add', $product) }}"
                          style="padding:1.25rem;background:var(--white);border:1px solid var(--border);
                                 border-radius:var(--radius-lg);box-shadow:var(--shadow-sm)">
                        @csrf
                        <label style="display:block;font-size:.8rem;font-weight:700;color:var(--slate-600);
                                      text-transform:uppercase;letter-spacing:.06em;margin-bottom:.65rem">
                            Quantity
                        </label>
                        {{-- Qty stepper --}}
                        <div style="display:flex;gap:1rem;align-items:center;margin-bottom:1rem">
                            <div style="display:inline-flex;align-items:center;border:1.5px solid var(--border);
                                        border-radius:var(--radius-sm);overflow:hidden;background:var(--white)">
                                <button type="button"
                                        onclick="const i=document.getElementById('qty');i.value=Math.max(1,+i.value-1)"
                                        style="width:2.75rem;height:2.75rem;border:none;background:transparent;
                                               font-size:1.25rem;font-weight:700;color:var(--slate-500);cursor:pointer;
                                               transition:background .15s"
                                        onmouseover="this.style.background='var(--slate-100)'"
                                        onmouseout="this.style.background='transparent'"
                                        aria-label="Decrease quantity">−</button>
                                <input type="number" id="qty" name="quantity" value="1"
                                       min="1" max="{{ $product->stock_quantity }}"
                                       style="width:3.5rem;border:none;border-left:1.5px solid var(--border);
                                              border-right:1.5px solid var(--border);text-align:center;
                                              font-size:.9375rem;font-weight:700;color:var(--text);
                                              height:2.75rem;font-family:var(--font)"
                                       aria-label="Quantity">
                                <button type="button"
                                        onclick="const i=document.getElementById('qty');i.value=Math.min({{ $product->stock_quantity }},+i.value+1)"
                                        style="width:2.75rem;height:2.75rem;border:none;background:transparent;
                                               font-size:1.25rem;font-weight:700;color:var(--slate-500);cursor:pointer;
                                               transition:background .15s"
                                        onmouseover="this.style.background='var(--slate-100)'"
                                        onmouseout="this.style.background='transparent'"
                                        aria-label="Increase quantity">+</button>
                            </div>
                            <span style="font-size:.8rem;color:var(--text-muted)">Max {{ $product->stock_quantity }}</span>
                        </div>

                        <div style="display:flex;gap:.75rem;flex-wrap:wrap">
                            <button type="submit" class="btn btn-primary" style="flex:1;justify-content:center">
                                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" d="M2 3h2l2 12h13l2-8H6M9 21a1 1 0 1 0 0-2 1 1 0 0 0 0 2Zm8 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z"/>
                                </svg>
                                Add to Cart
                            </button>
                            <a href="{{ route('contact') }}" class="btn btn-ghost" style="flex-shrink:0">Bulk Order</a>
                        </div>
                    </form>
                @else
                    <div style="padding:1.25rem;background:var(--white);border:1px solid var(--border);
                                border-radius:var(--radius-lg);text-align:center">
                        <p style="font-size:.9rem;color:var(--text-muted);margin-bottom:1rem">
                            This product is currently out of stock. Contact us to place an order or be notified when it's back.
                        </p>
                        <a href="{{ route('contact') }}" class="btn btn-primary" style="justify-content:center;width:100%">
                            Contact us to order
                        </a>
                    </div>
                @endif

                {{-- Trust badges --}}
                <div style="display:flex;flex-wrap:wrap;gap:.75rem">
                    @foreach([['icon'=>'<path stroke-linecap="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>','label'=>'Certified Quality'],['icon'=>'<path stroke-linecap="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/><path stroke-linecap="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/>','label'=>'Made in Rwanda'],['icon'=>'<path stroke-linecap="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25"/>','label'=>'Fast Delivery']] as $badge)
                        <div style="display:inline-flex;align-items:center;gap:.4rem;font-size:.78rem;
                                    font-weight:600;color:var(--slate-600);
                                    padding:.4rem .85rem;border-radius:99px;
                                    background:var(--slate-50);border:1px solid var(--border)">
                            <svg width="13" height="13" fill="none" stroke="var(--blue)" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">{!! $badge['icon'] !!}</svg>
                            {{ $badge['label'] }}
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- ── Related Products ── --}}
        @if($related->isNotEmpty())
            <div style="margin-top:4rem;padding-top:3rem;border-top:1px solid var(--border)">
                <div class="section-lead" style="margin-bottom:2rem">
                    <span class="eyebrow eyebrow--blue reveal">More Products</span>
                    <h2 class="section-title reveal" style="font-size:1.5rem">You might also like</h2>
                </div>
                <div class="products-grid">
                    @foreach($related as $item)
                        <x-site.product-card :product="$item" />
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</section>

@endsection

@push('scripts')
<script>
function swapImg(src, btn) {
    const img = document.getElementById('main-img');
    if (img) {
        img.style.opacity = '0';
        setTimeout(() => { img.src = src; img.style.opacity = '1'; }, 200);
    }
    document.querySelectorAll('.thumb-btn').forEach(b => {
        b.style.borderColor = 'var(--border)';
    });
    btn.style.borderColor = 'var(--blue)';
}
</script>
@endpush
