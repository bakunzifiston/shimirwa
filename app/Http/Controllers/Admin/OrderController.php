<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Order\UpdateOrderRequest;
use App\Models\Order;
use App\Services\StockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function __construct(private StockService $stock) {}

    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->toString();
        $orderStatus = $request->string('order_status')->toString();
        $paymentStatus = $request->string('payment_status')->toString();

        $orders = Order::query()
            ->with('customer')
            ->withCount('items')
            ->when($search, function ($q) use ($search) {
                $q->where(function ($q) use ($search) {
                    $q->where('order_number', 'like', "%{$search}%")
                        ->orWhereHas('customer', fn ($c) => $c
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%"));
                });
            })
            ->when($orderStatus && array_key_exists($orderStatus, Order::orderStatuses()), fn ($q) => $q->where('order_status', $orderStatus))
            ->when($paymentStatus && array_key_exists($paymentStatus, Order::paymentStatuses()), fn ($q) => $q->where('payment_status', $paymentStatus))
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        return view('admin.orders.index', compact('orders', 'search', 'orderStatus', 'paymentStatus'));
    }

    public function show(Order $order): View
    {
        $order->load(['customer', 'items.product']);

        return view('admin.orders.show', compact('order'));
    }

    public function update(UpdateOrderRequest $request, Order $order): RedirectResponse
    {
        $previousOrderStatus = $order->order_status;
        $previousPaymentStatus = $order->payment_status;

        $order->update($request->validated());

        $wasActive = $this->countsAsStockDeducted($previousOrderStatus, $previousPaymentStatus);
        $isActive = $order->isStockDeducted();

        if ($wasActive && ! $isActive) {
            $this->stock->restoreForCancelledOrder($order);
        }

        return redirect()
            ->route('admin.orders.show', $order)
            ->with('success', 'Order updated successfully.');
    }

    private function countsAsStockDeducted(string $orderStatus, string $paymentStatus): bool
    {
        if ($paymentStatus === Order::PAYMENT_CANCELLED || $orderStatus === Order::STATUS_CANCELLED) {
            return false;
        }

        return in_array($orderStatus, [
            Order::STATUS_NEW,
            Order::STATUS_PROCESSING,
            Order::STATUS_SHIPPED,
            Order::STATUS_COMPLETED,
        ], true);
    }
}
