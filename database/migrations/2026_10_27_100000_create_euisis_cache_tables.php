<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Local cache of EUISIS master data (docs/euisis-integration.md, Flow 1).
 * EUISIS owns these records; court-ms only mirrors what it needs and keeps
 * them current from the EUISIS change feed. Keys are EUISIS UUIDs.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('euisis_organizations', function (Blueprint $table): void {
            $table->uuid('uuid')->primary();
            $table->string('code', 60)->nullable();
            $table->string('name_en');
            $table->string('name_am')->nullable();
            $table->string('status', 40)->nullable()->index();
            $table->unsignedInteger('last_sequence')->default(0);
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();
        });

        Schema::create('euisis_organization_units', function (Blueprint $table): void {
            $table->uuid('uuid')->primary();
            $table->uuid('organization_uuid')->index();
            $table->uuid('parent_unit_uuid')->nullable();
            $table->string('code', 60)->nullable();
            $table->string('name_en');
            $table->string('name_am')->nullable();
            $table->string('status', 40)->nullable();
            $table->unsignedInteger('last_sequence')->default(0);
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();
        });

        Schema::create('euisis_positions', function (Blueprint $table): void {
            $table->uuid('uuid')->primary();
            $table->uuid('organization_uuid')->index();
            $table->uuid('unit_uuid')->nullable();
            $table->string('code', 60)->nullable();
            $table->string('title_en');
            $table->string('title_am')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('last_sequence')->default(0);
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();
        });

        // Only employees who are parties to a case are cached, never the whole workforce.
        Schema::create('euisis_employees', function (Blueprint $table): void {
            $table->uuid('uuid')->primary();
            $table->string('employee_number', 60)->nullable()->index();
            $table->string('full_name')->nullable();
            $table->string('full_name_en')->nullable();
            $table->string('status', 40)->nullable();
            $table->uuid('organization_uuid')->nullable();
            $table->uuid('unit_uuid')->nullable();
            $table->uuid('position_uuid')->nullable();
            $table->uuid('assignment_uuid')->nullable();
            $table->unsignedInteger('last_sequence')->default(0);
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();
        });

        Schema::create('euisis_sync_state', function (Blueprint $table): void {
            $table->string('key', 60)->primary();
            $table->text('value')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('euisis_sync_state');
        Schema::dropIfExists('euisis_employees');
        Schema::dropIfExists('euisis_positions');
        Schema::dropIfExists('euisis_organization_units');
        Schema::dropIfExists('euisis_organizations');
    }
};
