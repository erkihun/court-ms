<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('respondent_case_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('respondent_id')->constrained('respondents')->cascadeOnDelete();
            $table->foreignId('case_id')->constrained('court_cases')->cascadeOnDelete();
            $table->string('case_number', 60)->nullable();
            $table->timestamp('viewed_at')->useCurrent();
            $table->timestamps();
            $table->unique(['respondent_id', 'case_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('respondent_case_views');
    }
};
