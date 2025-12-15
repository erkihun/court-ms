<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('case_types')) {
            return;
        }

        Schema::table('case_types', function (Blueprint $table) {
            if (!Schema::hasColumn('case_types', 'prifix')) {
                $table->string('prifix', 100)->nullable()->unique()->after('name');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('case_types')) {
            return;
        }

        Schema::table('case_types', function (Blueprint $table) {
            if (Schema::hasColumn('case_types', 'prifix')) {
                $table->dropUnique('case_types_prifix_unique');
                $table->dropColumn('prifix');
            }
        });
    }
};
