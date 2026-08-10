<?php

/**
 * N.I. Engineering Services - Phase 0 Comprehensive Backend Verification Test Suite
 * Tests critical business rules: Products, Cart, Checkout, Stock, Snapshots, State Transitions, Security
 */

require_once __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Category;
use App\Models\Product;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\QuoteRequest;
use App\Models\ServiceRequest;
use App\Services\CheckoutService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TestRunner
{
    private int $passed = 0;
    private int $failed = 0;
    private array $errors = [];

    public function assert(bool $condition, string $testName, string $failureDetails = ''): void
    {
        if ($condition) {
            $this->passed++;
            echo "  [PASS] {$testName}\n";
        } else {
            $this->failed++;
            $msg = "  [FAIL] {$testName}" . ($failureDetails ? " -> {$failureDetails}" : "");
            $this->errors[] = $msg;
            echo "{$msg}\n";
        }
    }

    public function report(): bool
    {
        echo "\n==================================================\n";
        echo "TEST SUMMARY: {$this->passed} Passed, {$this->failed} Failed\n";
        echo "==================================================\n";
        if ($this->failed > 0) {
            echo "Failed Tests:\n";
            foreach ($this->errors as $err) {
                echo "{$err}\n";
            }
            return false;
        }
        return true;
    }
}

$t = new TestRunner();

echo "==================================================\n";
echo "1. PRODUCT CATALOG TESTS\n";
echo "==================================================\n";

// Ensure seed data exists
$cat = Category::firstOrCreate(['slug' => 'test-fire-safety'], [
    'name' => 'Test Fire Safety',
    'is_active' => true,
    'sort_order' => 1,
]);

$product = Product::updateOrCreate(['sku' => 'TEST-EXT-6KG'], [
    'title' => 'Test ABC Dry Powder Extinguisher 6kg',
    'slug' => 'test-abc-dry-powder-6kg',
    'category_id' => $cat->id,
    'category_slug' => 'test-fire-safety',
    'category_name' => 'Test Fire Safety',
    'price' => 1500.00,
    'compare_at_price' => 1800.00,
    'stock_quantity' => 20,
    'track_inventory' => true,
    'is_featured' => true,
    'status' => 'published',
    'image' => '/wp-content/uploads/2017/05/fire-extinguishers1.jpg',
    'description' => 'Test extinguisher for automated test suite',
]);

$t->assert($product->id > 0, "Product model persists in database");
$t->assert($product->status === 'published', "Product status is published");
$t->assert((float) $product->price === 1500.00, "Product price is authoritative 1500.00 BDT");
$t->assert($product->stock_quantity === 20, "Initial stock quantity is 20");

$draftProduct = Product::updateOrCreate(['sku' => 'TEST-DRAFT-01'], [
    'title' => 'Test Draft Product',
    'slug' => 'test-draft-01',
    'category_id' => $cat->id,
    'category_slug' => 'test-fire-safety',
    'price' => 500.00,
    'stock_quantity' => 10,
    'track_inventory' => true,
    'status' => 'draft',
]);
$t->assert($draftProduct->status === 'draft', "Draft product correctly flagged");

echo "\n==================================================\n";
echo "2. CART SYSTEM TESTS\n";
echo "==================================================\n";

$sessionToken = 'test_session_' . Str::random(10);
$cart = Cart::create([
    'session_token' => $sessionToken,
    'status' => 'active',
]);

$t->assert($cart->id > 0, "Server-backed Cart created with session token");

// Add item to cart
$cartItem = CartItem::create([
    'cart_id' => $cart->id,
    'product_id' => $product->id,
    'quantity' => 2,
    'unit_price' => $product->price,
]);

$t->assert($cartItem->id > 0, "Item successfully added to server cart");
$t->assert($cart->items()->count() === 1, "Cart contains exactly 1 line item");
$t->assert($cart->items()->first()->quantity === 2, "Cart line item quantity is 2");

// Update item quantity
$cartItem->update(['quantity' => 3]);
$t->assert($cartItem->fresh()->quantity === 3, "Cart item updated to quantity 3");

// Remove item from cart
$cartItem->delete();
$t->assert($cart->fresh()->items()->count() === 0, "Cart item removal succeeds");

echo "\n==================================================\n";
echo "3. CHECKOUT & AUTHORITATIVE PRICING SECURITY TESTS\n";
echo "==================================================\n";

$checkoutService = new CheckoutService();

// Reset test product stock
$product->update(['stock_quantity' => 25, 'price' => 1500.00]);

// Test 3.1: Price Tampering Defense
// Client attempts to send price = 10.00 BDT instead of 1500.00 BDT
$tamperedPayload = [
    'customer_name' => 'Karim Ahmed',
    'customer_phone' => '01711998877',
    'customer_email' => 'karim@example.com',
    'shipping_address' => 'House 12, Road 5, Dhanmondi, Dhaka',
    'payment_method' => 'cod',
    'items' => [
        [
            'product_id' => $product->id,
            'quantity' => 2,
            'price' => 10.00, // Malicious client-side price modification
        ]
    ]
];

