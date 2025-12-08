<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('letters', function (Blueprint $table) {
            if (!Schema::hasColumn('letters', 'case_number')) {
                $table->string('case_number', 60)->nullable()->after('reference_number');
                $table->index('case_number');
            }
        });
    }

    public function down(): void
    {
        Schema::table('letters', function (Blueprint $table) {
            if (Schema::hasColumn('letters', 'case_number')) {
                $table->dropIndex(['case_number']);
                $table->dropColumn('case_number');
            }
        });
    }
};
