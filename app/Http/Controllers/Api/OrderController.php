<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    /**
     * POST /api/v1/orders
     * Places a server-authoritative order with transaction safety and stock decrement
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
            $order = DB::transaction(function () use ($request, $validated) {
                // 1. Resolve Cart or Direct Items
                $sessionToken = $request->header('X-Cart-Session') ?? $request->input('session_token');
                $cart = $sessionToken ? Cart::where('session_token', $sessionToken)->where('status', 'active')->first() : null;

                $orderItemsData = [];
                $subtotal = 0;

                if (!empty($validated['items'])) {
                    // Direct item payload checkout
                    foreach ($validated['items'] as $itemInput) {
                        $product = Product::lockForUpdate()->findOrFail($itemInput['product_id']);
                        $qty = (int) $itemInput['quantity'];

                        if ($product->track_inventory && $product->stock_quantity < $qty) {
                            throw new \Exception("Insufficient stock for '{$product->title}'. Available: {$product->stock_quantity}");
                        }

                        $unitPrice = (float) $product->price;
                        $lineTotal = round($unitPrice * $qty, 2);
                        $subtotal += $lineTotal;

                        // Decrement stock
                        if ($product->track_inventory) {
                            $product->decrement('stock_quantity', $qty);
                        }

                        $orderItemsData[] = [
                            'product_id' => $product->id,
                            'product_title_snapshot' => $product->title,
                            'sku_snapshot' => $product->sku,
                            'unit_price_snapshot' => $unitPrice,
                            'quantity' => $qty,
                            'line_total' => $lineTotal,
                        ];
                    }
                } elseif ($cart && $cart->items()->count() > 0) {
                    // Checkout from existing session cart
                    foreach ($cart->items as $cartItem) {
                        $product = Product::lockForUpdate()->find($cartItem->product_id);
                        if (!$product || $product->status !== 'published') {
                            continue;
                        }

                        $qty = (int) $cartItem->quantity;
                        if ($product->track_inventory && $product->stock_quantity < $qty) {
                            throw new \Exception("Insufficient stock for '{$product->title}'. Available: {$product->stock_quantity}");
                        }

                        $unitPrice = (float) $product->price;
                        $lineTotal = round($unitPrice * $qty, 2);
                        $subtotal += $lineTotal;

                        // Decrement stock
                        if ($product->track_inventory) {
                            $product->decrement('stock_quantity', $qty);
                        }

                        $orderItemsData[] = [
                            'product_id' => $product->id,
                            'product_title_snapshot' => $product->title,
                            'sku_snapshot' => $product->sku,
                            'unit_price_snapshot' => $unitPrice,
                            'quantity' => $qty,
                            'line_total' => $lineTotal,
                        ];
                    }
                } else {
                    throw new \Exception("Cannot place order with an empty cart.");
                }

                if (empty($orderItemsData)) {
                    throw new \Exception("No valid items found in order.");
                }

                // 2. Find or Create Customer
                $customer = Customer::firstOrCreate(
                    ['phone' => $validated['customer_phone']],
                    [
                        'name' => $validated['customer_name'],
                        'email' => $validated['customer_email'] ?? null,
                    ]
                );

                // 3. Create Order Record
                $paymentMethod = $validated['payment_method'] ?? 'cod';
                $shippingFee = 0.00;
                $discountAmount = 0.00;
                $totalAmount = round($subtotal + $shippingFee - $discountAmount, 2);

                $order = Order::create([
                    'order_number' => Order::generateOrderNumber(),
                    'customer_id' => $customer->id,
                    'customer_name' => $validated['customer_name'],
                    'customer_email' => $validated['customer_email'] ?? null,
                    'customer_phone' => $validated['customer_phone'],
                    'shipping_address' => $validated['shipping_address'],
                    'subtotal' => $subtotal,
                    'shipping_fee' => $shippingFee,
                    'discount_amount' => $discountAmount,
                    'total_amount' => $totalAmount,
                    'payment_method' => $paymentMethod,
                    'payment_status' => $paymentMethod === 'cod' ? 'unpaid' : 'pending',
                    'status' => 'pending',
                    'notes' => $validated['notes'] ?? null,
                ]);

                // 4. Create Frozen Order Items Snapshots
                foreach ($orderItemsData as $item) {
                    $order->items()->create($item);
                }

                // 5. Create Initial Payment Record
                Payment::create([
                    'order_id' => $order->id,
                    'payment_method' => $paymentMethod,
                    'gateway' => $paymentMethod === 'cod' ? 'manual_cod' : $paymentMethod,
                    'amount' => $totalAmount,
                    'currency' => 'BDT',
                    'status' => $paymentMethod === 'cod' ? 'pending' : 'pending',
                ]);

                // 6. Close Cart
                if ($cart) {
                    $cart->update(['status' => 'converted']);
                    $cart->items()->delete();
                }

                return $order;
            });

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
     * Public order tracking endpoint
     */
    public function show(string $orderNumber, Request $request): JsonResponse
    {
        try {
            $order = Order::where('order_number', $orderNumber)
                ->with(['items:id,order_id,product_title_snapshot,sku_snapshot,unit_price_snapshot,quantity,line_total'])
                ->firstOrFail();

            // Optional security phone verification if provided
            if ($request->filled('phone')) {
                if (trim($order->customer_phone) !== trim($request->input('phone'))) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Phone number does not match order record',
                    ], 403);
                }
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'order_number' => $order->order_number,
                    'customer_name' => $order->customer_name,
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
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found',
            ], 404);
        }
    }
}
