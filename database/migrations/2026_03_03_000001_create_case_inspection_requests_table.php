<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('case_inspection_requests')) {
            return;
        }

        Schema::create('case_inspection_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('court_case_id')->constrained('court_cases')->cascadeOnDelete();
            $table->date('request_date');
            $table->string('subject', 255);
            $table->longText('request_note')->nullable();
            $table->string('status', 30)->default('pending');
            $table->foreignId('requested_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('assigned_inspector_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('court_case_id');
            $table->index('status');
            $table->index('assigned_inspector_user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('case_inspection_requests');
    }
};