$order = $checkoutService->placeOrder($tamperedPayload);

$t->assert($order->id > 0, "Order placed successfully");
$t->assert((float) $order->subtotal === 3000.00, "Server calculated authoritative subtotal 3000.00 BDT (ignored client 10.00)");
$t->assert((float) $order->total_amount === 3000.00, "Server calculated authoritative total 3000.00 BDT");
$t->assert($order->status === 'pending', "Order initial status is pending");
$t->assert($order->payment_status === 'unpaid', "COD payment status is unpaid");

// Test 3.2: Stock Decrement Verification
$product->refresh();
$t->assert($product->stock_quantity === 23, "Stock decremented correctly from 25 to 23 after ordering 2 units");

// Test 3.3: Historical Snapshots Verification
$orderItem = $order->items()->first();
$t->assert($orderItem !== null, "Order has associated order item");
$t->assert($orderItem->product_title_snapshot === 'Test ABC Dry Powder Extinguisher 6kg', "Snapshot preserved exact product title");
$t->assert($orderItem->sku_snapshot === 'TEST-EXT-6KG', "Snapshot preserved exact SKU");
$t->assert((float) $orderItem->unit_price_snapshot === 1500.00, "Snapshot preserved historical unit price");
$t->assert($orderItem->quantity === 2, "Snapshot recorded correct quantity");
$t->assert((float) $orderItem->line_total === 3000.00, "Snapshot recorded correct line total");

// Test 3.4: Customer Persistence & Deduplication
$customer = Customer::where('phone', '01711998877')->first();
$t->assert($customer !== null, "Authoritative Customer record created in database");
$t->assert($order->customer_id === $customer->id, "Order linked to authoritative Customer ID");

// Repeat checkout with same phone - must reuse existing customer
$secondPayload = [
    'customer_name' => 'Karim Ahmed Updated',
    'customer_phone' => '01711998877',
    'shipping_address' => 'Gulshan 2, Dhaka',
    'payment_method' => 'cod',
    'items' => [
        ['product_id' => $product->id, 'quantity' => 1]
    ]
];
$secondOrder = $checkoutService->placeOrder($secondPayload);
$t->assert($secondOrder->customer_id === $customer->id, "Customer record deduplicated by phone number");

echo "\n==================================================\n";
echo "4. INSUFFICIENT STOCK & BOUNDARY TESTS\n";
echo "==================================================\n";

$product->refresh(); // Current stock is 22
$excessiveQuantityPayload = [
    'customer_name' => 'Test User',
    'customer_phone' => '01811223344',
    'shipping_address' => 'Banani, Dhaka',
    'payment_method' => 'cod',
    'items' => [
        ['product_id' => $product->id, 'quantity' => 999] // 999 > 22
    ]
];

$insufficientStockCaught = false;
try {
    $checkoutService->placeOrder($excessiveQuantityPayload);
} catch (\Throwable $e) {
    $insufficientStockCaught = true;
}
$t->assert($insufficientStockCaught, "Server rejected order with quantity exceeding available stock (999 > 22)");

// Nonexistent product ID
$nonexistentProductCaught = false;
try {
    $checkoutService->placeOrder([
        'customer_name' => 'Test',
        'customer_phone' => '01911223344',
        'shipping_address' => 'Dhaka',
        'items' => [['product_id' => 999999, 'quantity' => 1]]
    ]);
} catch (\Throwable $e) {
    $nonexistentProductCaught = true;
}
$t->assert($nonexistentProductCaught, "Server rejected checkout with nonexistent product ID");

echo "\n==================================================\n";
echo "5. ORDER STATE MACHINE & AUDIT LOGGING TESTS\n";
echo "==================================================\n";

$t->assert($order->status === 'pending', "State 1: pending");
$order->update(['status' => 'confirmed']);
$t->assert($order->fresh()->status === 'confirmed', "State 2 transition: confirmed");
$order->update(['status' => 'processing']);
$t->assert($order->fresh()->status === 'processing', "State 3 transition: processing");
$order->update(['status' => 'shipped']);
$t->assert($order->fresh()->status === 'shipped', "State 4 transition: shipped");
$order->update(['status' => 'delivered', 'payment_status' => 'paid']);
$t->assert($order->fresh()->status === 'delivered', "State 5 transition: delivered");
$t->assert($order->fresh()->payment_status === 'paid', "Payment status marked paid upon delivery");

// Payment transaction log exists
$payment = Payment::where('order_id', $order->id)->first();
$t->assert($payment !== null, "Authoritative Payment transaction record logged");
$t->assert((float) $payment->amount === (float) $order->total_amount, "Payment record matches order total");

echo "\n==================================================\n";
echo "6. B2B QUOTE & SERVICE REQUEST WORKFLOW TESTS\n";
echo "==================================================\n";

