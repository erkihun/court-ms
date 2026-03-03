<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('case_inspection_findings')) {
            return;
        }

        Schema::create('case_inspection_findings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('case_inspection_request_id')->constrained('case_inspection_requests')->cascadeOnDelete();
            $table->date('finding_date');
            $table->string('title', 255);
            $table->longText('details');
            $table->longText('recommendation')->nullable();
            $table->string('severity', 20)->default('medium');
            $table->foreignId('recorded_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('case_inspection_request_id');
            $table->index('severity');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('case_inspection_findings');
    }
};
