<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique()->index();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->string('customer_name');
            $table->string('customer_email')->nullable();
            $table->string('customer_phone');
            $table->text('shipping_address');
            $table->decimal('subtotal', 12, 2);
            $table->decimal('shipping_fee', 12, 2)->default(0.00);
            $table->decimal('discount_amount', 12, 2)->default(0.00);
            $table->decimal('total_amount', 12, 2);
            $table->string('payment_method')->default('cod'); // cod, sslcommerz, bkash, nagad
            $table->string('payment_status')->default('unpaid'); // unpaid, paid, failed, refunded
            $table->string('status')->default('pending'); // pending, confirmed, processing, shipped, delivered, cancelled
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->string('product_title_snapshot');
            $table->string('sku_snapshot')->nullable();
            $table->decimal('unit_price_snapshot', 12, 2);
            $table->integer('quantity')->default(1);
            $table->decimal('line_total', 12, 2);
            $table->timestamps();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->string('transaction_id')->nullable()->index();
            $table->string('payment_method')->default('cod');
            $table->string('gateway')->default('manual_cod');
            $table->decimal('amount', 12, 2);
            $table->string('currency')->default('BDT');
            $table->string('status')->default('pending'); // pending, success, failed, cancelled
            $table->json('gateway_response')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
