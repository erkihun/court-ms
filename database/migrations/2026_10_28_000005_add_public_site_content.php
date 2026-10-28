<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The public site, managed in both languages from the landing manager: each
 * landing row keeps its Amharic text in `translations` ({"am": {field: text}}),
 * a service card may link somewhere, and the court's members (its composition)
 * get their own list. Additive only; down() removes exactly this.
 */
return new class extends Migration
{
    private const TABLES = ['home_slides', 'home_faqs', 'home_services', 'home_timeline_steps', 'home_resources'];

    public function up(): void
    {
        foreach (self::TABLES as $table) {
            if (Schema::hasTable($table) && ! Schema::hasColumn($table, 'translations')) {
                Schema::table($table, fn (Blueprint $blueprint) => $blueprint->json('translations')->nullable());
            }
        }

        if (Schema::hasTable('home_services') && ! Schema::hasColumn('home_services', 'href')) {
            Schema::table('home_services', fn (Blueprint $table) => $table->string('href', 500)->nullable()->after('accent'));
        }

        if (! Schema::hasTable('court_members')) {
            Schema::create('court_members', function (Blueprint $table): void {
                $table->id();
                $table->unsignedInteger('sort_order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->string('name');
                $table->string('role')->nullable();
                $table->text('bio')->nullable();
                $table->string('photo_path')->nullable();
                $table->json('translations')->nullable();
                $table->timestamps();
                $table->index(['is_active', 'sort_order']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('court_members');

        if (Schema::hasTable('home_services') && Schema::hasColumn('home_services', 'href')) {
            Schema::table('home_services', fn (Blueprint $table) => $table->dropColumn('href'));
        }

        foreach (self::TABLES as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'translations')) {
                Schema::table($table, fn (Blueprint $blueprint) => $blueprint->dropColumn('translations'));
            }
        }
    }
};
