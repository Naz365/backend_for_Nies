<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('media')) {
            Schema::create('media', function (Blueprint $table) {
                $table->id();
                $table->string('disk')->default('public'); // public, s3, r2
                $table->string('path');
                $table->string('filename');
                $table->string('mime_type')->nullable();
                $table->unsignedBigInteger('size')->nullable();
                $table->string('alt_text')->nullable();
                $table->json('metadata')->nullable();
                $table->nullableMorphs('mediable'); // polymorphic attachment to Product, BlogPost, Project, etc.
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};
