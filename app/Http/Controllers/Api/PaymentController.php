<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    use ApiResponse;

    public function __construct()
    {
        $this->middleware('auth:api');
        $this->middleware('permission:create payments', ['only' => ['store']]);
        $this->middleware('permission:view payments', ['only' => ['show', 'status']]);
        $this->middleware('permission:confirm payments', ['only' => ['confirm']]);
    }

    /**
     * Create payment for order
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
        ]);

        $order = Order::find($request->order_id);

        if ($order->user_id !== auth()->id()) {
            return $this->errorResponse('Unauthorized', 403);
        }

        if ($order->payment_status === 'paid') {
            return $this->errorResponse('Order already paid', 400);
        }

        $payment = Payment::create([
            'order_id' => $order->id,
            'method' => $order->payment_method,
            'amount' => $order->total,
            'expires_at' => now()->addHours(24), // 24 hour expiry
        ]);

        // Generate payment details based on method
        switch ($order->payment_method) {
            case 'qr':
                $payment->update([
                    'qr_code' => $this->generateQRCode($payment),
                ]);
                break;
                
            case 'bank_transfer':
                $payment->update([
                    'bank_details' => [
                        'bank_name' => 'Bank Central Asia',
                        'account_number' => '1234567890',
                        'account_name' => 'PT Toko Online',
                        'amount' => $order->total,
                        'reference' => $payment->payment_reference,
                    ],
                ]);
                break;
                
            case 'cash':
                // Cash payment will be confirmed manually
                break;
        }

        return $this->createdResponse($payment, 'Payment created successfully');
    }

    /**
     * Get payment details
     */
    public function show(Payment $payment): JsonResponse
    {
        if ($payment->order->user_id !== auth()->id()) {
            return $this->errorResponse('Unauthorized', 403);
        }

        $payment->load('order');

        return $this->successResponse($payment, 'Payment retrieved successfully');
    }

    /**
     * Confirm payment (for cash/manual confirmation)
     */
    public function confirm(Request $request, Payment $payment): JsonResponse
    {
        if ($payment->order->user_id !== auth()->id()) {
            return $this->errorResponse('Unauthorized', 403);
        }

        if ($payment->status === 'paid') {
            return $this->errorResponse('Payment already confirmed', 400);
        }

        $request->validate([
            'notes' => 'nullable|string',
        ]);

        $payment->update([
            'status' => 'paid',
            'paid_at' => now(),
            'notes' => $request->notes,
        ]);

        // Update order payment status
        $payment->order->update([
            'payment_status' => 'paid',
            'status' => 'confirmed',
        ]);

        return $this->successResponse($payment, 'Payment confirmed successfully');
    }

    /**
     * Check payment status
     */
    public function status(Payment $payment): JsonResponse
    {
        if ($payment->order->user_id !== auth()->id()) {
            return $this->errorResponse('Unauthorized', 403);
        }

        // Check if payment expired
        if ($payment->expires_at && $payment->expires_at->isPast() && $payment->status === 'pending') {
            $payment->update(['status' => 'expired']);
        }

        return $this->successResponse([
            'payment_reference' => $payment->payment_reference,
            'status' => $payment->status,
            'method' => $payment->method,
            'amount' => $payment->amount,
            'expires_at' => $payment->expires_at,
            'paid_at' => $payment->paid_at,
        ], 'Payment status retrieved');
    }

    /**
     * Generate QR Code for payment
     */
    private function generateQRCode(Payment $payment): string
    {
        // In real implementation, integrate with payment gateway like QRIS
        return "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8/5+hHgAHggJ/PchI7wAAAABJRU5ErkJggg==";
    }
}