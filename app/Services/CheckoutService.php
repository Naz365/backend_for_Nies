<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CheckoutService
{
    /**
     * Executes atomic order creation with concurrency-safe stock locking and frozen snapshots
     *
     * @param array $payload Validated checkout payload
     * @param string|null $sessionToken Guest cart session identifier
     * @return Order
     * @throws \Exception
     */
    public function placeOrder(array $payload, ?string $sessionToken = null): Order
    {
        return DB::transaction(function () use ($payload, $sessionToken) {
            $cart = $sessionToken 
                ? Cart::where('session_token', $sessionToken)->where('status', 'active')->first() 
                : null;

            $orderItemsData = [];
            $subtotal = 0;

            if (!empty($payload['items'])) {
                // Direct purchase mode
                foreach ($payload['items'] as $itemInput) {
                    $product = Product::lockForUpdate()->findOrFail($itemInput['product_id']);
                    $qty = (int) $itemInput['quantity'];

                    if ($product->track_inventory && $product->stock_quantity < $qty) {
                        throw new \Exception("Insufficient inventory for '{$product->title}'. Only {$product->stock_quantity} units available.");
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
                // Session cart checkout mode
                foreach ($cart->items as $cartItem) {
                    $product = Product::lockForUpdate()->find($cartItem->product_id);
                    if (!$product || $product->status !== 'published') {
                        continue;
                    }

                    $qty = (int) $cartItem->quantity;
                    if ($product->track_inventory && $product->stock_quantity < $qty) {
                        throw new \Exception("Insufficient inventory for '{$product->title}'. Only {$product->stock_quantity} units available.");
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
                throw new \Exception("Cannot complete checkout: Cart is empty.");
            }

            if (empty($orderItemsData)) {
                throw new \Exception("No valid published products found in order.");
            }

            // Find or create customer record
            $customer = Customer::firstOrCreate(
                ['phone' => $payload['customer_phone']],
                [
                    'name' => $payload['customer_name'],
                    'email' => $payload['customer_email'] ?? null,
                ]
            );

            // Compute financial totals
            $paymentMethod = $payload['payment_method'] ?? 'cod';
            $shippingFee = (float) ($payload['shipping_fee'] ?? 0.00);
            $discountAmount = (float) ($payload['discount_amount'] ?? 0.00);
            $totalAmount = round($subtotal + $shippingFee - $discountAmount, 2);

            // Concurrency-safe unique order number generator
            $orderNumber = $this->generateUniqueOrderNumber();

            // Create Order
            $order = Order::create([
                'order_number' => $orderNumber,
                'customer_id' => $customer->id,
                'customer_name' => $payload['customer_name'],
                'customer_email' => $payload['customer_email'] ?? null,
                'customer_phone' => $payload['customer_phone'],
                'shipping_address' => $payload['shipping_address'],
                'subtotal' => $subtotal,
                'shipping_fee' => $shippingFee,
                'discount_amount' => $discountAmount,
                'total_amount' => $totalAmount,
                'payment_method' => $paymentMethod,
                'payment_status' => $paymentMethod === 'cod' ? 'unpaid' : 'pending',
                'status' => 'pending',
                'notes' => $payload['notes'] ?? null,
            ]);

            // Save frozen snapshot line items
            foreach ($orderItemsData as $item) {
                $order->items()->create($item);
            }

            // Create payment log
            Payment::create([
                'order_id' => $order->id,
                'payment_method' => $paymentMethod,
                'gateway' => $paymentMethod === 'cod' ? 'manual_cod' : $paymentMethod,
                'amount' => $totalAmount,
                'currency' => 'BDT',
                'status' => $paymentMethod === 'cod' ? 'pending' : 'pending',
            ]);

            // Convert and flush cart
            if ($cart) {
                $cart->update(['status' => 'converted']);
                $cart->items()->delete();
            }

            return $order;
        });
    }

    /**
     * Generates a collision-resistant, concurrency-safe unique order number
     */
    private function generateUniqueOrderNumber(): string
    {
        do {
            $randomSuffix = strtoupper(Str::random(6));
            $candidate = 'NIES-' . date('Ymd') . '-' . $randomSuffix;
        } while (Order::where('order_number', $candidate)->exists());

        return $candidate;
    }
}
