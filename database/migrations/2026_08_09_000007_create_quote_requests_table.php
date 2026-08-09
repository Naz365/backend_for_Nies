<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quote_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_number')->unique()->index();
            $table->string('customer_name');
            $table->string('company_name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone');
            $table->string('service_type'); // fire_extinguishers, suppression_system, alarm_system, cctv, access_control, refilling, consultation
            $table->text('project_description');
            $table->string('status')->default('new'); // new, contacted, quoted, closed
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quote_requests');
    }
};
