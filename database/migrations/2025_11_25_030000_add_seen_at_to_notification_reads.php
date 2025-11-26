<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasColumn('notification_reads', 'seen_at')) {
            Schema::table('notification_reads', function (Blueprint $table) {
                $table->timestamp('seen_at')->nullable()->after('source_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('notification_reads', 'seen_at')) {
            Schema::table('notification_reads', function (Blueprint $table) {
                $table->dropColumn('seen_at');
            });
        }
    }
};
