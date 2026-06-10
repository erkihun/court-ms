<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('system_settings', function (Blueprint $table) {
            // ── Email (SMTP) ─────────────────────────────────────
            $table->boolean('mail_enabled')->default(false)->after('api_rate_limit');
            $table->string('mail_mailer', 20)->default('smtp')->after('mail_enabled');
            $table->string('mail_host')->nullable()->after('mail_mailer');
            $table->unsignedSmallInteger('mail_port')->default(587)->after('mail_host');
            $table->string('mail_username')->nullable()->after('mail_port');
            $table->text('mail_password')->nullable()->after('mail_username'); // encrypted at app layer
            $table->string('mail_encryption', 10)->default('tls')->after('mail_password');
            $table->string('mail_from_address')->nullable()->after('mail_encryption');
            $table->string('mail_from_name')->nullable()->after('mail_from_address');

            // ── Telegram ─────────────────────────────────────────
            $table->boolean('telegram_enabled')->default(false)->after('mail_from_name');
            $table->text('telegram_bot_token')->nullable()->after('telegram_enabled');
            $table->string('telegram_default_chat_id')->nullable()->after('telegram_bot_token');

            // ── SMS ───────────────────────────────────────────────
            $table->boolean('sms_enabled')->default(false)->after('telegram_default_chat_id');
            $table->string('sms_provider', 30)->default('infobip')->after('sms_enabled'); // infobip|twilio|vonage
            $table->text('sms_api_key')->nullable()->after('sms_provider');
            $table->string('sms_api_secret')->nullable()->after('sms_api_key');
            $table->string('sms_sender_id', 20)->nullable()->after('sms_api_secret');
            $table->string('sms_base_url')->nullable()->after('sms_sender_id');
        });
    }

    public function down(): void
    {
        Schema::table('system_settings', function (Blueprint $table) {
            $table->dropColumn([
                'mail_enabled', 'mail_mailer', 'mail_host', 'mail_port',
                'mail_username', 'mail_password', 'mail_encryption',
                'mail_from_address', 'mail_from_name',
                'telegram_enabled', 'telegram_bot_token', 'telegram_default_chat_id',
                'sms_enabled', 'sms_provider', 'sms_api_key', 'sms_api_secret',
                'sms_sender_id', 'sms_base_url',
            ]);
        });
    }
};
