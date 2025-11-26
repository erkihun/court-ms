<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('case_witnesses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('case_id')->constrained('court_cases')->cascadeOnDelete();
            $table->string('full_name');
            $table->string('phone', 60)->nullable();
            $table->string('email', 150)->nullable();
            $table->string('address')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('case_witnesses');
    }
};
