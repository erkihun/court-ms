<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * A case with several parties on the applicant's side (EUISIS ADR 0013 #13):
 * the lead applicant, co-applicants who confirmed, interveners the judge
 * admitted, the lawyers who represent them, and cases merged into another.
 * `court_cases.applicant_id` stays the lead applicant; every existing case
 * gets its lead row. Additive only; down() removes exactly this.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('case_applicants')) {
            Schema::create('case_applicants', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('case_id');
                $table->unsignedBigInteger('applicant_id')->nullable();
                $table->uuid('euisis_employee_uuid')->nullable();
                $table->string('name');
                // lead, co_applicant or intervener.
                $table->string('role', 20);
                // active; invited, declined (co-applicants); requested, admitted, refused (interveners); removed.
                $table->string('status', 20);
                // filing, lawyer, registry, intervention, merge or backfill.
                $table->string('source', 20);
                $table->text('statement')->nullable();
                // An intervener's supporting PDF (private disk), seen by the judge.
                $table->string('attachment_path')->nullable();
                $table->unsignedBigInteger('invited_by_applicant_id')->nullable();
                $table->unsignedBigInteger('merged_from_case_id')->nullable();
                $table->unsignedBigInteger('decided_by_user_id')->nullable();
                $table->timestamp('decided_at')->nullable();
                $table->text('decision_note')->nullable();
                $table->timestamp('responded_at')->nullable();
                $table->timestamps();

                $table->foreign('case_id')->references('id')->on('court_cases')->cascadeOnDelete();
                $table->foreign('applicant_id')->references('id')->on('applicants')->nullOnDelete();
                $table->unique(['case_id', 'euisis_employee_uuid']);
                $table->index(['applicant_id', 'status']);
                $table->index(['euisis_employee_uuid', 'status']);
                $table->index(['case_id', 'role', 'status']);
            });

            // Every case so far has one applicant: its lead row.
            DB::table('case_applicants')->insertUsing(
                ['case_id', 'applicant_id', 'euisis_employee_uuid', 'name', 'role', 'status', 'source', 'created_at', 'updated_at'],
                DB::table('court_cases as c')
                    ->join('applicants as a', 'a.id', '=', 'c.applicant_id')
                    ->selectRaw("c.id, a.id, a.euisis_employee_uuid, COALESCE(NULLIF(TRIM(c.title), ''), TRIM(CONCAT_WS(' ', a.first_name, a.middle_name, a.last_name))), 'lead', 'active', 'backfill', NOW(), NOW()")
            );
        }

        if (! Schema::hasTable('case_representatives')) {
            Schema::create('case_representatives', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('case_id');
                $table->unsignedBigInteger('case_applicant_id');
                $table->unsignedBigInteger('lawyer_applicant_id');
                $table->string('power_of_attorney_path')->nullable();
                // pending (the registry checks the power of attorney), accepted, refused or revoked.
                $table->string('status', 20)->default('pending');
                // Whether the represented employee confirmed (a lawyer's own filing needs it).
                $table->timestamp('confirmed_by_party_at')->nullable();
                $table->unsignedBigInteger('verified_by_user_id')->nullable();
                $table->timestamp('verified_at')->nullable();
                $table->text('note')->nullable();
                $table->timestamps();

                $table->foreign('case_id')->references('id')->on('court_cases')->cascadeOnDelete();
                $table->foreign('case_applicant_id')->references('id')->on('case_applicants')->cascadeOnDelete();
                $table->foreign('lawyer_applicant_id')->references('id')->on('applicants')->cascadeOnDelete();
                $table->unique(['case_applicant_id', 'lawyer_applicant_id'], 'case_representatives_party_lawyer_unique');
                $table->index(['lawyer_applicant_id', 'status']);
            });
        }

        Schema::table('court_cases', function (Blueprint $table): void {
            if (! Schema::hasColumn('court_cases', 'merged_into_case_id')) {
                $table->unsignedBigInteger('merged_into_case_id')->nullable()->index();
                $table->timestamp('merged_at')->nullable();
                $table->unsignedBigInteger('merged_by_user_id')->nullable();
                $table->text('merge_note')->nullable();
            }
        });

        // Merging cases is its own permission, for the roles of judges and registrars (Roles screen).
        if (Schema::hasTable('permissions') && DB::table('permissions')->where('name', 'cases.merge')->doesntExist()) {
            DB::table('permissions')->insert(['name' => 'cases.merge', 'label' => 'Merge cases', 'description' => 'Merge open cases against the same respondent into one', 'created_at' => now(), 'updated_at' => now()]);
        }

        Schema::table('applicants', function (Blueprint $table): void {
            if (! Schema::hasColumn('applicants', 'lawyer_license_number')) {
                $table->string('lawyer_license_number', 80)->nullable();
                $table->timestamp('lawyer_verified_at')->nullable();
                $table->unsignedBigInteger('lawyer_verified_by_user_id')->nullable();
            }
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('permissions')) {
            $id = DB::table('permissions')->where('name', 'cases.merge')->value('id');
            if ($id !== null) {
                if (Schema::hasTable('permission_role')) {
                    DB::table('permission_role')->where('permission_id', $id)->delete();
                }
                DB::table('permissions')->where('id', $id)->delete();
            }
        }
        Schema::table('applicants', function (Blueprint $table): void {
            if (Schema::hasColumn('applicants', 'lawyer_license_number')) {
                $table->dropColumn(['lawyer_license_number', 'lawyer_verified_at', 'lawyer_verified_by_user_id']);
            }
        });
        Schema::table('court_cases', function (Blueprint $table): void {
            if (Schema::hasColumn('court_cases', 'merged_into_case_id')) {
                $table->dropIndex(['merged_into_case_id']);
                $table->dropColumn(['merged_into_case_id', 'merged_at', 'merged_by_user_id', 'merge_note']);
            }
        });
        Schema::dropIfExists('case_representatives');
        Schema::dropIfExists('case_applicants');
    }
};
