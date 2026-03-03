<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('case_inspection_findings')) {
            return;
        }

        Schema::table('case_inspection_findings', function (Blueprint $table) {
            if (!Schema::hasColumn('case_inspection_findings', 'accepted_at')) {
                $table->timestamp('accepted_at')->nullable()->after('severity');
            }
            if (!Schema::hasColumn('case_inspection_findings', 'accepted_by_user_id')) {
                $table->foreignId('accepted_by_user_id')->nullable()->after('accepted_at')->constrained('users')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('case_inspection_findings')) {
            return;
        }

        Schema::table('case_inspection_findings', function (Blueprint $table) {
            if (Schema::hasColumn('case_inspection_findings', 'accepted_by_user_id')) {
                $table->dropConstrainedForeignId('accepted_by_user_id');
            }
            if (Schema::hasColumn('case_inspection_findings', 'accepted_at')) {
                $table->dropColumn('accepted_at');
            }
        });
    }
};
