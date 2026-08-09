<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop old legacy customers table (data has been preserved in client_logos)
        if (Schema::hasColumn('customers', 'logo_path')) {
            Schema::dropIfExists('customers');
        }

        if (!Schema::hasTable('customers')) {
            Schema::create('customers', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('name');
                $table->string('email')->nullable()->index();
                $table->string('phone')->index();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('addresses')) {
            Schema::create('addresses', function (Blueprint $table) {
                $table->id();
                $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
                $table->string('type')->default('shipping'); // shipping, billing
                $table->string('address_line_1');
                $table->string('address_line_2')->nullable();
                $table->string('city')->default('Dhaka');
                $table->string('postal_code')->nullable();
                $table->boolean('is_default')->default(true);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('addresses');
        Schema::dropIfExists('customers');
    }
};
