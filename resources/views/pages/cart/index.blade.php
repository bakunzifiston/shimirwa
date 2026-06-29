@extends('layouts.site')

@section('title', 'My Cart')

@section('content')

{{-- ── Progress steps ── --}}
<div class="progress-bar-wrap" style="background:var(--white);border-bottom:1px solid var(--border);padding:.85rem 0">
    <div class="sc">
        <ol style="display:flex;align-items:center;gap:0;list-style:none;margin:0;padding:0">
            @foreach([['Cart','cart.index'],['Checkout','checkout.show'],['Confirmation',null]] as $i => [$label,$rt])
                <li style="display:flex;align-items:center;{{ $i > 0 ? 'flex:1' : '' }}">
                    @if($i > 0)
                        <div style="flex:1;height:2px;margin:0 .6rem;background:{{ $i===1 ? 'var(--blue)' : 'var(--border)' }}"></div>
                    @endif
                    <span style="display:inline-flex;align-items:center;gap:.5rem;font-size:.82rem;font-weight:600;
                                 color:{{ $i===0 ? 'var(--blue)' : 'var(--slate-400)' }}">
                        <span style="width:1.6rem;height:1.6rem;display:flex;align-items:center;justify-content:center;
                                     border-radius:50%;font-size:.72rem;font-weight:800;
                                     background:{{ $i===0 ? 'var(--blue)' : 'var(--border)' }};
                                     color:{{ $i===0 ? 'white' : 'var(--slate-500)' }}">{{ $i+1 }}</span>
                        <span style="display:none" class="step-label">{{ $label }}</span>
                    </span>
                </li>
            @endforeach
        </ol>
    </div>
</div>

