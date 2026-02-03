<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasTable('case_inspections')) {
            return;
        }

        Schema::create('case_inspections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('court_case_id')->constrained('court_cases')->cascadeOnDelete();
            $table->date('inspection_date');
            $table->string('summary', 255);
            $table->longText('details');
            $table->foreignId('inspected_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('case_inspections');
    }
};
