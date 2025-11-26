<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('court_cases', function (Blueprint $table) {
            if (!Schema::hasColumn('court_cases', 'assigned_user_id')) {
                $table->unsignedBigInteger('assigned_user_id')->nullable()->after('status');
                $table->timestamp('assigned_at')->nullable()->after('assigned_user_id');

                // Foreign key (works on MySQL; on SQLite in tests, it’s fine too if FKs enabled)
                $table->foreign('assigned_user_id')->references('id')->on('users')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('court_cases', function (Blueprint $table) {
            if (Schema::hasColumn('court_cases', 'assigned_user_id')) {
                $table->dropForeign(['assigned_user_id']);
                $table->dropColumn(['assigned_user_id', 'assigned_at']);
            }
        });
    }
};
