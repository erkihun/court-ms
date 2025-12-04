<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Only create if it does not already exist (avoids collisions on existing installs)
        if (Schema::hasTable('bench_notes')) {
            return;
        }

        Schema::create('bench_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('case_id')->constrained('court_cases')->cascadeOnDelete();
            $table->foreignId('hearing_id')->nullable()->constrained('case_hearings')->nullOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('title', 255)->default('');
            $table->text('note');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bench_notes');
    }
};
