<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('permissions', function (Blueprint $table) {
            if (!Schema::hasColumn('permissions', 'label_translations')) {
                $table->json('label_translations')->nullable()->after('label');
            }
            if (!Schema::hasColumn('permissions', 'description_translations')) {
                $table->json('description_translations')->nullable()->after('description');
            }
        });
    }

    public function down(): void
    {
        Schema::table('permissions', function (Blueprint $table) {
            if (Schema::hasColumn('permissions', 'label_translations')) {
                $table->dropColumn('label_translations');
            }
            if (Schema::hasColumn('permissions', 'description_translations')) {
                $table->dropColumn('description_translations');
            }
        });
    }
};
