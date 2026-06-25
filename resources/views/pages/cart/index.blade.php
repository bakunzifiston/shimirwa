@extends('layouts.site')

@section('title', 'Cart')

@section('content')
    {{-- Progress steps --}}
    <div class="border-b border-slate-100 bg-white py-4">
        <div class="site-container">
            <ol class="flex items-center gap-0 text-sm">
                @foreach ([['Cart', 'cart.index'], ['Checkout', 'checkout.show'], ['Confirmation', null]] as $i => [$label, $route])
                    <li class="flex items-center {{ $i > 0 ? 'flex-1' : '' }}">
                        @if ($i > 0)
                            <div class="flex-1 h-px mx-2 {{ $i === 1 ? 'bg-[#10498c]' : 'bg-slate-200' }}"></div>
                        @endif
                        <span class="flex items-center gap-2 font-medium
                                     {{ $i === 0 ? 'text-[#10498c]' : 'text-slate-400' }}">
                            <span class="w-6 h-6 flex items-center justify-center rounded-full text-xs font-bold
                                         {{ $i === 0 ? 'bg-[#10498c] text-white' : 'bg-slate-200 text-slate-500' }}">
                                {{ $i + 1 }}
                            </span>
                            <span class="hidden sm:inline">{{ $label }}</span>
                        </span>
                    </li>
                @endforeach
            </ol>
        </div>
    </div>

    <section class="py-10">
        <div class="site-container">
            @if (session('error'))
                <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-200 text-red-700 text-sm">{{ session('error') }}</div>
            @endif
            @if (session('success'))
                <div class="mb-6 p-4 rounded-xl bg-green-50 border border-green-200 text-green-700 text-sm">{{ session('success') }}</div>
            @endif

            @if ($items->isEmpty())
                {{-- Empty state --}}
                <div class="flex flex-col items-center py-24 text-center">
                    <div class="w-20 h-20 rounded-full bg-slate-100 flex items-center justify-center mb-5">
                        <svg width="36" height="36" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" class="text-slate-400" aria-hidden="true">
                            <path stroke-linecap="round" d="M2 3h2l2 12h13l2-8H6M9 21a1 1 0 1 0 0-2 1 1 0 0 0 0 2Zm8 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z"/>
                        </svg>
                    </div>
                    <h2 class="text-xl font-semibold text-slate-700 mb-2">Your cart is empty</h2>
                    <p class="text-slate-500 text-sm mb-6">Looks like you haven't added anything yet.</p>
                    <a href="{{ route('shop.index') }}"
                       class="px-6 py-3 rounded-xl bg-[#10498c] text-white font-semibold hover:bg-[#082f57] transition-colors">
                        Browse products
                    </a>
                </div>
            @else
                @if ($hasStockIssues)
                    <div class="mb-6 p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-sm flex items-start gap-3">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" class="shrink-0 mt-0.5" aria-hidden="true"><path stroke-linecap="round" d="M12 9v4m0 4h.01M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/></svg>
                        Some items have stock issues. Update quantities before checkout.
                    </div>
                @endif

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    {{-- Cart items --}}
                    <div class="lg:col-span-2 flex flex-col gap-4">
                        @foreach ($items as $row)
                            @php $item = $row['item']; $product = $row['product']; @endphp
                            <article class="flex gap-4 p-4 bg-white rounded-2xl border
                                            {{ ! $row['stock_ok'] ? 'border-red-200 bg-red-50/30' : 'border-slate-100' }}
                                            shadow-sm">
                                {{-- Thumbnail --}}
                                <div class="w-20 h-20 rounded-xl overflow-hidden shrink-0 bg-gradient-to-br from-blue-50 to-amber-50 flex items-center justify-center">
                                    @if ($product && $product->primaryImageUrl())
                                        <img src="{{ $product->primaryImageUrl() }}" alt="{{ $item['name'] }}" class="w-full h-full object-cover">
                                    @else
                                        <svg width="28" height="28" viewBox="0 0 64 64" fill="none" class="opacity-25" aria-hidden="true">
                                            <path d="M32 8C18 8 8 20 8 32s10 24 24 24 24-10 24-24S46 8 32 8z" fill="#10498c"/>
                                        </svg>
                                    @endif
                                </div>

                                {{-- Info --}}
                                <div class="flex-1 min-w-0">
                                    <h3 class="text-sm font-semibold text-slate-800 truncate">
                                        @if ($product)
                                            <a href="{{ route('shop.show', $product) }}" class="hover:text-[#10498c] transition-colors">{{ $item['name'] }}</a>
                                        @else
                                            {{ $item['name'] }}
                                        @endif
                                    </h3>
                                    <p class="text-xs text-slate-500 mt-0.5">{{ number_format($item['unit_price']) }} RWF each</p>
                                    @if (! $row['stock_ok'])
                                        <p class="text-xs text-red-600 mt-1 flex items-center gap-1">
                                            <svg width="12" height="12" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 1 1-16 0 8 8 0 0 1 16 0zm-7-4a1 1 0 1 1-2 0 1 1 0 0 1 2 0zM9 9a1 1 0 0 0 0 2v3a1 1 0 0 0 2 0V9H9z" clip-rule="evenodd"/></svg>
                                            Only {{ $row['available_stock'] }} available
                                        </p>
                                    @endif

                                    {{-- Quantity stepper + actions --}}
                                    <div class="flex items-center gap-2 mt-3">
                                        <form method="POST" action="{{ route('cart.update', $item['product_id']) }}" class="flex items-center">
                                            @csrf @method('PATCH')
                                            <div class="flex items-center border border-slate-200 rounded-lg overflow-hidden">
                                                <button type="button"
                                                        onclick="const i=this.nextElementSibling;i.value=Math.max(0,+i.value-1);this.form.submit()"
                                                        class="w-8 h-8 flex items-center justify-center text-slate-500 hover:bg-slate-50 transition-colors font-bold">−</button>
                                                <input type="number" name="quantity" value="{{ $item['quantity'] }}"
                                                       min="0" max="{{ $row['available_stock'] }}"
                                                       class="w-10 h-8 text-center text-sm border-0 focus:outline-none font-medium">
                                                <button type="button"
                                                        onclick="const i=this.previousElementSibling;i.value=Math.min({{ $row['available_stock'] }},+i.value+1);this.form.submit()"
                                                        class="w-8 h-8 flex items-center justify-center text-slate-500 hover:bg-slate-50 transition-colors font-bold">+</button>
                                            </div>
                                        </form>

                                        <form method="POST" action="{{ route('cart.remove', $item['product_id']) }}">
                                            @csrf @method('DELETE')
                                            <button type="submit"
                                                    class="p-2 rounded-lg text-slate-400 hover:text-red-500 hover:bg-red-50 transition-colors"
                                                    aria-label="Remove">
                                                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M18 6 6 18M6 6l12 12"/></svg>
                                            </button>
                                        </form>
                                    </div>
                                </div>

                                {{-- Line total --}}
                                <div class="text-right shrink-0">
                                    <span class="text-sm font-bold text-slate-800">{{ number_format($row['line_total']) }} RWF</span>
                                </div>
                            </article>
                        @endforeach
                    </div>

                    {{-- Order summary --}}
                    <aside class="lg:sticky lg:top-24 self-start">
                        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
                            <h2 class="text-base font-bold text-slate-900 mb-4">Order Summary</h2>
                            <div class="flex items-center justify-between py-3 border-b border-slate-100">
                                <span class="text-sm text-slate-600">Subtotal</span>
                                <strong class="text-sm font-semibold text-slate-900">{{ number_format($subtotal) }} RWF</strong>
                            </div>
                            <div class="mt-4 flex flex-col gap-2">
                                @if ($hasStockIssues)
                                    <span class="flex items-center justify-center px-5 py-3 rounded-xl bg-slate-200 text-slate-500 text-sm font-semibold cursor-not-allowed">
                                        Proceed to checkout
                                    </span>
                                @else
                                    <a href="{{ route('checkout.show') }}"
                                       class="flex items-center justify-center gap-2 px-5 py-3 rounded-xl
                                              bg-[#10498c] text-white font-semibold text-sm hover:bg-[#082f57] transition-colors">
                                        Proceed to checkout
                                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" d="m9 18 6-6-6-6"/></svg>
                                    </a>
                                @endif
                                <a href="{{ route('shop.index') }}"
                                   class="flex items-center justify-center px-5 py-3 rounded-xl border border-slate-200
                                          text-slate-600 font-medium text-sm hover:bg-slate-50 transition-colors">
                                    Continue shopping
                                </a>
                            </div>
                        </div>
                    </aside>
                </div>
            @endif
        </div>
    </section>
@endsection
