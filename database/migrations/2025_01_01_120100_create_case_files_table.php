<?php
// database/migrations/2025_01_01_120100_create_case_files_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('case_files', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('case_id');
            $t->unsignedBigInteger('uploaded_by_applicant_id')->nullable(); // applicant uploads
            $t->unsignedBigInteger('uploaded_by_user_id')->nullable(); // admin uploads
            $t->string('label')->nullable(); // e.g. "Police report", "Contract"
            $t->string('path');              // storage path
            $t->string('mime')->nullable();
            $t->unsignedInteger('size')->nullable();
            $t->timestamps();

            $t->foreign('case_id')->references('id')->on('court_cases')->cascadeOnDelete();
            $t->foreign('uploaded_by_applicant_id')->references('id')->on('applicants')->nullOnDelete();
            $t->foreign('uploaded_by_user_id')->references('id')->on('users')->nullOnDelete();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('case_files');
    }
};
