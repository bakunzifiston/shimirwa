@extends('layouts.site')

@section('title', 'Checkout')

@section('content')

{{-- ── Progress steps ── --}}
<div style="background:var(--white);border-bottom:1px solid var(--border);padding:.85rem 0">
    <div class="sc">
        <ol style="display:flex;align-items:center;gap:0;list-style:none;margin:0;padding:0">
            @foreach([['Cart','cart.index'],['Checkout','checkout.show'],['Confirmation',null]] as $i => [$label,$rt])
                <li style="display:flex;align-items:center;{{ $i > 0 ? 'flex:1' : '' }}">
                    @if($i > 0)
                        <div style="flex:1;height:2px;margin:0 .6rem;
                                    background:{{ $i <= 1 ? 'var(--blue)' : 'var(--border)' }}"></div>
                    @endif
                    <span style="display:inline-flex;align-items:center;gap:.5rem;font-size:.82rem;font-weight:600;
                                 color:{{ $i===1 ? 'var(--blue)' : ($i===0 ? 'var(--slate-400)' : 'var(--slate-400)') }}">
                        <span style="width:1.6rem;height:1.6rem;display:flex;align-items:center;justify-content:center;
                                     border-radius:50%;font-size:.72rem;font-weight:800;
                                     background:{{ $i===0 ? '#22c55e' : ($i===1 ? 'var(--blue)' : 'var(--border)') }};
                                     color:{{ $i<=1 ? 'white' : 'var(--slate-500)' }}">
                            @if($i===0)
                                <svg width="9" height="9" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" d="m5 13 4 4L19 7"/></svg>
                            @else
                                {{ $i+1 }}
                            @endif
                        </span>
                        <span class="step-label">{{ $label }}</span>
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
        @if(!empty($cartErrors))
            <div class="alert alert-error" style="margin-bottom:1.5rem">
                <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/></svg>
                <ul style="margin:0;padding:0 0 0 .25rem;list-style:none">
                    @foreach($cartErrors as $e)<li>{{ $e }}</li>@endforeach
                </ul>
            </div>
        @endif

        {{-- ── Two-column layout ── --}}
        <div class="checkout-layout">

            {{-- ── Form panel ── --}}
            <div style="background:var(--white);border:1px solid var(--border);
                        border-radius:var(--radius-xl);box-shadow:var(--shadow-sm);overflow:hidden">

                {{-- Panel header --}}
                <div style="padding:1.1rem 1.5rem;border-bottom:1px solid var(--border);
                            display:flex;align-items:center;gap:.65rem">
                    <div style="width:2.25rem;height:2.25rem;border-radius:var(--radius-sm);
                                background:var(--blue-light);display:flex;align-items:center;justify-content:center">
                        <svg width="15" height="15" fill="none" stroke="var(--blue)" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/>
                        </svg>
                    </div>
                    <div>
                        <div style="font-size:.95rem;font-weight:800;color:var(--slate-900)">Your details</div>
                        <div style="font-size:.75rem;color:var(--text-muted)">We'll use this to confirm and deliver your order</div>
                    </div>
                </div>

                {{-- Form body --}}
                <form method="POST" action="{{ route('checkout.store') }}"
                      style="padding:1.5rem;display:flex;flex-direction:column;gap:1.25rem"
                      id="checkout-form">
                    @csrf
                    <input type="hidden" name="idempotency_key" value="{{ $idempotencyKey }}">
                    <input type="hidden" name="payment_method" value="{{ \App\Models\Order::PAYMENT_METHOD_COD }}">

                    {{-- Full name --}}
                    <div>
                        <label style="display:block;font-size:.8rem;font-weight:700;color:var(--slate-700);
                                      margin-bottom:.5rem;letter-spacing:.01em">
                            Full name <span style="color:#dc2626">*</span>
                        </label>
                        <input type="text" name="name" value="{{ old('name') }}" required
                               placeholder="e.g. Jean Pierre Habimana"
                               style="width:100%;padding:.7rem 1rem;border:1.5px solid {{ $errors->has('name') ? '#fca5a5' : 'var(--border)' }};
                                      border-radius:var(--radius);font-size:.9rem;font-family:var(--font);
                                      color:var(--text);background:{{ $errors->has('name') ? '#fef2f2' : 'var(--white)' }};
                                      transition:border-color .15s,box-shadow .15s;outline:none;box-sizing:border-box"
                               onfocus="this.style.borderColor='var(--blue)';this.style.boxShadow='0 0 0 3px rgba(16,73,140,.1)'"
                               onblur="this.style.borderColor='{{ $errors->has('name') ? '#fca5a5' : 'var(--border)' }}';this.style.boxShadow='none'">
                        @error('name')
                            <div style="font-size:.75rem;color:#dc2626;margin-top:.35rem;display:flex;align-items:center;gap:.3rem">
                                <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/></svg>
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    {{-- Phone --}}
                    <div>
                        <label style="display:block;font-size:.8rem;font-weight:700;color:var(--slate-700);
                                      margin-bottom:.5rem;letter-spacing:.01em">
                            Phone <span style="color:#dc2626">*</span>
                        </label>
                        <div style="position:relative">
                            <span style="position:absolute;left:.85rem;top:50%;transform:translateY(-50%);
                                         font-size:.82rem;color:var(--text-muted);font-weight:600;pointer-events:none">+250</span>
                            <input type="tel" name="phone" value="{{ old('phone') }}" required
                                   placeholder="788 000 000"
                                   style="width:100%;padding:.7rem 1rem .7rem 3.25rem;
                                          border:1.5px solid {{ $errors->has('phone') ? '#fca5a5' : 'var(--border)' }};
                                          border-radius:var(--radius);font-size:.9rem;font-family:var(--font);
                                          color:var(--text);background:{{ $errors->has('phone') ? '#fef2f2' : 'var(--white)' }};
                                          transition:border-color .15s,box-shadow .15s;outline:none;box-sizing:border-box"
                                   onfocus="this.style.borderColor='var(--blue)';this.style.boxShadow='0 0 0 3px rgba(16,73,140,.1)'"
                                   onblur="this.style.borderColor='{{ $errors->has('phone') ? '#fca5a5' : 'var(--border)' }}';this.style.boxShadow='none'">
                        </div>
                        @error('phone')
                            <div style="font-size:.75rem;color:#dc2626;margin-top:.35rem;display:flex;align-items:center;gap:.3rem">
                                <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/></svg>
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    {{-- Email --}}
                    <div>
                        <label style="display:block;font-size:.8rem;font-weight:700;color:var(--slate-700);
                                      margin-bottom:.5rem;letter-spacing:.01em">
                            Email
                            <span style="margin-left:.35rem;font-size:.72rem;font-weight:500;color:var(--text-muted)">(optional)</span>
                        </label>
                        <input type="email" name="email" value="{{ old('email') }}"
                               placeholder="you@example.com"
                               style="width:100%;padding:.7rem 1rem;border:1.5px solid {{ $errors->has('email') ? '#fca5a5' : 'var(--border)' }};
                                      border-radius:var(--radius);font-size:.9rem;font-family:var(--font);
                                      color:var(--text);background:{{ $errors->has('email') ? '#fef2f2' : 'var(--white)' }};
                                      transition:border-color .15s,box-shadow .15s;outline:none;box-sizing:border-box"
                               onfocus="this.style.borderColor='var(--blue)';this.style.boxShadow='0 0 0 3px rgba(16,73,140,.1)'"
                               onblur="this.style.borderColor='{{ $errors->has('email') ? '#fca5a5' : 'var(--border)' }}';this.style.boxShadow='none'">
                        @error('email')
                            <div style="font-size:.75rem;color:#dc2626;margin-top:.35rem;display:flex;align-items:center;gap:.3rem">
                                <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/></svg>
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    {{-- Delivery address --}}
                    <div>
                        <label style="display:block;font-size:.8rem;font-weight:700;color:var(--slate-700);
                                      margin-bottom:.5rem;letter-spacing:.01em">
                            Delivery address <span style="color:#dc2626">*</span>
                        </label>
                        <textarea name="address" rows="3" required
                                  placeholder="District, sector, street or landmarks…"
                                  style="width:100%;padding:.7rem 1rem;border:1.5px solid {{ $errors->has('address') ? '#fca5a5' : 'var(--border)' }};
                                         border-radius:var(--radius);font-size:.9rem;font-family:var(--font);
                                         color:var(--text);background:{{ $errors->has('address') ? '#fef2f2' : 'var(--white)' }};
                                         transition:border-color .15s,box-shadow .15s;outline:none;
                                         resize:vertical;box-sizing:border-box;min-height:5.5rem"
                                  onfocus="this.style.borderColor='var(--blue)';this.style.boxShadow='0 0 0 3px rgba(16,73,140,.1)'"
                                  onblur="this.style.borderColor='{{ $errors->has('address') ? '#fca5a5' : 'var(--border)' }}';this.style.boxShadow='none'">{{ old('address') }}</textarea>
                        @error('address')
                            <div style="font-size:.75rem;color:#dc2626;margin-top:.35rem;display:flex;align-items:center;gap:.3rem">
                                <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/></svg>
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    {{-- Order notes --}}
                    <div>
                        <label style="display:block;font-size:.8rem;font-weight:700;color:var(--slate-700);
                                      margin-bottom:.5rem;letter-spacing:.01em">
                            Order notes
                            <span style="margin-left:.35rem;font-size:.72rem;font-weight:500;color:var(--text-muted)">(optional)</span>
                        </label>
                        <textarea name="notes" rows="2"
                                  placeholder="Preferred delivery time, special instructions…"
                                  style="width:100%;padding:.7rem 1rem;border:1.5px solid var(--border);
                                         border-radius:var(--radius);font-size:.9rem;font-family:var(--font);
                                         color:var(--text);background:var(--white);
                                         transition:border-color .15s,box-shadow .15s;outline:none;
                                         resize:vertical;box-sizing:border-box"
                                  onfocus="this.style.borderColor='var(--blue)';this.style.boxShadow='0 0 0 3px rgba(16,73,140,.1)'"
                                  onblur="this.style.borderColor='var(--border)';this.style.boxShadow='none'">{{ old('notes') }}</textarea>
                    </div>

                    {{-- Submit --}}
                    {{-- What happens next --}}
                    <div style="padding:.85rem 1rem;background:var(--blue-light);border-radius:var(--radius);
                                border:1px solid rgba(16,73,140,.15);font-size:.8rem;color:var(--blue-dark);
                                display:flex;align-items:flex-start;gap:.6rem;line-height:1.55">
                        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0;margin-top:.1rem" aria-hidden="true"><path stroke-linecap="round" d="M11.25 11.25l.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z"/></svg>
                        <span>
                            <strong>What happens next?</strong><br>
                            Your order request is saved and our team will <strong>call you</strong> at the phone number above to confirm the delivery details and arrange payment before dispatch.
                        </span>
                    </div>

                    <button type="submit" id="checkout-submit"
                            class="btn btn-primary btn-lg"
                            style="justify-content:center;width:100%;margin-top:.25rem">
                        <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" d="m5 13 4 4L19 7"/></svg>
                        Submit Order Request
                    </button>
                </form>
            </div>

            {{-- ── Order summary ── --}}
            <aside style="position:sticky;top:calc(var(--header-h) + 1.5rem);align-self:start">
                <div style="background:var(--white);border:1px solid var(--border);
                            border-radius:var(--radius-xl);box-shadow:var(--shadow);overflow:hidden">

                    {{-- Header --}}
                    <div style="padding:1.1rem 1.5rem;background:linear-gradient(135deg,var(--blue),var(--blue-dark));color:white">
                        <div style="font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;opacity:.75;margin-bottom:.2rem">Review</div>
                        <div style="font-size:1rem;font-weight:800">Order Summary</div>
                    </div>

                    <div style="padding:1.1rem 1.5rem">
                        {{-- Items --}}
                        <div style="display:flex;flex-direction:column;border-bottom:1px solid var(--border);margin-bottom:.85rem;padding-bottom:.85rem">
                            @foreach($items as $row)
                                <div style="display:flex;align-items:center;justify-content:space-between;
                                            padding:.5rem 0;gap:1rem">
                                    <span style="font-size:.84rem;color:var(--slate-600);flex:1;min-width:0;
                                                 white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                                        {{ $row['item']['name'] }}
                                        <span style="color:var(--text-muted)">× {{ $row['item']['quantity'] }}</span>
                                    </span>
                                    <strong style="font-size:.84rem;font-weight:700;color:var(--slate-900);flex-shrink:0">
                                        {{ number_format($row['line_total']) }} RWF
                                    </strong>
                                </div>
                            @endforeach
                        </div>

                        {{-- Total --}}
                        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.25rem">
                            <span style="font-size:.95rem;font-weight:700;color:var(--slate-800)">Total</span>
                            <strong style="font-size:1.2rem;font-weight:900;color:var(--blue)">
                                {{ number_format($subtotal) }} RWF
                            </strong>
                        </div>

                        {{-- Info note --}}
                        <div style="background:var(--blue-light);border-radius:var(--radius-sm);padding:.75rem .9rem;
                                    display:flex;align-items:flex-start;gap:.5rem;font-size:.78rem;
                                    color:var(--blue-dark);line-height:1.5">
                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0;margin-top:.1rem" aria-hidden="true"><path stroke-linecap="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z"/></svg>
                            We'll call you to confirm before dispatch — no payment needed online.
                        </div>

                        {{-- Edit cart link --}}
                        <a href="{{ route('cart.index') }}"
                           style="display:flex;align-items:center;justify-content:center;gap:.35rem;
                                  margin-top:.85rem;padding:.6rem;font-size:.8rem;font-weight:600;
                                  color:var(--blue);text-decoration:none;border-radius:var(--radius-sm);
                                  transition:background .15s"
                           onmouseover="this.style.background='var(--blue-light)'"
                           onmouseout="this.style.background='transparent'">
                            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
                            Edit cart
                        </a>
                    </div>
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
        btn.style.opacity = '.6';
        btn.style.cursor = 'not-allowed';
        btn.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="animation:spin .8s linear infinite"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg> Submitting request…';
    }
});
</script>
<style>
@keyframes spin { to { transform: rotate(360deg); } }
</style>
@endpush