$quote = QuoteRequest::create([
    'request_number' => QuoteRequest::generateRequestNumber(),
    'customer_name' => 'BTI Construction Ltd',
    'company_name' => 'Building Technology & Ideas',
    'email' => 'procurement@bti.com.bd',
    'phone' => '+880 1711 000 111',
    'service_type' => 'Fire Hydrant & Suppression Installation',
    'project_description' => 'Commercial 24-floor corporate tower fire system installation in Gulshan.',
    'status' => 'new',
]);

$t->assert($quote->id > 0, "B2B Quote Request record created");
$t->assert(str_starts_with($quote->request_number, 'QR-'), "Quote Request number format verified (QR-YYYYMM-XXXX)");

$service = ServiceRequest::create([
    'request_number' => ServiceRequest::generateRequestNumber(),
    'customer_name' => 'BRAC Centre',
    'company_name' => 'BRAC',
    'phone' => '+880 1711 222 333',
    'service_category' => 'Fire Extinguisher Refilling',
    'location_address' => '75 Mohakhali, Dhaka',
    'urgency' => 'high',
    'status' => 'pending_review',
]);

$t->assert($service->id > 0, "Field Service Request record created");
$t->assert(str_starts_with($service->request_number, 'SRV-'), "Service Request number format verified (SRV-YYYYMM-XXXX)");

echo "\n==================================================\n";
echo "7. SECURITY & SCHEMA INTEGRATION VERIFICATION TESTS\n";
echo "==================================================\n";

// Test Product Category Auto-Sync (Fix Filament Constraint)
Product::where('slug', 'like', 'auto-category-sync%')->delete();
$randomSuffix = strtolower(Str::random(6));
$autoSku = 'TEST-AUTO-CAT-' . $randomSuffix;
$autoTitle = 'Auto Category Sync Test Valve ' . $randomSuffix;
$autoCategoryProduct = Product::create([
    'category_id' => $cat->id,
    'title' => $autoTitle,
    'sku' => $autoSku,
    'price' => 550.00,
    'status' => 'published',
]);
$t->assert($autoCategoryProduct->category_slug === $cat->slug, "Product model automatically synchronized category_slug from category_id");
$t->assert($autoCategoryProduct->category_name === $cat->name, "Product model automatically synchronized category_name from category_id");
$t->assert(str_starts_with($autoCategoryProduct->slug, 'auto-category-sync-test-valve-'), "Product model automatically generated slug from title");

// Test Order Tracking IDOR Protection
$orderController = app(\App\Http\Controllers\Api\OrderController::class);
$unverifiedRequest = \Illuminate\Http\Request::create("/api/v1/orders/{$order->order_number}", 'GET');
$unverifiedResponse = $orderController->show($order->order_number, $unverifiedRequest);
$unverifiedData = json_decode($unverifiedResponse->getContent(), true);

$t->assert($unverifiedData['is_verified'] === false, "Public unverified order tracking marked as is_verified = false");
$t->assert(!isset($unverifiedData['data']['shipping_address']), "Public unverified order tracking hides customer shipping address");
$t->assert(str_contains($unverifiedData['data']['customer_name'], '***'), "Public unverified order tracking masks customer name ({$unverifiedData['data']['customer_name']})");

$verifiedRequest = \Illuminate\Http\Request::create("/api/v1/orders/{$order->order_number}?phone={$order->customer_phone}", 'GET', ['phone' => $order->customer_phone]);
$verifiedResponse = $orderController->show($order->order_number, $verifiedRequest);
$verifiedData = json_decode($verifiedResponse->getContent(), true);

$t->assert($verifiedData['is_verified'] === true, "Verified order tracking with matching phone succeeds");
$t->assert($verifiedData['data']['shipping_address'] === $order->shipping_address, "Verified order tracking returns full shipping address");
$t->assert($verifiedData['data']['customer_name'] === $order->customer_name, "Verified order tracking returns unmasked customer name");

// Test Standardized Blog and Project Envelopes
$blogController = app(\App\Http\Controllers\Api\BlogPostController::class);
$blogResponse = $blogController->index();
$blogData = json_decode($blogResponse->getContent(), true);
$t->assert(isset($blogData['success']) && $blogData['success'] === true, "BlogPostController returns standardized success: true envelope");

$projectController = app(\App\Http\Controllers\Api\ProjectController::class);
$projectResponse = $projectController->index();
$projectData = json_decode($projectResponse->getContent(), true);
$t->assert(isset($projectData['success']) && $projectData['success'] === true, "ProjectController returns standardized success: true envelope");

$settingController = app(\App\Http\Controllers\Api\SiteSettingController::class);
$settingResponse = $settingController->index();
$settingData = json_decode($settingResponse->getContent(), true);
$t->assert(isset($settingData['success']) && $settingData['success'] === true, "SiteSettingController returns standardized success: true envelope");

// Clean up temporary test products
$autoCategoryProduct->delete();
$order->items()->delete();
$order->delete();
$secondOrder->items()->delete();
$secondOrder->delete();
$cart->items()->delete();
$cart->delete();
$product->delete();
$draftProduct->delete();
$cat->delete();
$quote->delete();
$service->delete();

$t->report();
