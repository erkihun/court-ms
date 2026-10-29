<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The staff side menu opens collapsed by default (System Settings ›
 * Appearance › "Sidebar compact default", now on). Only that one setting
 * changes; an administrator can turn it off again there, and a person's own
 * collapse or expand stays remembered in their browser. down() turns it off.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('system_settings', 'sidebar_compact_default')) {
            DB::table('system_settings')->update(['sidebar_compact_default' => true]);
            // SystemSetting::cached() keeps the row for an hour.
            Cache::forget('system_settings');
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('system_settings', 'sidebar_compact_default')) {
            DB::table('system_settings')->update(['sidebar_compact_default' => false]);
            Cache::forget('system_settings');
        }
    }
};
