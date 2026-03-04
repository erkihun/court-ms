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
            if (Schema::hasColumn('case_inspection_findings', 'recommendation')) {
                $table->dropColumn('recommendation');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('case_inspection_findings')) {
            return;
        }

        Schema::table('case_inspection_findings', function (Blueprint $table) {
            if (!Schema::hasColumn('case_inspection_findings', 'recommendation')) {
                $table->longText('recommendation')->nullable()->after('details');
            }
        });
    }
};

