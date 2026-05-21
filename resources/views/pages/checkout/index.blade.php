@extends('layouts.site')

@section('title', 'Checkout')

@section('content')
    <section class="site-page-hero">
        <div class="site-container"><h1>Checkout</h1></div>
    </section>

    <section class="site-section">
        <div class="site-container">
            @if (session('error'))
                <div class="site-alert site-alert-error">{{ session('error') }}</div>
            @endif
            @if (! empty($cartErrors))
                <div class="site-alert site-alert-error">
                    <ul style="margin:0;padding-left:1.25rem">@foreach ($cartErrors as $e)<li>{{ $e }}</li>@endforeach</ul>
                </div>
            @endif

            <div class="site-checkout-layout">
                <form method="POST" action="{{ route('checkout.store') }}" class="site-checkout-form" id="checkout-form">
                    @csrf
                    <input type="hidden" name="idempotency_key" value="{{ $idempotencyKey }}">
                    <h2>Your details</h2>
                    <x-site.input label="Full name" name="name" :required="true" value="{{ old('name') }}" />
                    <x-site.input label="Phone" name="phone" type="tel" :required="true" value="{{ old('phone') }}" />
                    <x-site.input label="Email (optional)" name="email" type="email" value="{{ old('email') }}" />
                    <x-site.input label="Delivery address" name="address" type="textarea" :required="true" :value="old('address')" />
                    <x-site.input label="Order notes (optional)" name="notes" type="textarea" :value="old('notes')" />
                    <x-site.button type="submit" size="site-btn-lg" id="checkout-submit">Place order</x-site.button>
                </form>

                <aside class="site-cart-summary">
                    <h2>Order summary</h2>
                    @foreach ($items as $row)
                        <p class="site-checkout-line">
                            <span>{{ $row['item']['name'] }} × {{ $row['item']['quantity'] }}</span>
                            <strong>{{ number_format($row['line_total']) }} RWF</strong>
                        </p>
                    @endforeach
                    <p class="site-cart-summary-row" style="margin-top:1rem;border-top:1px solid var(--site-border);padding-top:1rem">
                        <span>Total</span><strong>{{ number_format($subtotal) }} RWF</strong>
                    </p>
                </aside>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
<script>
document.getElementById('checkout-form')?.addEventListener('submit', function () {
    const btn = document.getElementById('checkout-submit');
    if (btn) { btn.disabled = true; btn.textContent = 'Placing order…'; }
});
</script>
@endpush
