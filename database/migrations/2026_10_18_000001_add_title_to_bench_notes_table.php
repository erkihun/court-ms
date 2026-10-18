<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Bench notes have always required a title, but no migration created the
     * column, so the value was dropped. Additive and nullable: existing notes
     * keep working and show no title.
     */
    public function up(): void
    {
        if (! Schema::hasTable('bench_notes') || Schema::hasColumn('bench_notes', 'title')) {
            return;
        }

        Schema::table('bench_notes', function (Blueprint $table) {
            $table->string('title', 255)->nullable()->after('user_id');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('bench_notes') || ! Schema::hasColumn('bench_notes', 'title')) {
            return;
        }

        Schema::table('bench_notes', function (Blueprint $table) {
            $table->dropColumn('title');
        });
    }
};
