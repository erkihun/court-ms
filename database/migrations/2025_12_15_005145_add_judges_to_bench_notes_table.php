<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('bench_notes', function (Blueprint $table) {
            $table->foreignId('judge_one_id')->nullable()->after('user_id')->constrained('users')->nullOnDelete();
            $table->foreignId('judge_two_id')->nullable()->after('judge_one_id')->constrained('users')->nullOnDelete();
            $table->foreignId('judge_three_id')->nullable()->after('judge_two_id')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('bench_notes', function (Blueprint $table) {
            $table->dropForeign(['judge_three_id']);
            $table->dropForeign(['judge_two_id']);
            $table->dropForeign(['judge_one_id']);
            $table->dropColumn(['judge_one_id', 'judge_two_id', 'judge_three_id']);
        });
    }
};
