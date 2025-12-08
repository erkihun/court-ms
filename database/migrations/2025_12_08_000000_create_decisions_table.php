<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('decisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('court_case_id')->constrained('court_cases')->cascadeOnDelete();
            $table->string('case_number');
            $table->string('applicant_full_name')->nullable();
            $table->string('respondent_full_name')->nullable();
            $table->date('case_filed_date')->nullable();
            $table->date('decision_date')->nullable();
            $table->foreignId('reviewing_admin_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('reviewing_admin_user_name')->nullable();
            $table->json('reviewing_admin_user_names')->nullable();
            $table->string('name');
            $table->text('description')->nullable();
            $table->text('decision_content');
            $table->string('status', 32)->default('draft');
            $table->timestamps();
            $table->index(['court_case_id', 'decision_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('decisions');
    }
};
