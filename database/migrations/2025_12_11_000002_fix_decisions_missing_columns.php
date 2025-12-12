<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('decisions', function (Blueprint $table) {
            if (!Schema::hasColumn('decisions', 'case_number')) {
                $table->string('case_number')->nullable();
            }

            if (!Schema::hasColumn('decisions', 'decision_content')) {
                $table->text('decision_content')->nullable();
            }

            if (!Schema::hasColumn('decisions', 'decision_date')) {
                $table->date('decision_date')->nullable();
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
            // Only drop fields we added, guarding existence.
            if (Schema::hasColumn('decisions', 'judges_comments')) {
                $table->dropColumn('judges_comments');
            }
            if (Schema::hasColumn('decisions', 'panel_decision')) {
                $table->dropColumn('panel_decision');
            }
            if (Schema::hasColumn('decisions', 'panel_judges')) {
                $table->dropColumn('panel_judges');
            }
            if (Schema::hasColumn('decisions', 'case_file_number')) {
                $table->dropColumn('case_file_number');
            }
            // We avoid dropping case_number/decision_content in down to not remove existing data.
        });
    }
};
