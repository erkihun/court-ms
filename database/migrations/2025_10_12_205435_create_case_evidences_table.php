<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('case_evidences', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('case_id');
            $table->enum('type', ['document', 'human']);
            $table->string('title')->nullable();       // document name or witness name
            $table->text('description')->nullable();   // summary or testimony
            $table->string('file_path')->nullable();   // for PDF (document)
            $table->timestamps();

            $table->foreign('case_id')->references('id')->on('court_cases')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('case_evidences');
    }
};
