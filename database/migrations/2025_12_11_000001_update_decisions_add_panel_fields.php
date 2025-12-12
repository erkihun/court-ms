<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('decisions', function (Blueprint $table) {
            // Some older databases might be missing case_number; ensure it exists.
            if (!Schema::hasColumn('decisions', 'case_number')) {
                $table->string('case_number')->nullable();
            }

            // Ensure decision content column exists for the editor body.
            if (!Schema::hasColumn('decisions', 'decision_content')) {
                $table->text('decision_content')->nullable();
            }

            if (!Schema::hasColumn('decisions', 'case_file_number')) {
                $table->string('case_file_number')->nullable();
            }

            if (!Schema::hasColumn('decisions', 'panel_judges')) {
                $table->json('panel_judges')->nullable();
            }

            if (!Schema::hasColumn('decisions', 'panel_decision')) {
                $table->string('panel_decision', 32)->default('pending');
            }

            if (!Schema::hasColumn('decisions', 'judges_comments')) {
                $table->text('judges_comments')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('decisions', function (Blueprint $table) {
            $table->dropColumn(['case_file_number', 'panel_judges', 'panel_decision', 'judges_comments']);
        });
    }
};
