@extends('layouts.site')

@section('title', 'Order Confirmed — '.$order->order_number)

@section('content')

{{-- ── Progress steps (all done) ── --}}
<div style="background:var(--white);border-bottom:1px solid var(--border);padding:.85rem 0">
    <div class="sc">
        <ol style="display:flex;align-items:center;gap:0;list-style:none;margin:0;padding:0">
            @foreach([['Cart','cart.index'],['Checkout','checkout.show'],['Confirmation',null]] as $i => [$label,$rt])
                <li style="display:flex;align-items:center;{{ $i > 0 ? 'flex:1' : '' }}">
                    @if($i > 0)
                        <div style="flex:1;height:2px;margin:0 .6rem;background:#22c55e"></div>
                    @endif
                    <span style="display:inline-flex;align-items:center;gap:.5rem;font-size:.82rem;font-weight:600;
                                 color:{{ $i===2 ? 'var(--blue)' : 'var(--slate-400)' }}">
                        <span style="width:1.6rem;height:1.6rem;display:flex;align-items:center;justify-content:center;
                                     border-radius:50%;font-size:.72rem;font-weight:800;
                                     background:#22c55e;color:white">
                            <svg width="9" height="9" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" d="m5 13 4 4L19 7"/></svg>
                        </span>
                        <span class="step-label">{{ $label }}</span>
                    </span>
                </li>
            @endforeach
        </ol>
    </div>
</div>

<section class="section" style="padding-top:3rem;padding-bottom:4rem">
    <div class="sc" style="max-width:660px;margin:0 auto">

        {{-- ── Success header ── --}}
        <div style="text-align:center;margin-bottom:2.5rem">
            <div style="width:4.5rem;height:4.5rem;border-radius:50%;background:#dcfce7;
                        display:flex;align-items:center;justify-content:center;margin:0 auto 1.25rem">
                <svg width="28" height="28" fill="none" stroke="#16a34a" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" d="m5 13 4 4L19 7"/>
                </svg>
            </div>
            <h1 style="font-size:clamp(1.4rem,3vw,1.75rem);font-weight:900;color:var(--slate-900);
                       letter-spacing:-.02em;margin-bottom:.5rem">
                Order Request Submitted!
            </h1>
            <p style="color:var(--text-muted);font-size:.9375rem;line-height:1.6">
                Thank you, <strong style="color:var(--slate-800)">{{ $order->customer->name }}</strong>.<br>
                Your order <strong style="color:var(--blue)">{{ $order->order_number }}</strong> has been received.
            </p>
        </div>

        {{-- ── What happens next ── --}}
        <div style="background:var(--blue-light);border:1px solid rgba(16,73,140,.15);
                    border-radius:var(--radius-lg);padding:1.25rem;margin-bottom:1.5rem;
                    display:flex;align-items:flex-start;gap:.85rem">
            <div style="width:2.25rem;height:2.25rem;flex-shrink:0;border-radius:var(--radius-sm);
                        background:var(--blue);display:flex;align-items:center;justify-content:center">
                <svg width="14" height="14" fill="none" stroke="white" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z"/>
                </svg>
            </div>
            <div>
                <div style="font-size:.875rem;font-weight:800;color:var(--blue-dark);margin-bottom:.25rem">
                    We will call you to confirm
                </div>
                <div style="font-size:.82rem;color:var(--blue-dark);opacity:.85;line-height:1.55">
                    Our team will call you at <strong>{{ $order->customer->phone }}</strong> to confirm delivery details and arrange payment before dispatch.
                </div>
            </div>
        </div>

        {{-- ── Order summary card ── --}}
        <div style="background:var(--white);border:1px solid var(--border);
                    border-radius:var(--radius-xl);box-shadow:var(--shadow);overflow:hidden;margin-bottom:2rem">
            <div style="padding:1rem 1.5rem;background:linear-gradient(135deg,var(--blue),var(--blue-dark));color:white;
                        display:flex;justify-content:space-between;align-items:center">
                <div>
                    <div style="font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;opacity:.75;margin-bottom:.15rem">Order</div>
                    <div style="font-size:.95rem;font-weight:800;font-family:monospace;letter-spacing:.04em">{{ $order->order_number }}</div>
                </div>
                <div style="text-align:right">
                    <div style="font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;opacity:.75;margin-bottom:.15rem">Total</div>
                    <div style="font-size:1.2rem;font-weight:900">{{ number_format($order->total) }} RWF</div>
                </div>
            </div>

            <div style="padding:1.25rem 1.5rem">
                {{-- Customer info --}}
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;
                            padding-bottom:1rem;margin-bottom:1rem;border-bottom:1px solid var(--border)">
                    <div>
                        <div style="font-size:.7rem;text-transform:uppercase;letter-spacing:.05em;color:var(--text-muted);font-weight:700;margin-bottom:.2rem">Customer</div>
                        <div style="font-size:.875rem;font-weight:700;color:var(--slate-800)">{{ $order->customer->name }}</div>
                    </div>
                    <div>
                        <div style="font-size:.7rem;text-transform:uppercase;letter-spacing:.05em;color:var(--text-muted);font-weight:700;margin-bottom:.2rem">Phone</div>
                        <div style="font-size:.875rem;font-weight:700;color:var(--blue)">{{ $order->customer->phone }}</div>
                    </div>
                    <div style="grid-column:1/-1">
                        <div style="font-size:.7rem;text-transform:uppercase;letter-spacing:.05em;color:var(--text-muted);font-weight:700;margin-bottom:.2rem">Delivery Address</div>
                        <div style="font-size:.84rem;color:var(--slate-700)">{{ $order->customer->address }}</div>
                    </div>
                </div>

                {{-- Items --}}
                <div style="display:flex;flex-direction:column;gap:.35rem;margin-bottom:.85rem">
                    @foreach($order->items as $item)
                        <div style="display:flex;justify-content:space-between;align-items:center;font-size:.84rem">
                            <span style="color:var(--slate-600)">{{ $item->product_name }} <span style="opacity:.65">× {{ $item->quantity }}</span></span>
                            <span style="font-weight:700;color:var(--slate-800)">{{ number_format($item->line_total) }} RWF</span>
                        </div>
                    @endforeach
                </div>

                {{-- Payment method --}}
                <div style="display:flex;justify-content:space-between;align-items:center;
                            padding-top:.75rem;border-top:1px solid var(--border)">
                    <span style="font-size:.8rem;color:var(--text-muted)">{{ $order->paymentMethodLabel() }}</span>
                    <div style="padding:.25rem .65rem;border-radius:99px;font-size:.72rem;font-weight:700;
                                background:#fef3c7;color:#92400e">
                        Pending confirmation
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Actions ── --}}
        <div style="display:flex;gap:.75rem;justify-content:center;flex-wrap:wrap">
            <a href="{{ route('shop.index') }}" class="btn btn-primary">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M2 3h2l2 12h13l2-8H6M9 21a1 1 0 1 0 0-2 1 1 0 0 0 0 2Zm8 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z"/></svg>
                Continue Shopping
            </a>
            <a href="{{ route('home') }}" class="btn btn-ghost">Back to Home</a>
        </div>
    </div>
</section>

@endsection
