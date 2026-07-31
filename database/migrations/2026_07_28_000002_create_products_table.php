<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('category_slug');
            $table->string('category_name')->nullable();
            $table->string('image')->nullable();
            $table->longText('description')->nullable();
            $table->longText('specifications')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->string('status')->default('published');
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
