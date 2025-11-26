<?php
// database/migrations/2025_01_01_120200_create_case_messages_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('case_messages', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('case_id');
            $t->unsignedBigInteger('sender_applicant_id')->nullable();
            $t->unsignedBigInteger('sender_user_id')->nullable();
            $t->text('body');
            $t->timestamps();

            $t->foreign('case_id')->references('id')->on('court_cases')->cascadeOnDelete();
            $t->foreign('sender_applicant_id')->references('id')->on('applicants')->nullOnDelete();
            $t->foreign('sender_user_id')->references('id')->on('users')->nullOnDelete();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('case_messages');
    }
};
