<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('court_cases', function (Blueprint $table) {
            if (!Schema::hasColumn('court_cases', 'respondent_name')) {
                $table->string('respondent_name')->nullable()->after('title');
            }
            if (!Schema::hasColumn('court_cases', 'respondent_address')) {
                $table->string('respondent_address')->nullable()->after('respondent_name');
            }
            if (!Schema::hasColumn('court_cases', 'relief_requested')) {
                $table->text('relief_requested')->nullable()->after('description');
            }
            // You already have 'description' – we’ll use it as case details
        });
    }

    public function down(): void
    {
        Schema::table('court_cases', function (Blueprint $table) {
            foreach (['respondent_name', 'respondent_address', 'relief_requested'] as $col) {
                if (Schema::hasColumn('court_cases', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
