<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_logos', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('logo_path');
            $table->string('website_url')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Safe data preservation: Copy existing customer logos into client_logos
        if (Schema::hasTable('customers')) {
            try {
                $existing = DB::table('customers')->get();
                foreach ($existing as $row) {
                    if (isset($row->logo_path)) {
                        DB::table('client_logos')->insert([
                            'name' => $row->name ?? 'Client',
                            'logo_path' => $row->logo_path ?? '',
                            'website_url' => $row->website_url ?? null,
                            'sort_order' => $row->sort_order ?? 0,
                            'is_active' => $row->is_active ?? true,
                            'created_at' => $row->created_at ?? now(),
                            'updated_at' => $row->updated_at ?? now(),
                        ]);
                    }
                }
            } catch (\Throwable $e) {
                // Ignore if customers schema doesn't match legacy structure
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('client_logos');
    }
};
