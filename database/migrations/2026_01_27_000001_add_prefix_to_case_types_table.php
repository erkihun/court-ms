<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('case_types') && !Schema::hasColumn('case_types', 'prefix')) {
            Schema::table('case_types', function (Blueprint $table) {
                $table->string('prefix', 16)->nullable()->after('name');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('case_types') && Schema::hasColumn('case_types', 'prefix')) {
            Schema::table('case_types', function (Blueprint $table) {
                $table->dropColumn('prefix');
            });
        }
    }
};