<section class="section" style="padding-top:2.25rem">
    <div class="sc">

        {{-- Alerts --}}
        @if(session('error'))
            <div class="alert alert-error" style="margin-bottom:1.5rem">
                <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/></svg>
                {{ session('error') }}
            </div>
        @endif
        @if(session('success'))
            <div class="alert alert-success" style="margin-bottom:1.5rem">
                <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                {{ session('success') }}
            </div>
        @endif

        @if($items->isEmpty())
            {{-- ── Empty state ── --}}
            <div style="text-align:center;padding:6rem 2rem">
                <div style="width:5.5rem;height:5.5rem;border-radius:50%;
                            background:var(--blue-light);
                            display:flex;align-items:center;justify-content:center;
                            margin:0 auto 1.75rem">
                    <svg width="36" height="36" fill="none" stroke="var(--blue)" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" d="M2 3h2l2 12h13l2-8H6M9 21a1 1 0 1 0 0-2 1 1 0 0 0 0 2Zm8 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z"/>
                    </svg>
                </div>
                <h2 style="font-size:1.25rem;font-weight:800;color:var(--slate-800);margin-bottom:.5rem">Your cart is empty</h2>
                <p style="color:var(--text-muted);font-size:.9rem;margin-bottom:2rem">
                    Browse our flour and grain products and add something to your cart.
                </p>
                <a href="{{ route('shop.index') }}" class="btn btn-primary">Browse Products</a>
            </div>

        @else

            @if($hasStockIssues)
                <div class="alert alert-warning" style="margin-bottom:1.5rem">
                    <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/></svg>
                    Some items have stock issues — please adjust quantities before checkout.
                </div>
            @endif

            {{-- ── Cart + Summary grid ── --}}
            <div class="cart-layout">

                {{-- ── Items column ── --}}
                <div style="display:flex;flex-direction:column;gap:1rem">

                    {{-- Column header --}}
                    <div style="display:flex;align-items:center;justify-content:space-between;padding-bottom:.75rem;border-bottom:1px solid var(--border)">
                        <h1 style="font-size:1.1rem;font-weight:800;color:var(--slate-900)">
                            Cart
                            <span style="margin-left:.5rem;font-size:.78rem;font-weight:600;
                                         padding:.2rem .65rem;border-radius:99px;
                                         background:var(--blue-light);color:var(--blue)">
                                {{ $items->count() }} item{{ $items->count()!==1?'s':'' }}
                            </span>
                        </h1>
                        <a href="{{ route('shop.index') }}"
                           style="font-size:.8rem;font-weight:600;color:var(--blue);text-decoration:none;display:inline-flex;align-items:center;gap:.3rem">
                            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
                            Continue shopping
                        </a>
                    </div>

                    @foreach($items as $row)
                        @php $item = $row['item']; $product = $row['product']; @endphp
                        <article style="display:flex;gap:1rem;padding:1.1rem;
                                        background:{{ !$row['stock_ok'] ? '#fef2f2' : 'var(--white)' }};
                                        border:1px solid {{ !$row['stock_ok'] ? '#fecaca' : 'var(--border)' }};
                                        border-radius:var(--radius-lg);
                                        box-shadow:var(--shadow-sm);
                                        align-items:flex-start">

                            {{-- Thumbnail --}}
                            <div style="width:5rem;height:5rem;flex-shrink:0;border-radius:var(--radius);
                                        overflow:hidden;border:1px solid var(--border);
                                        background:linear-gradient(135deg,var(--blue-light),var(--copper-light));
                                        display:flex;align-items:center;justify-content:center">
                                @if($product && $product->primaryImageUrl())
                                    <img src="{{ $product->primaryImageUrl() }}" alt="{{ $item['name'] }}"
                                         style="width:100%;height:100%;object-fit:cover">
                                @else
                                    <svg width="26" height="26" viewBox="0 0 64 64" fill="none" style="opacity:.3" aria-hidden="true">
                                        <circle cx="32" cy="32" r="20" fill="var(--blue)"/>
                                        <circle cx="32" cy="32" r="12" fill="var(--copper)"/>
                                    </svg>
                                @endif
                            </div>

                            {{-- Info --}}
                            <div style="flex:1;min-width:0">
                                <div style="font-size:.9375rem;font-weight:700;color:var(--slate-900);margin-bottom:.2rem;
                                            white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                                    @if($product)
                                        <a href="{{ route('shop.show', $product) }}"
                                           style="color:inherit;text-decoration:none;transition:color .15s"
                                           onmouseover="this.style.color='var(--blue)'"
                                           onmouseout="this.style.color='inherit'">{{ $item['name'] }}</a>
                                    @else
                                        {{ $item['name'] }}
                                    @endif
                                </div>
                                <div style="font-size:.78rem;color:var(--text-muted);margin-bottom:.65rem">
                                    {{ number_format($item['unit_price']) }} RWF per unit
                                </div>

                                @if(!$row['stock_ok'])
                                    <div style="font-size:.75rem;color:#dc2626;margin-bottom:.5rem;display:flex;align-items:center;gap:.35rem;font-weight:600">
                                        <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/></svg>
                                        Only {{ $row['available_stock'] }} in stock
                                    </div>
                                @endif

                                {{-- Stepper + remove --}}
                                <div style="display:flex;align-items:center;gap:.75rem;flex-wrap:wrap">
                                    <form method="POST" action="{{ route('cart.update', $item['product_id']) }}"
                                          style="display:inline-flex;align-items:center">
                                        @csrf @method('PATCH')
                                        <div style="display:inline-flex;align-items:center;border:1.5px solid var(--border);
                                                    border-radius:var(--radius-sm);overflow:hidden;background:var(--white)">
                                            <button type="button"
                                                    onclick="const i=this.nextElementSibling;i.value=Math.max(0,+i.value-1);this.form.submit()"
                                                    style="width:2.2rem;height:2.2rem;border:none;background:transparent;
                                                           font-size:1.1rem;font-weight:700;color:var(--slate-500);cursor:pointer"
                                                    onmouseover="this.style.background='var(--slate-100)'"
                                                    onmouseout="this.style.background='transparent'"
                                                    aria-label="Decrease">−</button>
                                            <input type="number" name="quantity" value="{{ $item['quantity'] }}"
                                                   min="0" max="{{ $row['available_stock'] }}"
                                                   style="width:2.75rem;height:2.2rem;border:none;
                                                          border-left:1.5px solid var(--border);
                                                          border-right:1.5px solid var(--border);
                                                          text-align:center;font-size:.875rem;font-weight:700;
                                                          color:var(--text);font-family:var(--font)"
                                                   aria-label="Quantity">
                                            <button type="button"
                                                    onclick="const i=this.previousElementSibling;i.value=Math.min({{ $row['available_stock'] }},+i.value+1);this.form.submit()"
                                                    style="width:2.2rem;height:2.2rem;border:none;background:transparent;
                                                           font-size:1.1rem;font-weight:700;color:var(--slate-500);cursor:pointer"
                                                    onmouseover="this.style.background='var(--slate-100)'"
                                                    onmouseout="this.style.background='transparent'"
                                                    aria-label="Increase">+</button>
                                        </div>
                                    </form>

                                    <form method="POST" action="{{ route('cart.remove', $item['product_id']) }}">
                                        @csrf @method('DELETE')
                                        <button type="submit"
                                                style="width:2.2rem;height:2.2rem;border:1.5px solid var(--border);
                                                       border-radius:var(--radius-sm);background:transparent;
                                                       cursor:pointer;display:flex;align-items:center;justify-content:center;
                                                       color:var(--slate-400);transition:all .15s"
                                                onmouseover="this.style.background='#fef2f2';this.style.borderColor='#fecaca';this.style.color='#dc2626'"
                                                onmouseout="this.style.background='transparent';this.style.borderColor='var(--border)';this.style.color='var(--slate-400)'"
                                                aria-label="Remove item">
                                            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M6 18 18 6M6 6l12 12"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </div>

                            {{-- Line total --}}
                            <div style="flex-shrink:0;text-align:right;padding-left:.25rem">
                                <div style="font-size:1rem;font-weight:900;color:var(--blue)">
                                    {{ number_format($row['line_total']) }}
                                </div>
                                <div style="font-size:.7rem;color:var(--text-muted);font-weight:500">RWF</div>
                            </div>
                        </article>
                    @endforeach
                </div>

                {{-- ── Order summary ── --}}
                <aside style="position:sticky;top:calc(var(--header-h) + 1.5rem);align-self:start">
                    <div style="background:var(--white);border:1px solid var(--border);
                                border-radius:var(--radius-xl);box-shadow:var(--shadow);overflow:hidden">

                        {{-- Header --}}
                        <div style="padding:1.25rem 1.5rem;background:linear-gradient(135deg,var(--blue),var(--blue-dark));
                                    color:white">
                            <div style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;
                                        opacity:.75;margin-bottom:.25rem">Summary</div>
                            <div style="font-size:1.1rem;font-weight:800">Order Summary</div>
                        </div>

                        <div style="padding:1.25rem 1.5rem">
                            {{-- Subtotal row --}}
                            <div style="display:flex;align-items:center;justify-content:space-between;
                                        padding:.75rem 0;border-bottom:1px solid var(--border);margin-bottom:.75rem">
                                <span style="font-size:.875rem;color:var(--text-muted)">Subtotal</span>
                                <strong style="font-size:1rem;font-weight:800;color:var(--slate-900)">
                                    {{ number_format($subtotal) }} RWF
                                </strong>
                            </div>

                            {{-- Shipping note --}}
                            <div style="font-size:.78rem;color:var(--text-muted);margin-bottom:1.25rem;
                                        padding:.65rem;border-radius:var(--radius-sm);background:var(--slate-50);
                                        display:flex;align-items:flex-start;gap:.5rem">
                                <svg width="14" height="14" fill="none" stroke="var(--blue)" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0;margin-top:.1rem"><path stroke-linecap="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25"/></svg>
                                Delivery cost confirmed at checkout
                            </div>

                            {{-- CTA --}}
                            @if($hasStockIssues)
                                <div style="display:flex;align-items:center;justify-content:center;
                                            padding:.9rem 1.25rem;border-radius:var(--radius);
                                            background:var(--slate-100);color:var(--slate-400);
                                            font-size:.875rem;font-weight:700;cursor:not-allowed">
                                    Fix stock issues first
                                </div>
                            @else
                                <a href="{{ route('checkout.show') }}"
                                   style="display:flex;align-items:center;justify-content:center;gap:.5rem;
                                          padding:.9rem 1.25rem;border-radius:var(--radius);
                                          background:var(--blue);color:white;
                                          font-size:.9rem;font-weight:700;text-decoration:none;
                                          transition:background .15s;box-shadow:0 4px 14px rgba(16,73,140,.3)"
                                   onmouseover="this.style.background='var(--blue-dark)'"
                                   onmouseout="this.style.background='var(--blue)'">
                                    Proceed to Checkout
                                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="m9 18 6-6-6-6"/></svg>
                                </a>
                            @endif

                            <a href="{{ route('shop.index') }}"
                               style="display:flex;align-items:center;justify-content:center;
                                      padding:.75rem;margin-top:.65rem;border-radius:var(--radius);
                                      border:1.5px solid var(--border);color:var(--slate-600);
                                      font-size:.84rem;font-weight:600;text-decoration:none;
                                      transition:all .15s"
                               onmouseover="this.style.borderColor='var(--blue)';this.style.color='var(--blue)'"
                               onmouseout="this.style.borderColor='var(--border)';this.style.color='var(--slate-600)'">
                                Continue Shopping
                            </a>
                        </div>

                        {{-- Trust strip --}}
                        <div style="padding:.85rem 1.5rem;background:var(--slate-50);border-top:1px solid var(--border);
                                    display:flex;flex-direction:column;gap:.4rem">
                            @foreach([['Secure checkout','M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z'],['100% local product','M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0ZM19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z']] as [$trust,$path])
                                <div style="display:flex;align-items:center;gap:.5rem;font-size:.75rem;color:var(--text-muted);font-weight:500">
                                    <svg width="13" height="13" fill="none" stroke="var(--blue)" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" d="{{ $path }}"/></svg>
                                    {{ $trust }}
                                </div>
                            @endforeach
                        </div>
                    </div>
                </aside>
            </div>
        @endif
    </div>
</section>

@endsection
