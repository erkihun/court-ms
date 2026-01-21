<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('court_cases')) {
            return;
        }

        Schema::table('court_cases', function (Blueprint $table) {
            if (Schema::hasColumn('court_cases', 'applicant_id')) {
                $table->foreign('applicant_id')
                    ->references('id')
                    ->on('applicants')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('court_cases')) {
            return;
        }

        Schema::table('court_cases', function (Blueprint $table) {
            if (!Schema::hasColumn('court_cases', 'applicant_id')) {
                return;
            }

            try {
                $table->dropForeign(['applicant_id']);
            } catch (\Throwable $e) {
                // swallow if the FK is already absent
            }
        });
    }
};
