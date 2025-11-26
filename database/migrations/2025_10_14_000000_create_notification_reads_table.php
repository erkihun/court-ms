<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_reads', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('applicant_id');  // who read it
            $table->string('type', 20);                  // message|hearing|status
            $table->unsignedBigInteger('source_id');     // id from case_messages / case_hearings / case_status_logs
            $table->timestamps();

            // if you have FK constraints set up and want them:
            // $table->foreign('applicant_id')->references('id')->on('applicants')->onDelete('cascade');

            // ensure an item is only marked once per applicant
            $table->unique(['applicant_id', 'type', 'source_id']);

            $table->index(['applicant_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_reads');
    }
};
