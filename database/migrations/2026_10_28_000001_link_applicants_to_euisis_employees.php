<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
| An applicant who is an EUISIS employee is linked by the employee's UUID, so the
| EUISIS employee portal finds the same court account every time (ADR 0013,
| docs/euisis-integration.md, Flow 4). Additive and nullable; skipped when present.
*/
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('applicants', 'euisis_employee_uuid')) {
            return;
        }

        Schema::table('applicants', function (Blueprint $table): void {
            $table->uuid('euisis_employee_uuid')->nullable()->unique()->after('national_id_number');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('applicants', 'euisis_employee_uuid')) {
            return;
        }

        Schema::table('applicants', function (Blueprint $table): void {
            $table->dropUnique(['euisis_employee_uuid']);
            $table->dropColumn('euisis_employee_uuid');
        });
    }
};
