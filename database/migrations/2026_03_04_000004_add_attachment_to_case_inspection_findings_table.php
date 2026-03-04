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
            if (!Schema::hasColumn('case_inspection_findings', 'attachment_path')) {
                $table->string('attachment_path')->nullable()->after('recommendation');
            }
            if (!Schema::hasColumn('case_inspection_findings', 'attachment_original_name')) {
                $table->string('attachment_original_name')->nullable()->after('attachment_path');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('case_inspection_findings')) {
            return;
        }

        Schema::table('case_inspection_findings', function (Blueprint $table) {
            if (Schema::hasColumn('case_inspection_findings', 'attachment_original_name')) {
                $table->dropColumn('attachment_original_name');
            }
            if (Schema::hasColumn('case_inspection_findings', 'attachment_path')) {
                $table->dropColumn('attachment_path');
            }
        });
    }
};

