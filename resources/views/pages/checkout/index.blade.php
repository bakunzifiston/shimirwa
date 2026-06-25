@extends('layouts.site')

@section('title', 'Checkout')

@section('content')
    {{-- Progress steps --}}
    <div class="border-b border-slate-100 bg-white py-4">
        <div class="site-container">
            <ol class="flex items-center gap-0 text-sm">
                @foreach ([['Cart', 'cart.index'], ['Checkout', 'checkout.show'], ['Confirmation', null]] as $i => [$label, $route])
                    <li class="flex items-center {{ $i > 0 ? 'flex-1' : '' }}">
                        @if ($i > 0)
                            <div class="flex-1 h-px mx-2 {{ $i <= 1 ? 'bg-[#10498c]' : 'bg-slate-200' }}"></div>
                        @endif
                        <span class="flex items-center gap-2 font-medium
                                     {{ $i === 1 ? 'text-[#10498c]' : ($i === 0 ? 'text-slate-400' : 'text-slate-400') }}">
                            <span class="w-6 h-6 flex items-center justify-center rounded-full text-xs font-bold
                                         {{ $i === 0 ? 'bg-green-500 text-white' : ($i === 1 ? 'bg-[#10498c] text-white' : 'bg-slate-200 text-slate-500') }}">
                                @if ($i === 0)
                                    <svg width="10" height="10" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" d="m5 13 4 4L19 7"/></svg>
                                @else
                                    {{ $i + 1 }}
                                @endif
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
            @if (! empty($cartErrors))
                <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-200 text-red-700 text-sm">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach ($cartErrors as $e)<li>{{ $e }}</li>@endforeach
                    </ul>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-5 gap-8">
                {{-- Form --}}
                <div class="lg:col-span-3">
                    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
                        <h2 class="text-lg font-bold text-slate-900 mb-6 flex items-center gap-2">
                            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" class="text-[#10498c]" aria-hidden="true"><path stroke-linecap="round" d="M16 7a4 4 0 1 1-8 0 4 4 0 0 1 8 0zM12 14a7 7 0 0 0-7 7h14a7 7 0 0 0-7-7z"/></svg>
                            Your details
                        </h2>

                        <form method="POST" action="{{ route('checkout.store') }}" class="flex flex-col gap-4" id="checkout-form">
                            @csrf
                            <input type="hidden" name="idempotency_key" value="{{ $idempotencyKey }}">

                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1.5">
                                    Full name <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="name" value="{{ old('name') }}" required
                                       class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm
                                              focus:outline-none focus:ring-2 focus:ring-[#10498c]/30 focus:border-[#10498c] transition
                                              @error('name') border-red-400 bg-red-50 @enderror">
                                @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1.5">
                                    Phone <span class="text-red-500">*</span>
                                </label>
                                <input type="tel" name="phone" value="{{ old('phone') }}" required
                                       class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm
                                              focus:outline-none focus:ring-2 focus:ring-[#10498c]/30 focus:border-[#10498c] transition
                                              @error('phone') border-red-400 bg-red-50 @enderror">
                                @error('phone')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1.5">Email (optional)</label>
                                <input type="email" name="email" value="{{ old('email') }}"
                                       class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm
                                              focus:outline-none focus:ring-2 focus:ring-[#10498c]/30 focus:border-[#10498c] transition
                                              @error('email') border-red-400 bg-red-50 @enderror">
                                @error('email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1.5">
                                    Delivery address <span class="text-red-500">*</span>
                                </label>
                                <textarea name="address" rows="3" required
                                          class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm resize-none
                                                 focus:outline-none focus:ring-2 focus:ring-[#10498c]/30 focus:border-[#10498c] transition
                                                 @error('address') border-red-400 bg-red-50 @enderror">{{ old('address') }}</textarea>
                                @error('address')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1.5">Order notes (optional)</label>
                                <textarea name="notes" rows="2"
                                          class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm resize-none
                                                 focus:outline-none focus:ring-2 focus:ring-[#10498c]/30 focus:border-[#10498c] transition">{{ old('notes') }}</textarea>
                            </div>

                            <button type="submit" id="checkout-submit"
                                    class="mt-2 flex items-center justify-center gap-2 px-6 py-3.5 rounded-xl
                                           bg-[#10498c] text-white font-semibold hover:bg-[#082f57] transition-colors
                                           disabled:opacity-60 disabled:cursor-not-allowed">
                                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" d="M5 13l4 4L19 7"/></svg>
                                Place order
                            </button>
                        </form>
                    </div>
                </div>

                {{-- Order summary --}}
                <aside class="lg:col-span-2 lg:sticky lg:top-24 self-start">
                    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
                        <h2 class="text-base font-bold text-slate-900 mb-4">Order Summary</h2>
                        <div class="flex flex-col divide-y divide-slate-100">
                            @foreach ($items as $row)
                                <div class="flex items-center justify-between py-3 text-sm">
                                    <span class="text-slate-600">{{ $row['item']['name'] }} × {{ $row['item']['quantity'] }}</span>
                                    <strong class="text-slate-900 font-semibold">{{ number_format($row['line_total']) }} RWF</strong>
                                </div>
                            @endforeach
                        </div>
                        <div class="flex items-center justify-between pt-4 mt-2 border-t border-slate-200">
                            <span class="font-semibold text-slate-900">Total</span>
                            <strong class="text-lg font-bold" style="color:var(--site-primary)">{{ number_format($subtotal) }} RWF</strong>
                        </div>
                        <p class="mt-4 text-xs text-slate-400 text-center">Secure checkout — we'll confirm your order by phone</p>
                    </div>
                </aside>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
<script>
document.getElementById('checkout-form')?.addEventListener('submit', function () {
    const btn = document.getElementById('checkout-submit');
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg> Placing order…';
    }
});
</script>
@endpush
