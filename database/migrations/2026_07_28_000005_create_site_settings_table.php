<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_settings', function (Blueprint $table) {
            $table->id();
            $table->string('address');
            $table->string('phone_primary');
            $table->string('phone_secondary')->nullable();
            $table->string('telephone')->nullable();
            $table->string('fax')->nullable();
            $table->json('emails')->nullable();
            $table->string('company_profile_pdf')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_settings');
    }
};
