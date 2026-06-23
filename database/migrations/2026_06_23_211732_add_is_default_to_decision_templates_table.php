<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('decision_templates', function (Blueprint $table) {
            if (! Schema::hasColumn('decision_templates', 'is_default')) {
                $table->boolean('is_default')->default(false)->after('category');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('decision_templates', function (Blueprint $table) {
            if (Schema::hasColumn('decision_templates', 'is_default')) {
                $table->dropColumn('is_default');
            }
        });
    }
};
