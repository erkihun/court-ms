<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * The EUISIS appearance settings court-ms's screens read (the copied
 * AuthenticatedLayout, sidebar, theme toggle and language switcher): brand
 * colours, density, shapes and shell switches. Null keeps the EUISIS default
 * (App\Support\Ui\CourtSettingsSchema::APPEARANCE_DEFAULTS). Additive only.
 */
return new class extends Migration
{
    /** @var array<string, string> column => type */
    private const COLUMNS = [
        'primary_color' => 'color',
        'secondary_color' => 'color',
        'accent_color' => 'color',
        'sidebar_color' => 'color',
        'table_density' => 'choice',
        'button_style' => 'choice',
        'card_radius' => 'choice',
        'logo_position' => 'choice',
        'allow_user_theme_switching' => 'boolean',
        'enable_ui_animations' => 'boolean',
        'sidebar_compact_default' => 'boolean',
        'show_breadcrumbs' => 'boolean',
        'sticky_table_headers' => 'boolean',
    ];

    public function up(): void
    {
        Schema::table('system_settings', function (Blueprint $table): void {
            foreach (self::COLUMNS as $column => $type) {
                if (Schema::hasColumn('system_settings', $column)) {
                    continue;
                }
                match ($type) {
                    'color' => $table->string($column, 7)->nullable(),
                    'choice' => $table->string($column, 20)->nullable(),
                    'boolean' => $table->boolean($column)->nullable(),
                };
            }
        });
    }

    public function down(): void
    {
        $existing = array_values(array_filter(array_keys(self::COLUMNS), fn (string $column): bool => Schema::hasColumn('system_settings', $column)));
        if ($existing !== []) {
            Schema::table('system_settings', fn (Blueprint $table) => $table->dropColumn($existing));
        }
    }
};
