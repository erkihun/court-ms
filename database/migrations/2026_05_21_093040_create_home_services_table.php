<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('home_services', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->string('title', 255);
            $table->text('description')->nullable();
            $table->string('meta', 120)->nullable();
            $table->json('features')->nullable();
            $table->string('icon_type', 30)->default('document'); // document|calendar|lock|chart|database|chat
            $table->string('accent', 30)->default('blue');        // blue|orange|emerald|violet|amber|pink
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('home_services');
    }
};
