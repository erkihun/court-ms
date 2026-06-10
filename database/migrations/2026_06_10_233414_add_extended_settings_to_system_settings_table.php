<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('system_settings', function (Blueprint $table) {
            // General (footer_text & welcome_message already exist)
            $table->string('address')->nullable()->after('footer_text');
            $table->string('website_url')->nullable()->after('address');

            // Localization
            $table->string('default_locale', 10)->default('en')->after('website_url');
            $table->string('timezone', 60)->default('Africa/Addis_Ababa')->after('default_locale');
            $table->string('date_format', 30)->default('Y-m-d')->after('timezone');
            $table->string('time_format', 10)->default('H:i')->after('date_format');
            $table->boolean('use_ethiopian_calendar')->default(false)->after('time_format');

            // Security
            $table->unsignedSmallInteger('session_lifetime')->default(120)->after('use_ethiopian_calendar');
            $table->unsignedTinyInteger('password_min_length')->default(8)->after('session_lifetime');
            $table->boolean('password_require_uppercase')->default(true)->after('password_min_length');
            $table->boolean('password_require_number')->default(true)->after('password_require_uppercase');
            $table->boolean('password_require_symbol')->default(false)->after('password_require_number');
            $table->unsignedTinyInteger('login_max_attempts')->default(5)->after('password_require_symbol');
            $table->unsignedSmallInteger('lockout_minutes')->default(15)->after('login_max_attempts');
            $table->boolean('force_https')->default(false)->after('lockout_minutes');
            $table->boolean('registration_open')->default(true)->after('force_https');

            // Appearance
            $table->string('accent_palette', 20)->default('blue')->after('registration_open');
            $table->string('default_theme', 10)->default('system')->after('accent_palette');
            $table->boolean('show_banner_on_login')->default(true)->after('default_theme');
            $table->text('custom_css')->nullable()->after('show_banner_on_login');

            // API
            $table->boolean('api_enabled')->default(false)->after('custom_css');
            $table->unsignedSmallInteger('api_rate_limit')->default(60)->after('api_enabled');
        });
    }

    public function down(): void
    {
        Schema::table('system_settings', function (Blueprint $table) {
            $table->dropColumn([
                'address', 'website_url',
                'default_locale', 'timezone', 'date_format', 'time_format', 'use_ethiopian_calendar',
                'session_lifetime', 'password_min_length', 'password_require_uppercase',
                'password_require_number', 'password_require_symbol',
                'login_max_attempts', 'lockout_minutes', 'force_https', 'registration_open',
                'accent_palette', 'default_theme', 'show_banner_on_login', 'custom_css',
                'api_enabled', 'api_rate_limit',
            ]);
        });
    }
};
