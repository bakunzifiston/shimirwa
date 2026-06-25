<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;

class CheckoutService
{
    public function __construct(
        private CartService $cart,
        private StockService $stock,
    ) {}

    /**
     * @param  array{name: string, phone: string, address: string, email?: string|null, notes?: string|null, payment_method: string}  $customerData
     */
    public function placeOrder(array $customerData, string $idempotencyKey): Order
    {
        $existing = Order::query()->where('idempotency_key', $idempotencyKey)->first();

        if ($existing) {
            return $existing->load(['customer', 'items']);
        }

        $errors = $this->cart->validateForCheckout();

        if ($errors !== []) {
            throw new \InvalidArgumentException(implode(' ', $errors));
        }

        return DB::transaction(function () use ($customerData, $idempotencyKey) {
            $lines = [];
            $subtotal = 0.0;

            foreach ($this->cart->detailedItems() as $row) {
                $product = $row['product'];
                $item = $row['item'];

                if (! $product || $product->stock_quantity < $item['quantity']) {
                    throw new \RuntimeException("Insufficient stock for {$item['name']}.");
                }

                $lineTotal = round($item['unit_price'] * $item['quantity'], 2);
                $subtotal += $lineTotal;

                $lines[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'unit_price' => $item['unit_price'],
                    'quantity' => $item['quantity'],
                    'line_total' => $lineTotal,
                ];
            }

            $customer = Customer::create([
                'name' => $customerData['name'],
                'phone' => $customerData['phone'],
                'email' => $customerData['email'] ?? null,
                'address' => $customerData['address'],
            ]);

            $order = Order::create([
                'order_number' => $this->generateOrderNumber(),
                'customer_id' => $customer->id,
                'subtotal' => $subtotal,
                'total' => $subtotal,
                'payment_method' => $customerData['payment_method'] ?? Order::PAYMENT_METHOD_COD,
                'payment_status' => Order::PAYMENT_PENDING,
                'order_status' => Order::STATUS_NEW,
                'idempotency_key' => $idempotencyKey,
                'notes' => $customerData['notes'] ?? null,
            ]);

            foreach ($lines as $line) {
                OrderItem::create([
                    'shop_order_id' => $order->id,
                    ...$line,
                ]);
            }

            $this->stock->deductForOrder($order, array_map(fn ($l) => [
                'product_id' => $l['product_id'],
                'quantity' => $l['quantity'],
            ], $lines));

            $this->cart->clear();

            return $order->load(['customer', 'items']);
        });
    }

    private function generateOrderNumber(): string
    {
        do {
            $number = 'SW-'.now()->format('Ymd').'-'.strtoupper(str()->random(6));
        } while (Order::query()->where('order_number', $number)->exists());

        return $number;
    }
}
