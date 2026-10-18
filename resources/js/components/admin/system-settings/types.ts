import type { InertiaFormProps } from '@inertiajs/react';

export const TABS = ['general', 'branding', 'localization', 'security', 'appearance', 'notifications', 'data', 'api'] as const;
export type SettingsTab = (typeof TABS)[number];

/** Field names posted to settings.system.update (see SystemSettingController::update). */
export interface SettingsForm {
    app_name: string;
    short_name: string;
    welcome_message: string;
    about: string;
    footer_text: string;
    address: string;
    website_url: string;
    contact_email: string;
    contact_phone: string;

    banner: File | null;
    logo: File | null;
    favicon: File | null;
    seal: File | null;

    default_locale: string;
    show_language_switcher: boolean;
    timezone: string;
    date_format: string;
    time_format: string;
    use_ethiopian_calendar: boolean;

    maintenance_mode: boolean;
    registration_open: boolean;
    force_https: boolean;
    mfa_enabled: boolean;
    mfa_roles: number[];
    session_lifetime: string;
    session_warning_minutes: string;
    session_extend_on_activity: boolean;
    password_min_length: string;
    password_require_uppercase: boolean;
    password_require_number: boolean;
    password_require_symbol: boolean;
    login_max_attempts: string;
    lockout_minutes: string;

    accent_palette: string;
    default_theme: string;
    show_banner_on_login: boolean;
    custom_css: string;

    mail_enabled: boolean;
    mail_mailer: string;
    mail_host: string;
    mail_port: string;
    mail_username: string;
    mail_password: string;
    mail_encryption: string;
    mail_from_address: string;
    mail_from_name: string;

    telegram_enabled: boolean;
    telegram_bot_token: string;
    telegram_default_chat_id: string;

    sms_enabled: boolean;
    sms_provider: string;
    sms_base_url: string;
    sms_api_key: string;
    sms_api_secret: string;
    sms_sender_id: string;

    audit_logging_enabled: boolean;
    audit_retention_days: string;

    api_enabled: boolean;
    api_rate_limit: string;
}

export type SecretField = 'mail_password' | 'telegram_bot_token' | 'sms_api_key' | 'sms_api_secret';
export type FileField = 'banner' | 'logo' | 'favicon' | 'seal';

/** Values the server sends: everything except files and secrets, which always start empty. */
export type SettingsValues = Omit<SettingsForm, FileField | SecretField>;

export type SettingsFormHandle = InertiaFormProps<SettingsForm>;

/** Which tab each field lives on, to flag tabs that hold validation errors. */
export const TAB_FIELDS: Record<SettingsTab, string[]> = {
    general: ['app_name', 'short_name', 'welcome_message', 'about', 'footer_text', 'address', 'website_url', 'contact_email', 'contact_phone'],
    branding: ['banner', 'logo', 'favicon', 'seal'],
    localization: ['default_locale', 'show_language_switcher', 'timezone', 'date_format', 'time_format', 'use_ethiopian_calendar'],
    security: [
        'maintenance_mode',
        'registration_open',
        'force_https',
        'mfa_enabled',
        'mfa_roles',
        'session_lifetime',
        'login_max_attempts',
        'lockout_minutes',
        'session_warning_minutes',
        'session_extend_on_activity',
        'password_min_length',
        'password_require_uppercase',
        'password_require_number',
        'password_require_symbol',
    ],
    appearance: ['accent_palette', 'default_theme', 'show_banner_on_login', 'custom_css'],
    notifications: [
        'mail_enabled',
        'mail_mailer',
        'mail_host',
        'mail_port',
        'mail_username',
        'mail_password',
        'mail_encryption',
        'mail_from_address',
        'mail_from_name',
        'telegram_enabled',
        'telegram_bot_token',
        'telegram_default_chat_id',
        'sms_enabled',
        'sms_provider',
        'sms_base_url',
        'sms_api_key',
        'sms_api_secret',
        'sms_sender_id',
        'smtp_test',
        'recipient',
    ],
    data: ['audit_logging_enabled', 'audit_retention_days'],
    api: ['api_enabled', 'api_rate_limit'],
};

export function tabHasErrors(tab: SettingsTab, errors: Record<string, string | undefined>): boolean {
    return Object.keys(errors).some((key) => TAB_FIELDS[tab].includes(key.split('.')[0] ?? key));
}
