<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Widen bench_notes.note from TEXT (~64 KB) to LONGTEXT (~4 GB) so long
     * bench notes are no longer constrained by the column size.
     */
    public function up(): void
    {
        if (! Schema::hasTable('bench_notes') || ! Schema::hasColumn('bench_notes', 'note')) {
            return;
        }

        DB::statement('ALTER TABLE `bench_notes` MODIFY `note` LONGTEXT NOT NULL');
    }

    /**
     * Revert to TEXT. Note: this is only safe while existing notes fit within
     * the TEXT limit (~65,535 bytes); MySQL will error rather than truncate if
     * any row exceeds it.
     */
    public function down(): void
    {
        if (! Schema::hasTable('bench_notes') || ! Schema::hasColumn('bench_notes', 'note')) {
            return;
        }

        DB::statement('ALTER TABLE `bench_notes` MODIFY `note` TEXT NOT NULL');
    }
};
