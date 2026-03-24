<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('respondent_responses')) {
            return;
        }

        Schema::table('respondent_responses', function (Blueprint $table) {
            if (!Schema::hasColumn('respondent_responses', 'response_number')) {
                $table->string('response_number', 120)->nullable()->after('case_number');
            }
        });

        $this->backfillResponseNumbers();

        Schema::table('respondent_responses', function (Blueprint $table) {
            $table->unique('response_number', 'respondent_responses_response_number_unique');
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('respondent_responses')) {
            return;
        }

        Schema::table('respondent_responses', function (Blueprint $table) {
            if (Schema::hasColumn('respondent_responses', 'response_number')) {
                $table->dropUnique('respondent_responses_response_number_unique');
                $table->dropColumn('response_number');
            }
        });
    }

    private function backfillResponseNumbers(): void
    {
        if (!Schema::hasColumn('respondent_responses', 'response_number')) {
            return;
        }

        $countsByCase = [];

        DB::table('respondent_responses')
            ->select(['id', 'case_number'])
            ->orderBy('id')
            ->chunkById(500, function ($rows) use (&$countsByCase): void {
                foreach ($rows as $row) {
                    $caseNumber = trim((string) ($row->case_number ?? ''));
                    if ($caseNumber === '') {
                        continue;
                    }

                    $sequence = $countsByCase[$caseNumber] ?? 0;
                    $suffix = $sequence === 0
                        ? ''
                        : '/' . str_pad((string) $sequence, 2, '0', STR_PAD_LEFT);

                    DB::table('respondent_responses')
                        ->where('id', $row->id)
                        ->update(['response_number' => "\u{1218}/{$caseNumber}{$suffix}"]);

                    $countsByCase[$caseNumber] = $sequence + 1;
                }
            }, 'id');
    }
};
