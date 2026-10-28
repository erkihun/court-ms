/** Props of Court/Settings/System (Admin\SystemSettingController::edit). */
export interface SystemSettingsProps {
    tab: string;
    /** Every field the form edits, as stored; secrets are not among them. */
    settings: {
        app_name: string | null; short_name: string | null; welcome_message: string | null; about: string | null; footer_text: string | null;
        address: string | null; website_url: string | null; contact_email: string | null; contact_phone: string | null;
        default_locale: string | null; timezone: string | null; date_format: string | null; time_format: string | null;
        session_lifetime: number | null; session_warning_minutes: number | null; password_min_length: number | null;
        login_max_attempts: number | null; lockout_minutes: number | null; audit_retention_days: number | null;
        accent_palette: string | null; default_theme: string | null; custom_css: string | null; api_rate_limit: number | null;
        mail_mailer: string | null; mail_host: string | null; mail_port: number | null; mail_username: string | null; mail_encryption: string | null;
        mail_from_address: string | null; mail_from_name: string | null;
        telegram_default_chat_id: string | null; sms_provider: string | null; sms_sender_id: string | null; sms_base_url: string | null;
        maintenance_mode: boolean; registration_open: boolean; force_https: boolean; mfa_enabled: boolean;
        audit_logging_enabled: boolean; session_extend_on_activity: boolean; use_ethiopian_calendar: boolean; show_language_switcher: boolean;
        password_require_uppercase: boolean; password_require_number: boolean; password_require_symbol: boolean;
        show_banner_on_login: boolean; api_enabled: boolean; mail_enabled: boolean; telegram_enabled: boolean; sms_enabled: boolean;
    };
    /** Whether each stored secret has a value; the values themselves never reach the page. */
    secretsSet: Record<'mail_password' | 'telegram_bot_token' | 'sms_api_key' | 'sms_api_secret', boolean>;
    assets: Record<'logo' | 'banner' | 'favicon' | 'seal', string | null>;
    timezones: string[];
    locales: { code: string; name: string }[];
    roles: { id: number; name: string; description: string | null; mfa_required: boolean }[];
    databaseMetrics: { driver: string; database: string; connection: string; table_count: number; size: string | null; migration_batch: number | null; backup_supported: boolean };
    securityMetrics: {
        https_enforced: boolean; debug_disabled: boolean; secure_cookies: boolean; strong_password: boolean; strict_lockout: boolean; mfa_configured: boolean;
        score: number; environment: string; session_driver: string;
    };
    systemInfo: Record<string, string | boolean>;
}
