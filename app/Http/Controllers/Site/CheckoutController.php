<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Http\Requests\Site\CheckoutRequest;
use App\Services\CartService;
use App\Services\CheckoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function __construct(
        private CartService $cart,
        private CheckoutService $checkout,
    ) {}

    public function show(): View|RedirectResponse
    {
        if ($this->cart->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
        }

        $errors = $this->cart->validateForCheckout();

        return view('pages.checkout.index', [
            'items' => $this->cart->detailedItems(),
            'subtotal' => $this->cart->subtotal(),
            'idempotencyKey' => $this->cart->idempotencyKey(),
            'cartErrors' => $errors,
        ]);
    }

    public function store(CheckoutRequest $request): RedirectResponse
    {
        if ($this->cart->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
        }

        try {
            $order = $this->checkout->placeOrder(
                $request->only(['name', 'phone', 'email', 'address', 'notes', 'payment_method']),
                $request->input('idempotency_key'),
            );
        } catch (\InvalidArgumentException $e) {
            return redirect()
                ->route('cart.index')
                ->with('error', $e->getMessage());
        } catch (\RuntimeException $e) {
            return redirect()
                ->route('checkout.show')
                ->with('error', $e->getMessage());
        }

        $this->cart->regenerateIdempotencyKey();

        return redirect()
            ->signedRoute('checkout.success', ['order' => $order])
            ->with('success', 'Your order has been placed successfully.');
    }

    public function success(\App\Models\Order $order): View
    {
        $order->load(['customer', 'items']);

        return view('pages.checkout.success', compact('order'));
    }
}
