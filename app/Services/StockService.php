<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;

class StockService
{
    public function deductForOrder(Order $order, array $lines): void
    {
        foreach ($lines as $line) {
            /** @var Product $product */
            $product = Product::query()->lockForUpdate()->findOrFail($line['product_id']);

            $qty = (int) $line['quantity'];

            if ($product->stock_quantity < $qty) {
                throw new \RuntimeException("Insufficient stock for {$product->name}.");
            }

            $before = $product->stock_quantity;
            $product->stock_quantity = $before - $qty;
            $product->save();

            StockMovement::create([
                'product_id' => $product->id,
                'shop_order_id' => $order->id,
                'quantity_change' => -$qty,
                'quantity_before' => $before,
                'quantity_after' => $product->stock_quantity,
                'reason' => StockMovement::REASON_ORDER_PLACED,
                'notes' => "Order {$order->order_number}",
            ]);
        }
    }

    public function restoreForCancelledOrder(Order $order): void
    {
        DB::transaction(function () use ($order) {
            $order->load('items');

            foreach ($order->items as $item) {
                if (! $item->product_id) {
                    continue;
                }

                $product = Product::query()->lockForUpdate()->find($item->product_id);

                if (! $product) {
                    continue;
                }

                $before = $product->stock_quantity;
                $product->stock_quantity = $before + $item->quantity;
                $product->save();

                StockMovement::create([
                    'product_id' => $product->id,
                    'shop_order_id' => $order->id,
                    'quantity_change' => $item->quantity,
                    'quantity_before' => $before,
                    'quantity_after' => $product->stock_quantity,
                    'reason' => StockMovement::REASON_ORDER_CANCELLED,
                    'notes' => "Order {$order->order_number} cancelled",
                ]);
            }
        });
    }
}
