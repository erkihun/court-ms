<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add an optional note date (the date the bench note refers to) to bench_notes.
     */
    public function up(): void
    {
        if (! Schema::hasTable('bench_notes') || Schema::hasColumn('bench_notes', 'note_date')) {
            return;
        }

        Schema::table('bench_notes', function (Blueprint $table) {
            $table->date('note_date')->nullable();
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('bench_notes') || ! Schema::hasColumn('bench_notes', 'note_date')) {
            return;
        }

        Schema::table('bench_notes', function (Blueprint $table) {
            $table->dropColumn('note_date');
        });
    }
};
