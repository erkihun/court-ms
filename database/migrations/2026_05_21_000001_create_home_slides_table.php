<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('home_slides', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->string('badge', 120)->nullable();
            $table->string('title', 255);
            $table->text('description')->nullable();
            $table->string('primary_label', 120);
            $table->string('primary_href', 500)->default('#');
            $table->string('secondary_label', 120)->nullable();
            $table->string('secondary_href', 500)->nullable();
            $table->string('bg_style', 20)->default('blue'); // blue | orange | emerald
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('home_slides');
    }
};
