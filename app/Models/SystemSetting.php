<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Auditable;

class SystemSetting extends Model implements AuditableContract
{
    use Auditable;

    protected $table = 'system_settings';

    protected $fillable = [
        // Identity
        'app_name', 'short_name', 'welcome_message', 'about', 'footer_text',
        'address', 'website_url', 'contact_email', 'contact_phone',

        // Branding assets
        'logo_path', 'banner_path', 'favicon_path', 'seal_path',

        // Localization
        'default_locale', 'show_language_switcher', 'timezone', 'date_format', 'time_format', 'use_ethiopian_calendar',

        // Security
        'maintenance_mode', 'registration_open', 'force_https',
        'session_lifetime', 'password_min_length',
        'password_require_uppercase', 'password_require_number', 'password_require_symbol',
        'login_max_attempts', 'lockout_minutes',

        // Appearance
        'accent_palette', 'default_theme', 'show_banner_on_login', 'custom_css',

        // API
        'api_enabled', 'api_rate_limit',

        // Email (SMTP)
        'mail_enabled', 'mail_mailer', 'mail_host', 'mail_port',
        'mail_username', 'mail_password', 'mail_encryption',
        'mail_from_address', 'mail_from_name',

        // Telegram
        'telegram_enabled', 'telegram_bot_token', 'telegram_default_chat_id',

        // SMS
        'sms_enabled', 'sms_provider', 'sms_api_key', 'sms_api_secret',
        'sms_sender_id', 'sms_base_url',
    ];

    protected $casts = [
        'maintenance_mode'          => 'boolean',
        'registration_open'         => 'boolean',
        'force_https'               => 'boolean',
        'use_ethiopian_calendar'    => 'boolean',
        'show_language_switcher'    => 'boolean',
        'password_require_uppercase'=> 'boolean',
        'password_require_number'   => 'boolean',
        'password_require_symbol'   => 'boolean',
        'show_banner_on_login'      => 'boolean',
        'api_enabled'               => 'boolean',
        'mail_enabled'              => 'boolean',
        'mail_port'                 => 'integer',
        'telegram_enabled'          => 'boolean',
        'sms_enabled'               => 'boolean',
        'session_lifetime'          => 'integer',
        'password_min_length'       => 'integer',
        'login_max_attempts'        => 'integer',
        'lockout_minutes'           => 'integer',
        'api_rate_limit'            => 'integer',
    ];

    /** Fetch the single settings row, or return a new instance with sane defaults. */
    public static function current(): self
    {
        return static::first() ?? new static([
            'app_name'                   => config('app.name', 'Court MS'),
            'short_name'                 => 'CMS',
            'default_locale'             => config('app.locale', 'en'),
            'show_language_switcher'     => true,
            'timezone'                   => config('app.timezone', 'Africa/Addis_Ababa'),
            'date_format'                => 'Y-m-d',
            'time_format'                => 'H:i',
            'use_ethiopian_calendar'     => false,
            'maintenance_mode'           => false,
            'registration_open'          => true,
            'force_https'                => false,
            'session_lifetime'           => 120,
            'password_min_length'        => 8,
            'password_require_uppercase' => true,
            'password_require_number'    => true,
            'password_require_symbol'    => false,
            'login_max_attempts'         => 5,
            'lockout_minutes'            => 15,
            'accent_palette'             => 'blue',
            'default_theme'              => 'system',
            'show_banner_on_login'       => true,
            'api_enabled'                => false,
            'api_rate_limit'             => 60,
            'mail_enabled'               => false,
            'mail_mailer'                => 'smtp',
            'mail_port'                  => 587,
            'mail_encryption'            => 'tls',
            'telegram_enabled'           => false,
            'sms_enabled'                => false,
        ]);
    }
}
