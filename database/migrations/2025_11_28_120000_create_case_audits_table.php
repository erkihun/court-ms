<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('case_audits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('case_id')->constrained('court_cases')->cascadeOnDelete();
            $table->string('action', 100); // e.g., status_updated, review_decision, message_posted
            $table->string('actor_type', 30)->nullable(); // user|applicant|system
            $table->unsignedBigInteger('actor_id')->nullable();
            $table->json('meta')->nullable(); // free-form context (notes, status values, file ids)
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('case_audits');
    }
};
