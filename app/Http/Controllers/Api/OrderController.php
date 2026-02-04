<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    use ApiResponse;

    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * Get user orders
     * 
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $orders = auth()->user()->orders()->with(['items.product'])->latest()->get();
        
        return $this->successResponse($orders, 'Orders retrieved successfully');
    }

    /**
     * Create new order
     * 
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'delivery_type' => 'required|in:pickup,delivery',
            'delivery_address' => 'required_if:delivery_type,delivery|string',
            'payment_method' => 'required|in:cash,qr,bank_transfer',
            'notes' => 'nullable|string',
        ]);

        $subtotal = 0;
        $orderItems = [];

        foreach ($request->items as $item) {
            $product = Product::find($item['product_id']);
            
            if ($product->stock < $item['quantity']) {
                return $this->errorResponse('Insufficient stock for ' . $product->name, 400);
            }

            $itemTotal = $product->price * $item['quantity'];
            $subtotal += $itemTotal;

            $orderItems[] = [
                'product_id' => $product->id,
                'quantity' => $item['quantity'],
                'price' => $product->price,
                'total' => $itemTotal,
            ];
        }

        $deliveryFee = $request->delivery_type === 'delivery' ? 10000 : 0; // 10k delivery fee
        $total = $subtotal + $deliveryFee;

        $order = Order::create([
            'user_id' => auth()->id(),
            'subtotal' => $subtotal,
            'delivery_fee' => $deliveryFee,
            'total' => $total,
            'delivery_type' => $request->delivery_type,
            'delivery_address' => $request->delivery_address,
            'payment_method' => $request->payment_method,
            'notes' => $request->notes,
        ]);

        foreach ($orderItems as $item) {
            $order->items()->create($item);
        }

        // Update stock
        foreach ($request->items as $item) {
            $product = Product::find($item['product_id']);
            $product->decrement('stock', $item['quantity']);
        }

        $order->load(['items.product']);

        return $this->createdResponse($order, 'Order created successfully');
    }

    /**
     * Get order details
     * 
     * @return JsonResponse
     */
    public function show(Order $order): JsonResponse
    {
        if ($order->user_id !== auth()->id()) {
            return $this->errorResponse('Unauthorized', 403);
        }

        $order->load(['items.product']);
        
        return $this->successResponse($order, 'Order retrieved successfully');
    }

    public function update(Request $request, Order $order): JsonResponse
    {
        if ($order->user_id !== auth()->id()) {
            return $this->errorResponse('Unauthorized', 403);
        }

        if ($order->status !== 'pending') {
            return $this->errorResponse('Cannot update confirmed order', 400);
        }

        $request->validate([
            'delivery_address' => 'sometimes|string',
            'notes' => 'sometimes|nullable|string',
        ]);

        $order->update($request->only(['delivery_address', 'notes']));

        return $this->successResponse($order, 'Order updated successfully');
    }

    public function destroy(Order $order): JsonResponse
    {
        if ($order->user_id !== auth()->id()) {
            return $this->errorResponse('Unauthorized', 403);
        }

        if ($order->status !== 'pending') {
            return $this->errorResponse('Cannot cancel confirmed order', 400);
        }

        // Restore stock
        foreach ($order->items as $item) {
            $item->product->increment('stock', $item->quantity);
        }

        $order->update(['status' => 'cancelled']);

        return $this->successResponse(null, 'Order cancelled successfully');
    }
}