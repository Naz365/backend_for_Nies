<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\CheckoutService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    protected CheckoutService $checkoutService;

    public function __construct(CheckoutService $checkoutService)
    {
        $this->checkoutService = $checkoutService;
    }

    /**
     * POST /api/v1/orders
     * Places a server-authoritative order via CheckoutService
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:50',
            'customer_email' => 'nullable|email|max:255',
            'shipping_address' => 'required|string',
            'payment_method' => 'nullable|string|in:cod,sslcommerz,bkash,nagad',
            'notes' => 'nullable|string',
            'items' => 'nullable|array',
            'items.*.product_id' => 'required_with:items|exists:products,id',
            'items.*.quantity' => 'required_with:items|integer|min:1',
        ]);

        try {
            $sessionToken = $request->header('X-Cart-Session') ?? $request->input('session_token');
            $order = $this->checkoutService->placeOrder($validated, $sessionToken);

            $order->load('items');

            return response()->json([
                'success' => true,
                'message' => 'Order placed successfully',
                'data' => [
                    'order_number' => $order->order_number,
                    'total_amount' => (float) $order->total_amount,
                    'currency' => 'BDT',
                    'payment_method' => $order->payment_method,
                    'payment_status' => $order->payment_status,
                    'status' => $order->status,
                    'items_count' => $order->items->count(),
                    'created_at' => $order->created_at->toIso8601String(),
                ],
            ], 201);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * GET /api/v1/orders/{order_number}
     * Public order tracking endpoint with phone number verification (Section 22 Security Rule)
     */
    public function show(string $orderNumber, Request $request): JsonResponse
    {
        $request->validate([
            'phone' => 'nullable|string',
        ]);

        try {
            $order = Order::where('order_number', $orderNumber)
                ->with(['items:id,order_id,product_title_snapshot,sku_snapshot,unit_price_snapshot,quantity,line_total'])
                ->firstOrFail();

            $isVerified = false;
            if ($request->filled('phone')) {
                $cleanInput = preg_replace('/[^0-9]/', '', $request->input('phone'));
                $cleanDb = preg_replace('/[^0-9]/', '', $order->customer_phone);

                if (str_ends_with($cleanDb, substr($cleanInput, -8))) {
                    $isVerified = true;
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'Phone number does not match order record.',
                    ], 403);
                }
            }

            if ($isVerified) {
                // Full details for verified customer
                return response()->json([
                    'success' => true,
                    'is_verified' => true,
                    'data' => [
                        'order_number' => $order->order_number,
                        'customer_name' => $order->customer_name,
                        'customer_phone' => $order->customer_phone,
                        'shipping_address' => $order->shipping_address,
                        'subtotal' => (float) $order->subtotal,
                        'shipping_fee' => (float) $order->shipping_fee,
                        'total_amount' => (float) $order->total_amount,
                        'payment_method' => $order->payment_method,
                        'payment_status' => $order->payment_status,
                        'status' => $order->status,
                        'created_at' => $order->created_at->toIso8601String(),
                        'items' => $order->items,
                    ],
                ]);
            }

            // Masked summary for unverified public queries
            $maskedName = mb_substr($order->customer_name, 0, 1) . '*** ' . mb_substr(strrchr($order->customer_name, ' ') ?: $order->customer_name, 0, 2) . '***';
            return response()->json([
                'success' => true,
                'is_verified' => false,
                'data' => [
                    'order_number' => $order->order_number,
                    'customer_name' => trim($maskedName),
                    'status' => $order->status,
                    'payment_method' => $order->payment_method,
                    'payment_status' => $order->payment_status,
                    'items_count' => $order->items->count(),
                    'created_at' => $order->created_at->toIso8601String(),
                    'message' => 'Provide order phone number to view full recipient and address details.',
                ],
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Order record not found',
            ], 404);
        }
    }
}
