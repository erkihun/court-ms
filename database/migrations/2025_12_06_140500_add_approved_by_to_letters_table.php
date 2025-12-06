<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('letters', function (Blueprint $table) {
            if (!Schema::hasColumn('letters', 'approved_by_name')) {
                $table->string('approved_by_name')->nullable()->after('cc');
            }
            if (!Schema::hasColumn('letters', 'approved_by_title')) {
                $table->string('approved_by_title')->nullable()->after('approved_by_name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('letters', function (Blueprint $table) {
            if (Schema::hasColumn('letters', 'approved_by_title')) {
                $table->dropColumn('approved_by_title');
            }
            if (Schema::hasColumn('letters', 'approved_by_name')) {
                $table->dropColumn('approved_by_name');
            }
        });
    }
};
