<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Product;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    use ApiResponse;

    public function __construct()
    {
        $this->middleware('auth:api');
        $this->middleware('permission:manage cart', ['except' => []]);
    }

    /**
     * Get user cart items
     */
    public function index(): JsonResponse
    {
        $cartItems = auth()->user()->cart()->with('product.category')->get();
        
        $total = $cartItems->sum(function ($item) {
            return $item->product->price * $item->quantity;
        });

        return $this->successResponse([
            'items' => $cartItems,
            'total_items' => $cartItems->sum('quantity'),
            'subtotal' => $total,
            'delivery_fee' => 10000, // Default delivery fee
            'total' => $total + 10000,
        ], 'Cart retrieved successfully');
    }

    /**
     * Add item to cart
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $product = Product::find($request->product_id);
        
        if ($product->stock < $request->quantity) {
            return $this->errorResponse('Insufficient stock', 400);
        }

        $cartItem = Cart::where('user_id', auth()->id())
            ->where('product_id', $request->product_id)
            ->first();

        if ($cartItem) {
            // Update existing cart item
            $newQuantity = $cartItem->quantity + $request->quantity;
            
            if ($newQuantity > $product->stock) {
                $cartItem->update(['quantity' => $product->stock]);
                return $this->errorResponse('Quantity adjusted to available stock: ' . $product->stock, 400);
            }
            
            $cartItem->update(['quantity' => $newQuantity]);
        } else {
            // Create new cart item
            $cartItem = Cart::create([
                'user_id' => auth()->id(),
                'product_id' => $request->product_id,
                'quantity' => $request->quantity,
            ]);
        }

        $cartItem->load('product');

        return $this->successResponse($cartItem, 'Item added to cart');
    }

    /**
     * Update cart item quantity
     */
    public function update(Request $request, Cart $cart): JsonResponse
    {
        if ($cart->user_id !== auth()->id()) {
            return $this->errorResponse('Unauthorized', 403);
        }

        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        if ($cart->product->stock < $request->quantity) {
            return $this->errorResponse('Insufficient stock', 400);
        }

        $cart->update(['quantity' => $request->quantity]);
        $cart->load('product');

        return $this->successResponse($cart, 'Cart updated');
    }

    /**
     * Remove item from cart
     */
    public function destroy(Cart $cart): JsonResponse
    {
        if ($cart->user_id !== auth()->id()) {
            return $this->errorResponse('Unauthorized', 403);
        }

        $cart->delete();

        return $this->successResponse(null, 'Item removed from cart');
    }

    /**
     * Clear entire cart
     */
    public function clear(): JsonResponse
    {
        auth()->user()->cart()->delete();

        return $this->successResponse(null, 'Cart cleared');
    }

    /**
     * Checkout cart to create order
     */
    public function checkout(Request $request): JsonResponse
    {
        $request->validate([
            'delivery_type' => 'required|in:pickup,delivery',
            'delivery_address' => 'required_if:delivery_type,delivery|string',
            'payment_method' => 'required|in:cash,qr,bank_transfer',
            'notes' => 'nullable|string',
        ]);

        $cartItems = auth()->user()->cart()->with('product')->get();

        if ($cartItems->isEmpty()) {
            return $this->errorResponse('Cart is empty', 400);
        }

        // Check stock availability
        foreach ($cartItems as $item) {
            if ($item->product->stock < $item->quantity) {
                return $this->errorResponse('Insufficient stock for ' . $item->product->name, 400);
            }
        }

        $subtotal = $cartItems->sum(function ($item) {
            return $item->product->price * $item->quantity;
        });

        $deliveryFee = $request->delivery_type === 'delivery' ? 10000 : 0;
        $total = $subtotal + $deliveryFee;

        // Create order
        $order = auth()->user()->orders()->create([
            'subtotal' => $subtotal,
            'delivery_fee' => $deliveryFee,
            'total' => $total,
            'delivery_type' => $request->delivery_type,
            'delivery_address' => $request->delivery_address,
            'payment_method' => $request->payment_method,
            'notes' => $request->notes,
        ]);

        // Create order items
        foreach ($cartItems as $item) {
            $order->items()->create([
                'product_id' => $item->product_id,
                'quantity' => $item->quantity,
                'price' => $item->product->price,
                'total' => $item->product->price * $item->quantity,
            ]);

            // Update stock
            $item->product->decrement('stock', $item->quantity);
        }

        // Clear cart
        auth()->user()->cart()->delete();

        $order->load(['items.product']);

        return $this->createdResponse($order, 'Order created successfully');
    }
}