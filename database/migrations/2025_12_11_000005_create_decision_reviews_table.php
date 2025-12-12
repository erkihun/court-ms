<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('decision_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('decision_id')->constrained('decisions')->cascadeOnDelete();
            $table->string('case_number')->nullable();
            $table->foreignId('reviewer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('review_note')->nullable();
            $table->string('outcome', 32)->default('pending'); // approve, reject, improve, pending
            $table->timestamps();

            $table->index(['decision_id', 'outcome']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('decision_reviews');
    }
};
