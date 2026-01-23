<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('court_cases')) {
            return;
        }

        if (!Schema::hasColumn('court_cases', 'applicant_id')) {
            return;
        }

        $constraint = 'court_cases_applicant_id_foreign';
        $exists = DB::selectOne(
            'select constraint_name from information_schema.table_constraints where constraint_schema = database() and table_name = ? and constraint_name = ?',
            ['court_cases', $constraint]
        );

        Schema::table('court_cases', function (Blueprint $table) use ($constraint, $exists) {
            if ($exists) {
                $table->dropForeign($constraint);
            }

            if (!$exists) {
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

        if (!Schema::hasColumn('court_cases', 'applicant_id')) {
            return;
        }

        $constraint = 'court_cases_applicant_id_foreign';
        $exists = DB::selectOne(
            'select constraint_name from information_schema.table_constraints where constraint_schema = database() and table_name = ? and constraint_name = ?',
            ['court_cases', $constraint]
        );

        Schema::table('court_cases', function (Blueprint $table) use ($constraint, $exists) {
            if ($exists) {
                $table->dropForeign($constraint);
            }
        });
    }
};
