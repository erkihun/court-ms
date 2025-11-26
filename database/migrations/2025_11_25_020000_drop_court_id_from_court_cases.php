<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasColumn('court_cases', 'court_id')) {
            Schema::table('court_cases', function (Blueprint $table) {
                try {
                    $table->dropForeign(['court_id']);
                } catch (\Throwable $e) {
                    // ignore if FK already missing
                }

                $table->dropColumn('court_id');
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasColumn('court_cases', 'court_id')) {
            Schema::table('court_cases', function (Blueprint $table) {
                $table->foreignId('court_id')->nullable()->constrained()->after('case_type_id');
            });
        }
    }
};
