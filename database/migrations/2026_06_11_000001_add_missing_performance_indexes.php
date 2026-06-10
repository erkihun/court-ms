<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private function hasIndex(string $table, string $index): bool
    {
        return (bool) DB::selectOne(
            "SELECT COUNT(1) as cnt
             FROM information_schema.STATISTICS
             WHERE table_schema = DATABASE()
               AND table_name   = ?
               AND index_name   = ?",
            [$table, $index]
        )?->cnt;
    }

    public function up(): void
    {
        // court_cases: assigned_user_id (used in WHERE / JOIN on nearly every case query)
        if (!$this->hasIndex('court_cases', 'idx_court_cases_assigned_user_id')) {
            Schema::table('court_cases', function (Blueprint $table) {
                $table->index('assigned_user_id', 'idx_court_cases_assigned_user_id');
            });
        }

        // court_cases: assigned_member_user_id (used in OR-JOIN for member case counts)
        if (!$this->hasIndex('court_cases', 'idx_court_cases_assigned_member_user_id')) {
            Schema::table('court_cases', function (Blueprint $table) {
                $table->index('assigned_member_user_id', 'idx_court_cases_assigned_member_user_id');
            });
        }

        // court_cases: assigned_team_id (dashboard team aggregation)
        if (!$this->hasIndex('court_cases', 'idx_court_cases_assigned_team_id')) {
            Schema::table('court_cases', function (Blueprint $table) {
                $table->index('assigned_team_id', 'idx_court_cases_assigned_team_id');
            });
        }

        // court_cases: case_type_id (type breakdown joins)
        if (!$this->hasIndex('court_cases', 'idx_court_cases_case_type_id')) {
            Schema::table('court_cases', function (Blueprint $table) {
                $table->index('case_type_id', 'idx_court_cases_case_type_id');
            });
        }

        // court_cases: composite (status, assigned_user_id) — notification + pending unassigned query
        if (!$this->hasIndex('court_cases', 'idx_court_cases_status_assigned')) {
            Schema::table('court_cases', function (Blueprint $table) {
                $table->index(['status', 'assigned_user_id'], 'idx_court_cases_status_assigned');
            });
        }

        // users: status (active user count query)
        if (!$this->hasIndex('users', 'idx_users_status')) {
            Schema::table('users', function (Blueprint $table) {
                $table->index('status', 'idx_users_status');
            });
        }

        // team_user: user_id (membership existence check + member joins)
        if (!$this->hasIndex('team_user', 'idx_team_user_user_id')) {
            Schema::table('team_user', function (Blueprint $table) {
                $table->index('user_id', 'idx_team_user_user_id');
            });
        }

        // team_user: team_id (team member lookup)
        if (!$this->hasIndex('team_user', 'idx_team_user_team_id')) {
            Schema::table('team_user', function (Blueprint $table) {
                $table->index('team_id', 'idx_team_user_team_id');
            });
        }

        // admin_notification_reads: composite (user_id, type, source_id) — whereNotExists subqueries
        if (!$this->hasIndex('admin_notification_reads', 'idx_admin_notif_reads_user_type_source')) {
            Schema::table('admin_notification_reads', function (Blueprint $table) {
                $table->index(['user_id', 'type', 'source_id'], 'idx_admin_notif_reads_user_type_source');
            });
        }

        // notification_reads: composite (applicant_id, type, source_id) — applicant portal whereNotExists
        if (Schema::hasTable('notification_reads') && !$this->hasIndex('notification_reads', 'idx_notif_reads_applicant_type_source')) {
            Schema::table('notification_reads', function (Blueprint $table) {
                $table->index(['applicant_id', 'type', 'source_id'], 'idx_notif_reads_applicant_type_source');
            });
        }

        // respondent_case_views: composite (case_id, viewed_at) — notification query
        if (Schema::hasTable('respondent_case_views') && !$this->hasIndex('respondent_case_views', 'idx_respondent_views_case_viewed')) {
            Schema::table('respondent_case_views', function (Blueprint $table) {
                $table->index(['case_id', 'viewed_at'], 'idx_respondent_views_case_viewed');
            });
        }

        // case_status_logs: case_id (status log joins — if table exists)
        if (Schema::hasTable('case_status_logs') && !$this->hasIndex('case_status_logs', 'idx_case_status_logs_case_id')) {
            Schema::table('case_status_logs', function (Blueprint $table) {
                $table->index('case_id', 'idx_case_status_logs_case_id');
            });
        }
    }

    public function down(): void
    {
        $drops = [
            'court_cases'            => [
                'idx_court_cases_assigned_user_id',
                'idx_court_cases_assigned_member_user_id',
                'idx_court_cases_assigned_team_id',
                'idx_court_cases_case_type_id',
                'idx_court_cases_status_assigned',
            ],
            'users'                  => ['idx_users_status'],
            'team_user'              => ['idx_team_user_user_id', 'idx_team_user_team_id'],
            'admin_notification_reads' => ['idx_admin_notif_reads_user_type_source'],
            'notification_reads'     => ['idx_notif_reads_applicant_type_source'],
            'respondent_case_views'  => ['idx_respondent_views_case_viewed'],
            'case_status_logs'       => ['idx_case_status_logs_case_id'],
        ];

        foreach ($drops as $table => $indexes) {
            if (!Schema::hasTable($table)) continue;
            foreach ($indexes as $index) {
                if ($this->hasIndex($table, $index)) {
                    Schema::table($table, fn(Blueprint $t) => $t->dropIndex($index));
                }
            }
        }
    }
};
