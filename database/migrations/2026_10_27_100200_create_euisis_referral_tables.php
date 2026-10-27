<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Inbound EUISIS integration (docs/euisis-integration.md, Flows 2 and 3).
 *
 * integration_clients  machine credentials for the /api/v1/integration routes;
 *                      only a SHA-256 hash of the token is stored, and these
 *                      tokens are never accepted by the person-facing API
 * euisis_referrals     one row per grievance EUISIS referred, with what was sent
 * euisis_case_updates  the change list EUISIS polls; its id is the cursor
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('integration_clients', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 80)->unique();
            $table->char('token_hash', 64)->unique();
            $table->json('abilities');
            $table->json('allowed_ips')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->string('last_used_ip', 45)->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();
        });

        Schema::create('euisis_referrals', function (Blueprint $table): void {
            $table->id();
            $table->uuid('referral_uuid')->unique();
            $table->foreignId('court_case_id')->constrained('court_cases')->cascadeOnDelete();
            $table->foreignId('integration_client_id')->nullable()->constrained('integration_clients')->nullOnDelete();
            $table->string('grievance_reference', 80)->nullable();
            $table->string('tribunal_case_number', 80)->nullable();
            $table->string('complainant_name')->nullable();
            $table->uuid('complainant_employee_uuid')->nullable();
            $table->string('complainant_phone', 40)->nullable();
            $table->string('complainant_email')->nullable();
            $table->uuid('respondent_organization_uuid')->nullable();
            $table->string('referring_officer')->nullable();
            $table->json('payload');
            // What the last update row reported, to detect the next change.
            $table->json('last_reported_state')->nullable();
            $table->timestamps();
        });

        Schema::create('euisis_case_updates', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('euisis_referral_id')->constrained('euisis_referrals')->cascadeOnDelete();
            $table->foreignId('court_case_id')->constrained('court_cases')->cascadeOnDelete();
            $table->string('case_number', 80);
            $table->string('review_status', 30)->nullable();
            $table->text('review_reason')->nullable();
            $table->string('status', 30)->nullable();
            $table->dateTime('next_hearing_at')->nullable();
            $table->date('decision_date')->nullable();
            $table->text('decision_summary')->nullable();
            $table->json('changed');
            $table->timestamp('created_at')->nullable();
            $table->index(['court_case_id', 'id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('euisis_case_updates');
        Schema::dropIfExists('euisis_referrals');
        Schema::dropIfExists('integration_clients');
    }
};
