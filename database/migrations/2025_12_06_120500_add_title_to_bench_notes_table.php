<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('bench_notes')) {
            return;
        }

        Schema::table('bench_notes', function (Blueprint $table) {
            if (!Schema::hasColumn('bench_notes', 'title')) {
                $table->string('title', 255)->after('user_id')->default('');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('bench_notes')) {
            return;
        }

        Schema::table('bench_notes', function (Blueprint $table) {
            if (Schema::hasColumn('bench_notes', 'title')) {
                $table->dropColumn('title');
            }
        });
    }
};
