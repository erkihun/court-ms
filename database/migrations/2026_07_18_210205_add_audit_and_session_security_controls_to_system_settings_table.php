<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('system_settings', function (Blueprint $table): void {
            $table->boolean('audit_logging_enabled')->default(true)->after('mfa_enabled');
            $table->unsignedSmallInteger('audit_retention_days')->default(365)->after('audit_logging_enabled');
            $table->unsignedSmallInteger('session_warning_minutes')->default(5)->after('session_lifetime');
            $table->boolean('session_extend_on_activity')->default(true)->after('session_warning_minutes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('system_settings', function (Blueprint $table): void {
            $table->dropColumn(['audit_logging_enabled', 'audit_retention_days', 'session_warning_minutes', 'session_extend_on_activity']);
        });
    }
};
