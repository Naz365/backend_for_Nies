<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable()->after('id')->constrained('categories')->nullOnDelete();
            $table->string('sku')->nullable()->unique()->after('slug');
            $table->decimal('price', 12, 2)->default(0.00)->after('specifications');
            $table->decimal('compare_at_price', 12, 2)->nullable()->after('price');
            $table->integer('stock_quantity')->default(100)->after('compare_at_price');
            $table->boolean('track_inventory')->default(true)->after('stock_quantity');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropColumn([
                'category_id',
                'sku',
                'price',
                'compare_at_price',
                'stock_quantity',
                'track_inventory',
            ]);
        });
    }
};
