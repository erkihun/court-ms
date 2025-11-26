// database/migrations/2025_01_01_000000_create_case_hearings_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('case_hearings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('case_id');
            $table->dateTime('hearing_at');            // date & time
            $table->string('location')->nullable();     // e.g. Courtroom 2
            $table->string('type')->nullable();         // e.g. Preliminary, Hearing, Ruling
            $table->text('notes')->nullable();          // internal notes
            $table->unsignedBigInteger('created_by_user_id')->nullable();
            $table->timestamps();

            $table->foreign('case_id')->references('id')->on('court_cases')->onDelete('cascade');
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('case_hearings');
    }
};
