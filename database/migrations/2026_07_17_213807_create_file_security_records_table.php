<?php

declare(strict_types=1);

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
        Schema::create('file_security_records', function (Blueprint $table) {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->string('disk', 64);
            $table->string('path')->unique();
            $table->string('original_name')->nullable();
            $table->string('detected_mime', 191)->nullable();
            $table->unsignedBigInteger('size_bytes')->nullable();
            $table->char('sha256', 64)->nullable()->index();
            $table->string('scan_status', 32)->default('not_configured')->index();
            $table->string('scanner', 64)->nullable();
            $table->text('scan_message')->nullable();
            $table->string('related_type', 100)->nullable();
            $table->unsignedBigInteger('related_id')->nullable();
            $table->unsignedBigInteger('uploaded_by_user_id')->nullable()->index();
            $table->unsignedBigInteger('uploaded_by_applicant_id')->nullable()->index();
            $table->timestamp('scanned_at')->nullable();
            $table->timestamps();

            $table->index(['related_type', 'related_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('file_security_records');
    }
};
