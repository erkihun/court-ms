<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
| Flow 5 (docs/euisis-integration.md): employees file cases from the EUISIS employee
| portal. A filing is idempotent on its EUISIS id; the grievance record sent with it
| (the grievances, their responses and decisions) is kept with the case; and every
| letter approved on a case against an EUISIS institution is queued for EUISIS to
| deliver to that institution. Additive; each step is skipped when already present.
*/
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('court_cases', 'euisis_filing_id')) {
            Schema::table('court_cases', function (Blueprint $table): void {
                $table->uuid('euisis_filing_id')->nullable()->unique();
            });
        }

        if (! Schema::hasTable('case_euisis_grievances')) {
            Schema::create('case_euisis_grievances', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('court_case_id')->constrained('court_cases')->cascadeOnDelete();
                $table->uuid('grievance_uuid');
                $table->string('reference', 80)->nullable();
                $table->string('subject', 500);
                $table->string('category', 255)->nullable();
                $table->string('status', 60)->nullable();
                $table->text('description')->nullable();
                $table->timestamp('submitted_at')->nullable();
                $table->timestamp('closed_at')->nullable();
                $table->json('responses')->nullable();
                $table->json('decisions')->nullable();
                $table->timestamps();
                $table->unique(['court_case_id', 'grievance_uuid']);
            });
        }

        if (! Schema::hasTable('euisis_letter_deliveries')) {
            Schema::create('euisis_letter_deliveries', function (Blueprint $table): void {
                // The id is the cursor EUISIS reads the queue with.
                $table->id();
                $table->foreignId('letter_id')->unique()->constrained('letters')->cascadeOnDelete();
                $table->foreignId('court_case_id')->nullable()->constrained('court_cases')->nullOnDelete();
                $table->uuid('organization_uuid')->index();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('euisis_letter_deliveries');
        Schema::dropIfExists('case_euisis_grievances');
        if (Schema::hasColumn('court_cases', 'euisis_filing_id')) {
            Schema::table('court_cases', function (Blueprint $table): void {
                $table->dropUnique(['euisis_filing_id']);
                $table->dropColumn('euisis_filing_id');
            });
        }
    }
};
