<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('bench_notes')) {
            return;
        }

        Schema::table('bench_notes', function (Blueprint $table) {
            if (!Schema::hasColumn('bench_notes', 'judge_one_id')) {
                $table->foreignId('judge_one_id')->nullable()->constrained('users')->nullOnDelete()->after('user_id');
            }
            if (!Schema::hasColumn('bench_notes', 'judge_two_id')) {
                $table->foreignId('judge_two_id')->nullable()->constrained('users')->nullOnDelete()->after('judge_one_id');
            }
            if (!Schema::hasColumn('bench_notes', 'judge_three_id')) {
                $table->foreignId('judge_three_id')->nullable()->constrained('users')->nullOnDelete()->after('judge_two_id');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('bench_notes')) {
            return;
        }

        Schema::table('bench_notes', function (Blueprint $table) {
            if (Schema::hasColumn('bench_notes', 'judge_three_id')) {
                $table->dropForeign(['judge_three_id']);
                $table->dropColumn('judge_three_id');
            }
            if (Schema::hasColumn('bench_notes', 'judge_two_id')) {
                $table->dropForeign(['judge_two_id']);
                $table->dropColumn('judge_two_id');
            }
            if (Schema::hasColumn('bench_notes', 'judge_one_id')) {
                $table->dropForeign(['judge_one_id']);
                $table->dropColumn('judge_one_id');
            }
        });
    }
};
