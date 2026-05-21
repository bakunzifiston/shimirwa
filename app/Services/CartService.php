<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Session;

class CartService
{
    private const SESSION_KEY = 'shop_cart';

    public function items(): array
    {
        return Session::get(self::SESSION_KEY.'.items', []);
    }

    public function idempotencyKey(): string
    {
        $key = Session::get(self::SESSION_KEY.'.idempotency_key');

        if (! $key) {
            $key = (string) str()->uuid();
            Session::put(self::SESSION_KEY.'.idempotency_key', $key);
        }

        return $key;
    }

    public function regenerateIdempotencyKey(): string
    {
        $key = (string) str()->uuid();
        Session::put(self::SESSION_KEY.'.idempotency_key', $key);

        return $key;
    }

    public function count(): int
    {
        return (int) collect($this->items())->sum('quantity');
    }

    public function subtotal(): float
    {
        return (float) collect($this->items())->sum(fn ($item) => $item['unit_price'] * $item['quantity']);
    }

    public function isEmpty(): bool
    {
        return empty($this->items());
    }

    public function add(Product $product, int $quantity = 1): void
    {
        $this->assertCanPurchase($product, $quantity, $this->quantityFor($product->id));

        $items = $this->items();
        $id = $product->id;

        if (isset($items[$id])) {
            $items[$id]['quantity'] += $quantity;
            $items[$id]['unit_price'] = $product->effectivePrice();
            $items[$id]['max_stock'] = $product->stock_quantity;
        } else {
            $items[$id] = [
                'product_id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'unit_price' => $product->effectivePrice(),
                'quantity' => $quantity,
                'max_stock' => $product->stock_quantity,
            ];
        }

        Session::put(self::SESSION_KEY.'.items', $items);
    }

    public function update(int $productId, int $quantity): void
    {
        $product = Product::query()->findOrFail($productId);

        if ($quantity <= 0) {
            $this->remove($productId);

            return;
        }

        $this->assertCanPurchase($product, $quantity, 0);

        $items = $this->items();
        if (! isset($items[$productId])) {
            return;
        }

        $items[$productId]['quantity'] = $quantity;
        $items[$productId]['unit_price'] = $product->effectivePrice();
        $items[$productId]['max_stock'] = $product->stock_quantity;

        Session::put(self::SESSION_KEY.'.items', $items);
    }

    public function remove(int $productId): void
    {
        $items = $this->items();
        unset($items[$productId]);
        Session::put(self::SESSION_KEY.'.items', $items);
    }

    public function clear(): void
    {
        Session::forget(self::SESSION_KEY);
    }

    public function quantityFor(int $productId): int
    {
        return (int) ($this->items()[$productId]['quantity'] ?? 0);
    }

    /**
     * @return Collection<int, array{item: array, product: Product|null, line_total: float, stock_ok: bool}>
     */
    public function detailedItems(): Collection
    {
        $productIds = array_keys($this->items());

        $products = Product::query()->whereIn('id', $productIds)->get()->keyBy('id');

        return collect($this->items())->map(function (array $item, $productId) use ($products) {
            $product = $products->get($productId);
            $stock = $product?->stock_quantity ?? 0;
            $stockOk = $product
                && $product->isActive()
                && $stock >= $item['quantity'];

            return [
                'item' => $item,
                'product' => $product,
                'line_total' => $item['unit_price'] * $item['quantity'],
                'stock_ok' => $stockOk,
                'available_stock' => $stock,
            ];
        });
    }

    public function validateForCheckout(): array
    {
        $errors = [];

        if ($this->isEmpty()) {
            $errors[] = 'Your cart is empty.';

            return $errors;
        }

        foreach ($this->detailedItems() as $row) {
            $product = $row['product'];
            $item = $row['item'];

            if (! $product || ! $product->isActive()) {
                $errors[] = "{$item['name']} is no longer available.";

                continue;
            }

            if ($product->stock_quantity < $item['quantity']) {
                $errors[] = "{$item['name']}: only {$product->stock_quantity} in stock.";
            }
        }

        return $errors;
    }

    private function assertCanPurchase(Product $product, int $quantity, int $existingQty): void
    {
        if (! $product->isActive()) {
            throw new \InvalidArgumentException('This product is not available.');
        }

        if (! $product->isInStock()) {
            throw new \InvalidArgumentException('This product is out of stock.');
        }

        $newTotal = $existingQty + $quantity;

        if ($newTotal > $product->stock_quantity) {
            throw new \InvalidArgumentException("Only {$product->stock_quantity} available in stock.");
        }
    }
}
