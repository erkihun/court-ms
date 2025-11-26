<?php

// database/migrations/2025_01_01_120000_create_case_status_logs_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('case_status_logs', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('case_id');
            $t->string('from_status')->nullable();
            $t->string('to_status');
            $t->unsignedBigInteger('changed_by_user_id')->nullable(); // admin user, nullable if system
            $t->timestamps();

            $t->foreign('case_id')->references('id')->on('court_cases')->cascadeOnDelete();
            $t->foreign('changed_by_user_id')->references('id')->on('users')->nullOnDelete();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('case_status_logs');
    }
};
