<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/*
| Court staff are EUISIS employees of the court's own unit (docs/euisis-integration.md,
| "Staff accounts"). A staff account is linked by the employee's UUID; the units the
| court counts as its own are chosen from the mirrored EUISIS units; a link that no
| longer holds suspends the account (never deletes it) and leaves a notice for the
| administrators. Additive and nullable; each part is skipped when present.
*/
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'euisis_employee_uuid')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->uuid('euisis_employee_uuid')->nullable()->unique()->after('status');
                $table->timestamp('euisis_linked_at')->nullable()->after('euisis_employee_uuid');
                // Set only when the EUISIS check suspended the account, so only those are lifted.
                $table->timestamp('euisis_suspended_at')->nullable()->after('euisis_linked_at');
                $table->string('euisis_suspension_reason', 40)->nullable()->after('euisis_suspended_at');
            });
        }

        if (! Schema::hasTable('euisis_staff_units')) {
            Schema::create('euisis_staff_units', function (Blueprint $table): void {
                $table->uuid('unit_uuid')->primary();
                $table->uuid('organization_uuid')->index();
                $table->boolean('include_sub_units')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('euisis_staff_notices')) {
            Schema::create('euisis_staff_notices', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->string('event', 20); // suspended | restored
                $table->string('reason', 40)->nullable();
                $table->timestamps();
                $table->index('created_at');
            });
        }

        // The administrators' bell marks these notices seen like its other kinds.
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE admin_notification_reads CHANGE COLUMN `type` `type` ENUM('message','case','hearing','respondent_view','staff_account') NOT NULL");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            // Only the "seen" marks of the notices this migration's table held.
            DB::table('admin_notification_reads')->where('type', 'staff_account')->delete();
            DB::statement("ALTER TABLE admin_notification_reads CHANGE COLUMN `type` `type` ENUM('message','case','hearing','respondent_view') NOT NULL");
        }
        Schema::dropIfExists('euisis_staff_notices');
        Schema::dropIfExists('euisis_staff_units');

        if (Schema::hasColumn('users', 'euisis_employee_uuid')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->dropUnique(['euisis_employee_uuid']);
                $table->dropColumn(['euisis_employee_uuid', 'euisis_linked_at', 'euisis_suspended_at', 'euisis_suspension_reason']);
            });
        }
    }
};
