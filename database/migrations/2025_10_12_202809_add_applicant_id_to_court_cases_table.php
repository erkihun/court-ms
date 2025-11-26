<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('court_cases', function (Blueprint $table) {
            if (!Schema::hasColumn('court_cases', 'applicant_id')) {
                $table->unsignedBigInteger('applicant_id')->nullable()->after('id');
                $table->foreign('applicant_id')->references('id')->on('applicants')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('court_cases', function (Blueprint $table) {
            if (Schema::hasColumn('court_cases', 'applicant_id')) {
                try {
                    $table->dropForeign(['applicant_id']);
                } catch (\Throwable $e) {
                }
                $table->dropColumn('applicant_id');
            }
        });
    }
};
