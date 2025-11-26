<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('applicant_notification_prefs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('applicant_id')->unique();
            $table->boolean('email_status')->default(true);
            $table->boolean('email_hearing')->default(true);
            $table->boolean('email_message')->default(true);
            $table->boolean('email_weekly_digest')->default(false);
            $table->timestamps();

            $table->foreign('applicant_id')
                ->references('id')->on('applicants')
                ->onDelete('cascade');
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('applicant_notification_prefs');
    }
};
