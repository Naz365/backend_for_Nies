<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('fire_safety_assets')) {
            Schema::create('fire_safety_assets', function (Blueprint $table) {
                $table->id();
                $table->string('asset_tag')->unique(); // e.g. NIES-AST-10024
                $table->string('serial_number')->nullable();
                $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
                $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
                $table->string('asset_name'); // e.g. 6kg ABC Powder Cylinder #4 - 2nd Floor Server Room
                $table->string('equipment_type')->default('extinguisher'); // extinguisher, alarm_panel, smoke_detector, hydrant_valve, pump, fm200_cylinder
                $table->string('location_in_building')->nullable();
                $table->string('capacity_rating')->nullable(); // e.g. 6kg, 3kg, 45L
                $table->date('installed_date')->nullable();
                $table->date('last_serviced_date')->nullable();
                $table->date('next_service_due_date')->nullable();
                $table->string('status')->default('active'); // active, needs_refill, inspection_overdue, decommissioned
                $table->string('qr_code_token')->nullable()->unique();
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index('next_service_due_date');
                $table->index('status');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('fire_safety_assets');
    }
};
