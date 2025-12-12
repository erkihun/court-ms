<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Align decisions table with the current create/edit form without dropping data.
 * Safely adds missing columns only.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('decisions', function (Blueprint $table) {
            // Foreign keys / base references
            if (!Schema::hasColumn('decisions', 'court_case_id')) {
                $table->foreignId('court_case_id')->nullable()->constrained('court_cases')->nullOnDelete();
            }

            // Case identifiers & parties
            if (!Schema::hasColumn('decisions', 'case_number')) {
                $table->string('case_number')->nullable();
            }
            if (!Schema::hasColumn('decisions', 'case_file_number')) {
                $table->string('case_file_number')->nullable();
            }
            if (!Schema::hasColumn('decisions', 'applicant_full_name')) {
                $table->string('applicant_full_name')->nullable();
            }
            if (!Schema::hasColumn('decisions', 'respondent_full_name')) {
                $table->string('respondent_full_name')->nullable();
            }
            if (!Schema::hasColumn('decisions', 'case_filed_date')) {
                $table->date('case_filed_date')->nullable();
            }
            if (!Schema::hasColumn('decisions', 'decision_date')) {
                $table->date('decision_date')->nullable();
            }

            // Panel + reviewer
            if (!Schema::hasColumn('decisions', 'panel_judges')) {
                $table->json('panel_judges')->nullable();
            }
            if (!Schema::hasColumn('decisions', 'panel_decision')) {
                $table->string('panel_decision', 32)->default('pending');
            }
            if (!Schema::hasColumn('decisions', 'judges_comments')) {
                $table->text('judges_comments')->nullable();
            }
            if (!Schema::hasColumn('decisions', 'reviewing_admin_user_id')) {
                $table->foreignId('reviewing_admin_user_id')->nullable()->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('decisions', 'reviewing_admin_user_name')) {
                $table->string('reviewing_admin_user_name')->nullable();
            }
            if (!Schema::hasColumn('decisions', 'reviewing_admin_user_names')) {
                $table->json('reviewing_admin_user_names')->nullable();
            }

            // Content
            if (!Schema::hasColumn('decisions', 'name')) {
                $table->string('name')->nullable();
            }
            if (!Schema::hasColumn('decisions', 'description')) {
                $table->text('description')->nullable();
            }
            if (!Schema::hasColumn('decisions', 'decision_content')) {
                $table->text('decision_content')->nullable();
            }

            // Status / timestamps
            if (!Schema::hasColumn('decisions', 'status')) {
                $table->string('status', 32)->default('draft');
            }
            if (!Schema::hasColumn('decisions', 'created_at')) {
                $table->timestamps();
            }

        });
    }

    public function down(): void
    {
        Schema::table('decisions', function (Blueprint $table) {
            // Drop only columns that are unlikely to pre-exist; skip risky drops.
            // Drop FK first if it exists
            if (Schema::hasColumn('decisions', 'reviewing_admin_user_id')) {
                $table->dropForeign(['reviewing_admin_user_id']);
            }

            foreach (['panel_judges', 'panel_decision', 'judges_comments', 'reviewing_admin_user_id', 'reviewing_admin_user_name', 'reviewing_admin_user_names', 'case_file_number'] as $col) {
                if (Schema::hasColumn('decisions', $col)) {
                    $table->dropColumn($col);
                }
            }
            // Keep core identifiers/content to avoid data loss.
        });
    }
};
