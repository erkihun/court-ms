<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * EUISIS references beside the existing free-text fields
 * (docs/euisis-integration.md, "Identifiers"). All nullable: existing rows,
 * forms and reports keep working; organization_name, position and
 * respondent_name stay the text recorded on the case.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('respondents', function (Blueprint $table): void {
            $table->uuid('euisis_organization_uuid')->nullable()->index();
            $table->uuid('euisis_employee_uuid')->nullable()->index();
        });

        Schema::table('applicants', function (Blueprint $table): void {
            $table->uuid('euisis_employee_uuid')->nullable()->index();
        });

        Schema::table('court_cases', function (Blueprint $table): void {
            // 'euisis' for a case opened from an EUISIS tribunal referral; null for every other case.
            $table->string('source', 20)->nullable()->index();
            // The EUISIS grievance UUID: the referral's idempotency key.
            $table->uuid('euisis_referral_id')->nullable()->unique();
            $table->uuid('respondent_organization_uuid')->nullable()->index();
        });
    }

    public function down(): void
    {
        Schema::table('court_cases', function (Blueprint $table): void {
            $table->dropUnique(['euisis_referral_id']);
            $table->dropIndex(['source']);
            $table->dropIndex(['respondent_organization_uuid']);
            $table->dropColumn(['source', 'euisis_referral_id', 'respondent_organization_uuid']);
        });

        Schema::table('applicants', function (Blueprint $table): void {
            $table->dropIndex(['euisis_employee_uuid']);
            $table->dropColumn('euisis_employee_uuid');
        });

        Schema::table('respondents', function (Blueprint $table): void {
            $table->dropIndex(['euisis_organization_uuid']);
            $table->dropIndex(['euisis_employee_uuid']);
            $table->dropColumn(['euisis_organization_uuid', 'euisis_employee_uuid']);
        });
    }
};
