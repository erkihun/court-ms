<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('applicant_response_replies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('case_id')->constrained('court_cases')->cascadeOnDelete();
            $table->foreignId('applicant_id')->constrained('applicants')->cascadeOnDelete();
            $table->foreignId('respondent_response_id')->constrained('respondent_responses')->cascadeOnDelete();
            $table->text('description');
            $table->string('pdf_path');
            $table->timestamps();

            $table->index(['case_id', 'respondent_response_id'], 'app_reply_case_response_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('applicant_response_replies');
    }
};
