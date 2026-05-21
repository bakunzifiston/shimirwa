<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Http\Requests\Site\AddToCartRequest;
use App\Http\Requests\Site\UpdateCartRequest;
use App\Models\Product;
use App\Services\CartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CartController extends Controller
{
    public function __construct(private CartService $cart) {}

    public function index(): View|RedirectResponse
    {
        $items = $this->cart->detailedItems();
        $hasStockIssues = $items->contains(fn ($row) => ! $row['stock_ok']);

        return view('pages.cart.index', [
            'items' => $items,
            'subtotal' => $this->cart->subtotal(),
            'hasStockIssues' => $hasStockIssues,
        ]);
    }

    public function add(AddToCartRequest $request, Product $product): RedirectResponse
    {
        abort_unless($product->isActive(), 404);

        try {
            $this->cart->add($product, (int) $request->input('quantity', 1));
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('cart.index')
            ->with('success', "{$product->name} added to your cart.");
    }

    public function update(UpdateCartRequest $request, Product $product): RedirectResponse
    {
        try {
            $this->cart->update($product->id, (int) $request->input('quantity'));
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('cart.index')
            ->with('success', 'Cart updated.');
    }

    public function remove(Product $product): RedirectResponse
    {
        $this->cart->remove($product->id);

        return redirect()
            ->route('cart.index')
            ->with('success', 'Item removed from cart.');
    }
}
