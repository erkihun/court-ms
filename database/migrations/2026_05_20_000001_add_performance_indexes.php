<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private function hasIndex(string $table, string $indexName): bool
    {
        return (bool) DB::selectOne(
            "SELECT COUNT(1) as cnt
             FROM information_schema.STATISTICS
             WHERE table_schema = DATABASE()
               AND table_name = ?
               AND index_name = ?",
            [$table, $indexName]
        )?->cnt;
    }

    public function up(): void
    {
        // court_cases: status (heavily filtered on dashboard/lists)
        if (!$this->hasIndex('court_cases', 'idx_court_cases_status')) {
            Schema::table('court_cases', function (Blueprint $table) {
                $table->index('status', 'idx_court_cases_status');
            });
        }

        // court_cases: applicant_id (every applicant-facing query)
        if (!$this->hasIndex('court_cases', 'idx_court_cases_applicant_id')) {
            Schema::table('court_cases', function (Blueprint $table) {
                $table->index('applicant_id', 'idx_court_cases_applicant_id');
            });
        }

        // court_cases: created_at (date-range dashboard queries)
        if (!$this->hasIndex('court_cases', 'idx_court_cases_created_at')) {
            Schema::table('court_cases', function (Blueprint $table) {
                $table->index('created_at', 'idx_court_cases_created_at');
            });
        }

        // case_hearings: case_id + hearing_at composite (notification query)
        if (!$this->hasIndex('case_hearings', 'idx_case_hearings_case_hearing_at')) {
            Schema::table('case_hearings', function (Blueprint $table) {
                $table->index(['case_id', 'hearing_at'], 'idx_case_hearings_case_hearing_at');
            });
        }

        // case_messages: case_id + created_at (notification query)
        if (!$this->hasIndex('case_messages', 'idx_case_messages_case_created')) {
            Schema::table('case_messages', function (Blueprint $table) {
                $table->index(['case_id', 'created_at'], 'idx_case_messages_case_created');
            });
        }

        // case_status_logs: case_id + created_at (notification query)
        if (Schema::hasTable('case_status_logs') && !$this->hasIndex('case_status_logs', 'idx_case_status_logs_case_created')) {
            Schema::table('case_status_logs', function (Blueprint $table) {
                $table->index(['case_id', 'created_at'], 'idx_case_status_logs_case_created');
            });
        }

        // letters: case_number (applicant dashboard and letter queries)
        if (!$this->hasIndex('letters', 'idx_letters_case_number')) {
            Schema::table('letters', function (Blueprint $table) {
                $table->index('case_number', 'idx_letters_case_number');
            });
        }

        // letters: approval_status (filtered on every letter query)
        if (!$this->hasIndex('letters', 'idx_letters_approval_status')) {
            Schema::table('letters', function (Blueprint $table) {
                $table->index('approval_status', 'idx_letters_approval_status');
            });
        }

        // respondent_responses: case_number
        if (Schema::hasTable('respondent_responses') && !$this->hasIndex('respondent_responses', 'idx_respondent_responses_case_number')) {
            Schema::table('respondent_responses', function (Blueprint $table) {
                $table->index('case_number', 'idx_respondent_responses_case_number');
            });
        }

        // respondent_responses: review_status
        if (Schema::hasTable('respondent_responses') && !$this->hasIndex('respondent_responses', 'idx_respondent_responses_review_status')) {
            Schema::table('respondent_responses', function (Blueprint $table) {
                $table->index('review_status', 'idx_respondent_responses_review_status');
            });
        }

        // respondent_case_views: respondent_id (myCases query)
        if (Schema::hasTable('respondent_case_views') && !$this->hasIndex('respondent_case_views', 'idx_respondent_case_views_respondent_id')) {
            Schema::table('respondent_case_views', function (Blueprint $table) {
                $table->index('respondent_id', 'idx_respondent_case_views_respondent_id');
            });
        }

        // applicants: email (login + lookup queries)
        if (!$this->hasIndex('applicants', 'idx_applicants_email')) {
            Schema::table('applicants', function (Blueprint $table) {
                $table->index('email', 'idx_applicants_email');
            });
        }

        // applicants: is_active (filtered on password reset)
        if (Schema::hasColumn('applicants', 'is_active') && !$this->hasIndex('applicants', 'idx_applicants_is_active')) {
            Schema::table('applicants', function (Blueprint $table) {
                $table->index('is_active', 'idx_applicants_is_active');
            });
        }
    }

    public function down(): void
    {
        $drops = [
            'court_cases'            => ['idx_court_cases_status', 'idx_court_cases_applicant_id', 'idx_court_cases_created_at'],
            'case_hearings'          => ['idx_case_hearings_case_hearing_at'],
            'case_messages'          => ['idx_case_messages_case_created'],
            'case_status_logs'       => ['idx_case_status_logs_case_created'],
            'letters'                => ['idx_letters_case_number', 'idx_letters_approval_status'],
            'respondent_responses'   => ['idx_respondent_responses_case_number', 'idx_respondent_responses_review_status'],
            'respondent_case_views'  => ['idx_respondent_case_views_respondent_id'],
            'applicants'             => ['idx_applicants_email', 'idx_applicants_is_active'],
        ];

        foreach ($drops as $table => $indexes) {
            if (!Schema::hasTable($table)) {
                continue;
            }
            foreach ($indexes as $index) {
                if ($this->hasIndex($table, $index)) {
                    Schema::table($table, fn(Blueprint $t) => $t->dropIndex($index));
                }
            }
        }
    }
};
