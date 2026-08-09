<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('service_requests')) {
            Schema::create('service_requests', function (Blueprint $table) {
                $table->id();
                $table->string('request_number')->unique();
                $table->string('customer_name');
                $table->string('company_name')->nullable();
                $table->string('phone');
                $table->string('email')->nullable();
                $table->string('service_category'); // refill, inspection, fire_alarm, fire_hydrant, fire_pump, cctv, access_control, maintenance
                $table->text('location_address');
                $table->text('equipment_details')->nullable();
                $table->string('urgency')->default('normal'); // low, normal, high, emergency
                $table->string('status')->default('pending_review'); // pending_review, assigned, site_visit_scheduled, inspection_completed, quoted, resolved, cancelled
                $table->string('technician_assigned')->nullable();
                $table->text('technician_notes')->nullable();
                $table->date('scheduled_visit_date')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('service_requests');
    }
};
