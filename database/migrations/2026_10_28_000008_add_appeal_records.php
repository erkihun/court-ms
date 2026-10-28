<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/*
| Appeals to the appellate court (EUISIS ADR 0013 #14). A party to a closed case
| asks for the case record to take to the appellate court; the registry releases
| it, which marks the case as appealed; the appellate court's outcome is recorded
| and scores the judges whose decision it was. The EUISIS record a case started
| from (a grievance, or now a discipline case) travels with the record.
| Additive only: every column is nullable or defaulted, and down() drops them.
*/
return new class extends Migration
{
    private const APPEAL_COLUMNS = [
        'appellant_type', 'appellant_name', 'requested_via', 'requester_reference', 'decision_id', 'judge_user_ids',
        'released_at', 'released_by_user_id', 'appellate_court', 'appellate_case_number', 'outcome', 'outcome_date',
        'outcome_notes', 'outcome_document_path', 'outcome_recorded_by_user_id', 'outcome_recorded_at',
    ];

    public function up(): void
    {
        if (! Schema::hasColumn('appeals', 'appellant_type')) {
            Schema::table('appeals', function (Blueprint $table): void {
                // Who appeals: the applicant's side or the respondent institution, and how the request came.
                $table->string('appellant_type', 20)->nullable()->after('applicant_id');
                $table->string('appellant_name')->nullable()->after('appellant_type');
                $table->string('requested_via', 30)->nullable()->after('appellant_name');
                $table->string('requester_reference', 64)->nullable()->after('requested_via');
                // The decision appealed and the panel of judges who gave it (kept as at the request).
                $table->unsignedBigInteger('decision_id')->nullable()->after('requester_reference');
                $table->json('judge_user_ids')->nullable()->after('decision_id');
                $table->timestamp('released_at')->nullable();
                $table->unsignedBigInteger('released_by_user_id')->nullable();
                $table->string('appellate_court')->nullable();
                $table->string('appellate_case_number', 120)->nullable();
                $table->string('outcome', 20)->nullable();
                $table->date('outcome_date')->nullable();
                $table->text('outcome_notes')->nullable();
                $table->string('outcome_document_path')->nullable();
                $table->unsignedBigInteger('outcome_recorded_by_user_id')->nullable();
                $table->timestamp('outcome_recorded_at')->nullable();

                $table->index(['court_case_id', 'status']);
                $table->index('outcome_date');
                $table->foreign('released_by_user_id')->references('id')->on('users')->nullOnDelete();
                $table->foreign('outcome_recorded_by_user_id')->references('id')->on('users')->nullOnDelete();
            });
        }

        if (! Schema::hasColumn('court_cases', 'appealed_at')) {
            Schema::table('court_cases', function (Blueprint $table): void {
                // The record went to the appellate court (released), and what that court decided.
                $table->timestamp('appealed_at')->nullable();
                $table->string('appeal_outcome', 20)->nullable();
            });
        }

        if (! Schema::hasColumn('case_euisis_grievances', 'kind')) {
            Schema::table('case_euisis_grievances', function (Blueprint $table): void {
                // grievance, or discipline: the EUISIS proceeding the case started from.
                $table->string('kind', 20)->default('grievance')->after('court_case_id');
            });
        }

        if (! Schema::hasColumn('performance_evaluation_criteria', 'source')) {
            Schema::table('performance_evaluation_criteria', function (Blueprint $table): void {
                // null: scored by the evaluator; appeal_outcomes: worked out from the appellate court's outcomes.
                $table->string('source', 40)->nullable()->after('category');
            });
        }
        if (! DB::table('performance_evaluation_criteria')->where('source', 'appeal_outcomes')->exists()) {
            DB::table('performance_evaluation_criteria')->insert([
                'name' => 'Decisions upheld on appeal',
                'name_am' => 'ይግባኝ ላይ የጸኑ ውሳኔዎች',
                'category' => 'quality',
                'source' => 'appeal_outcomes',
                'weight' => 15,
                'description' => 'Worked out from the appellate court\'s outcomes in the period: a decision confirmed scores 10, modified 5, reversed 0. Left out when no appeal was decided.',
                'active' => true,
                'sort_order' => (int) DB::table('performance_evaluation_criteria')->max('sort_order') + 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('performance_evaluation_criteria')->where('source', 'appeal_outcomes')->delete();
        if (Schema::hasColumn('performance_evaluation_criteria', 'source')) {
            Schema::table('performance_evaluation_criteria', fn (Blueprint $table) => $table->dropColumn('source'));
        }
        if (Schema::hasColumn('case_euisis_grievances', 'kind')) {
            Schema::table('case_euisis_grievances', fn (Blueprint $table) => $table->dropColumn('kind'));
        }
        if (Schema::hasColumn('court_cases', 'appealed_at')) {
            Schema::table('court_cases', fn (Blueprint $table) => $table->dropColumn(['appealed_at', 'appeal_outcome']));
        }
        if (Schema::hasColumn('appeals', 'appellant_type')) {
            Schema::table('appeals', function (Blueprint $table): void {
                $table->dropForeign(['released_by_user_id']);
                $table->dropForeign(['outcome_recorded_by_user_id']);
                $table->dropIndex(['court_case_id', 'status']);
                $table->dropIndex(['outcome_date']);
                $table->dropColumn(self::APPEAL_COLUMNS);
            });
        }
    }
};
