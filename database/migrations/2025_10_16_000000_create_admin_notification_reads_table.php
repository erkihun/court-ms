<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('admin_notification_reads', function (Blueprint $table) {
            // Use this instead of $table->id() to avoid the error
            $table->bigIncrements('id');

            // If foreignId() gives you trouble, use unsignedBigInteger + foreign()
            $table->unsignedBigInteger('user_id');

            // If ENUMs are annoying in your MySQL, you can switch to string(20)
            $table->enum('type', ['message', 'case', 'hearing']);
            $table->unsignedBigInteger('source_id');

            $table->timestamp('seen_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'type', 'source_id'], 'admin_read_unique');
            $table->index('user_id');

            $table->foreign('user_id')
                ->references('id')->on('users')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_notification_reads');
    }
};
