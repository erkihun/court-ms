<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('respondent_notification_reads', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('respondent_id');
            $table->string('type', 32);
            $table->unsignedBigInteger('source_id');
            $table->timestamp('seen_at')->nullable();
            $table->timestamps();

            $table->unique(['respondent_id', 'type', 'source_id'], 'respondent_read_unique');
            $table->index('respondent_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('respondent_notification_reads');
    }
};
