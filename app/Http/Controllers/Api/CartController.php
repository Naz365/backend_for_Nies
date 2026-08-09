<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CartController extends Controller
{
    /**
     * Get or create a cart based on session token or authenticated customer
     */
    private function resolveCart(Request $request): Cart
    {
        $sessionToken = $request->header('X-Cart-Session') 
            ?? $request->input('session_token') 
            ?? $request->cookie('cart_session');

        if (!$sessionToken) {
            $sessionToken = (string) Str::uuid();
        }

        $cart = Cart::firstOrCreate(
            ['session_token' => $sessionToken, 'status' => 'active'],
            ['session_token' => $sessionToken, 'status' => 'active']
        );

        return $cart;
    }

    /**
     * Format cart payload with live product pricing and subtotal
     */
    private function formatCart(Cart $cart): array
    {
        $cart->load(['items.product:id,title,slug,image,price,stock_quantity,track_inventory,status']);

        $items = [];
        $subtotal = 0;
        $totalItems = 0;

        foreach ($cart->items as $item) {
            $product = $item->product;
            if (!$product || $product->status !== 'published') {
                continue;
            }

            $currentPrice = (float) $product->price;
            $lineTotal = round($currentPrice * $item->quantity, 2);
            $subtotal += $lineTotal;
            $totalItems += $item->quantity;

            $items[] = [
                'id' => $item->id,
                'product_id' => $product->id,
                'title' => $product->title,
                'slug' => $product->slug,
                'image' => $product->image,
                'unit_price' => $currentPrice,
                'quantity' => $item->quantity,
                'line_total' => $lineTotal,
                'stock_quantity' => $product->stock_quantity,
                'in_stock' => !$product->track_inventory || $product->stock_quantity >= $item->quantity,
            ];
        }

        return [
            'cart_id' => $cart->id,
            'session_token' => $cart->session_token,
            'items' => $items,
            'item_count' => $totalItems,
            'subtotal' => round($subtotal, 2),
            'currency' => 'BDT',
            'updated_at' => $cart->updated_at?->toIso8601String(),
        ];
    }

    /**
     * GET /api/v1/cart
     */
    public function show(Request $request): JsonResponse
    {
        try {
            $cart = $this->resolveCart($request);
            return response()->json([
                'success' => true,
                'data' => $this->formatCart($cart),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load shopping cart',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * POST /api/v1/cart/items
     */
    public function addItem(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'integer|min:1|max:100',
        ]);

        try {
            $product = Product::where('status', 'published')->findOrFail($validated['product_id']);
            $quantity = $validated['quantity'] ?? 1;

            $cart = $this->resolveCart($request);

            $cartItem = CartItem::where('cart_id', $cart->id)
                ->where('product_id', $product->id)
                ->first();

            $newQuantity = ($cartItem ? $cartItem->quantity : 0) + $quantity;

            // Server-authoritative stock check
            if ($product->track_inventory && $product->stock_quantity < $newQuantity) {
                return response()->json([
                    'success' => false,
                    'message' => "Insufficient stock. Only {$product->stock_quantity} units available.",
                ], 422);
            }

            if ($cartItem) {
                $cartItem->update([
                    'quantity' => $newQuantity,
                    'unit_price' => $product->price,
                ]);
            } else {
                CartItem::create([
                    'cart_id' => $cart->id,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'unit_price' => $product->price,
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Item added to cart',
                'data' => $this->formatCart($cart),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add item to cart',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * PUT /api/v1/cart/items/{id}
     */
    public function updateItem(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:0|max:100',
        ]);

        try {
            $cart = $this->resolveCart($request);
            $cartItem = CartItem::where('cart_id', $cart->id)->findOrFail($id);

            if ($validated['quantity'] <= 0) {
                $cartItem->delete();
            } else {
                $product = $cartItem->product;
                if ($product->track_inventory && $product->stock_quantity < $validated['quantity']) {
                    return response()->json([
                        'success' => false,
                        'message' => "Requested quantity exceeds available stock ({$product->stock_quantity}).",
                    ], 422);
                }

                $cartItem->update([
                    'quantity' => $validated['quantity'],
                    'unit_price' => $product->price,
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Cart updated',
                'data' => $this->formatCart($cart),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update cart item',
            ], 404);
        }
    }

    /**
     * DELETE /api/v1/cart/items/{id}
     */
    public function removeItem(Request $request, int $id): JsonResponse
    {
        try {
            $cart = $this->resolveCart($request);
            CartItem::where('cart_id', $cart->id)->where('id', $id)->delete();

            return response()->json([
                'success' => true,
                'message' => 'Item removed from cart',
                'data' => $this->formatCart($cart),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove cart item',
            ], 500);
        }
    }

    /**
     * DELETE /api/v1/cart
     */
    public function clearCart(Request $request): JsonResponse
    {
        try {
            $cart = $this->resolveCart($request);
            $cart->items()->delete();

            return response()->json([
                'success' => true,
                'message' => 'Cart cleared',
                'data' => $this->formatCart($cart),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to clear cart',
            ], 500);
        }
    }
}
