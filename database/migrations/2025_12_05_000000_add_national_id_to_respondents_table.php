<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('respondents', function (Blueprint $table) {
            if (!Schema::hasColumn('respondents', 'national_id')) {
                $table->string('national_id', 16)->nullable()->unique()->after('address');
            }
        });
    }

    public function down(): void
    {
        Schema::table('respondents', function (Blueprint $table) {
            if (Schema::hasColumn('respondents', 'national_id')) {
                $table->dropColumn('national_id');
            }
        });
    }
};
