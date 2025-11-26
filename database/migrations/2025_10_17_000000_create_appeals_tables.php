<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('appeals', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('court_case_id');
            $t->unsignedBigInteger('applicant_id')->nullable();
            $t->unsignedBigInteger('submitted_by_user_id')->nullable(); // if staff files it
            $t->string('appeal_number')->unique();
            $t->string('title');
            $t->text('grounds')->nullable();
            $t->enum('status', ['draft', 'submitted', 'under_review', 'approved', 'rejected', 'closed'])->default('draft');
            $t->timestamp('submitted_at')->nullable();
            $t->unsignedBigInteger('decided_by_user_id')->nullable();
            $t->timestamp('decided_at')->nullable();
            $t->text('decision_notes')->nullable();
            $t->timestamps();

            $t->foreign('court_case_id')->references('id')->on('court_cases')->cascadeOnDelete();
            $t->foreign('applicant_id')->references('id')->on('applicants')->nullOnDelete();
            $t->foreign('submitted_by_user_id')->references('id')->on('users')->nullOnDelete();
            $t->foreign('decided_by_user_id')->references('id')->on('users')->nullOnDelete();
        });

        Schema::create('appeal_documents', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('appeal_id');
            $t->string('label')->nullable();
            $t->string('path');
            $t->string('mime')->nullable();
            $t->unsignedInteger('size')->nullable();
            $t->timestamps();

            $t->foreign('appeal_id')->references('id')->on('appeals')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appeal_documents');
        Schema::dropIfExists('appeals');
    }
};
