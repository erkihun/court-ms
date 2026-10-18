import { ClockIcon, FingerPrintIcon, KeyIcon, LockClosedIcon } from '@heroicons/react/24/outline';
import { Field } from '@/components/ui/Field';
import { cn } from '@/lib/cn';
import { t } from '@/lib/i18n';
import { SettingsCard, ToggleRow, UnitInput } from '../controls';
import type { SettingsFormHandle } from '../types';

export interface SecurityMetrics {
    https_enforced: boolean;
    debug_disabled: boolean;
    secure_cookies: boolean;
    strong_password: boolean;
    strict_lockout: boolean;
    mfa_configured: boolean;
    score: number;
    environment: string;
    session_driver: string;
}

export interface RoleOption {
    id: number;
    name: string;
    description: string | null;
}

type BooleanField =
    | 'maintenance_mode'
    | 'registration_open'
    | 'force_https'
    | 'mfa_enabled'
    | 'password_require_uppercase'
    | 'password_require_number'
    | 'password_require_symbol';

export function SecurityTab({ form, metrics, roles }: { form: SettingsFormHandle; metrics: SecurityMetrics; roles: RoleOption[] }) {
    const { data, setData, errors } = form;

    const controls: Array<{ state: boolean; label: string }> = [
        { state: metrics.https_enforced, label: t('settings.https_protection') },
        { state: metrics.debug_disabled, label: t('settings.debug_protection') },
        { state: metrics.secure_cookies, label: t('settings.cookie_protection') },
        { state: metrics.strong_password, label: t('settings.password_strength') },
        { state: metrics.strict_lockout, label: t('settings.login_protection') },
        { state: metrics.mfa_configured, label: t('settings.mfa_protection') },
    ];
    const access: Array<{ name: BooleanField; label: string; desc: string }> = [
        { name: 'maintenance_mode', label: t('settings.maintenance_mode'), desc: t('settings.maintenance_mode_desc') },
        { name: 'registration_open', label: t('settings.registration_open'), desc: t('settings.registration_open_desc') },
        { name: 'force_https', label: t('settings.force_https'), desc: t('settings.force_https_desc') },
        { name: 'mfa_enabled', label: t('settings.mfa_enabled'), desc: t('settings.mfa_enabled_desc') },
    ];
    const passwordRules: Array<{ name: BooleanField; label: string; desc: string }> = [
        { name: 'password_require_uppercase', label: t('settings.require_uppercase'), desc: t('settings.require_uppercase_desc') },
        { name: 'password_require_number', label: t('settings.require_number'), desc: t('settings.require_number_desc') },
        { name: 'password_require_symbol', label: t('settings.require_symbol'), desc: t('settings.require_symbol_desc') },
    ];
    const toggleRole = (id: number, on: boolean) => setData('mfa_roles', on ? [...data.mfa_roles, id] : data.mfa_roles.filter((roleId) => roleId !== id));
    const protectedState = metrics.score >= 80;

    return (
        <>
            <section className="mb-4 rounded-2xl border border-[rgb(var(--ac)/0.2)] bg-linear-to-br from-[rgb(var(--ac)/0.12)] to-(--surface-strong) p-5">
                <div className="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <p className="text-xs font-semibold tracking-wide text-(--text-subtle) uppercase">{t('settings.security_posture')}</p>
                        <p className="mt-1 flex items-baseline gap-2">
                            <strong className="text-3xl font-bold text-(--text)">{metrics.score}%</strong>
                            <span className="text-sm text-(--text-muted)">{t('settings.controls_active')}</span>
                        </p>
                        <p className="mt-1 max-w-xl text-xs text-(--text-subtle)">{t('settings.security_posture_hint')}</p>
                    </div>
                    <div className="text-end">
                        <span
                            className={cn(
                                'inline-flex rounded-full px-2.5 py-1 text-xs font-bold',
                                protectedState
                                    ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300'
                                    : 'bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-300',
                            )}
                        >
                            {protectedState ? t('settings.protected') : t('settings.action_recommended')}
                        </span>
                        <p className="mt-1 text-xs text-(--text-subtle) capitalize">
                            {metrics.environment} · {metrics.session_driver}
                        </p>
                    </div>
                </div>
                <ul className="mt-4 grid gap-2 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
                    {controls.map((control) => (
                        <li key={control.label} className="rounded-lg border border-(--border) bg-(--surface-strong) p-3">
                            <span className={cn('inline-block h-2 w-2 rounded-full', control.state ? 'bg-emerald-500' : 'bg-amber-500')} aria-hidden="true" />
                            <strong className="mt-1.5 block text-xs text-(--text)">{control.label}</strong>
                            <span className="text-[11px] text-(--text-subtle)">{control.state ? t('settings.active') : t('settings.review_needed')}</span>
                        </li>
                    ))}
                </ul>
            </section>

            <div className="mb-4 grid gap-4 lg:grid-cols-3">
                <SettingsCard title={t('settings.access_controls')} icon={<LockClosedIcon />}>
                    <div className="space-y-2">
                        {access.map((toggle) => (
                            <ToggleRow
                                key={toggle.name}
                                label={toggle.label}
                                description={toggle.desc}
                                checked={data[toggle.name]}
                                onChange={(checked) => setData(toggle.name, checked)}
                            />
                        ))}
                    </div>
                </SettingsCard>
                <SettingsCard title={t('settings.mfa_role_policy')} icon={<FingerPrintIcon />}>
                    <p className="mb-3 text-xs text-(--text-subtle)">{t('settings.mfa_role_policy_hint')}</p>
                    {roles.length === 0 ? (
                        <p className="text-sm text-(--text-subtle)">{t('settings.mfa_no_roles')}</p>
                    ) : (
                        <div className="grid gap-2">
                            {roles.map((role) => (
                                <ToggleRow
                                    key={role.id}
                                    label={role.name}
                                    description={role.description || t('settings.mfa_role_default_desc')}
                                    checked={data.mfa_roles.includes(role.id)}
                                    onChange={(checked) => toggleRole(role.id, checked)}
                                />
                            ))}
                        </div>
                    )}
                    {errors.mfa_roles && <p className="mt-2 text-xs font-medium text-rose-600">{errors.mfa_roles}</p>}
                </SettingsCard>
                <SettingsCard title={t('settings.password_policy')} icon={<KeyIcon />}>
                    <Field label={t('settings.password_min_length')} error={errors.password_min_length} className="mb-4">
                        <UnitInput
                            value={data.password_min_length}
                            onChange={(value) => setData('password_min_length', value)}
                            unit={t('settings.unit_characters')}
                            min={6}
                            max={128}
                            className="max-w-48"
                        />
                    </Field>
                    <div className="space-y-2">
                        {passwordRules.map((toggle) => (
                            <ToggleRow
                                key={toggle.name}
                                label={toggle.label}
                                description={toggle.desc}
                                checked={data[toggle.name]}
                                onChange={(checked) => setData(toggle.name, checked)}
                            />
                        ))}
                    </div>
                </SettingsCard>
            </div>

            <SettingsCard title={t('settings.session_lockout')} icon={<ClockIcon />}>
                <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <Field label={t('settings.session_lifetime')} error={errors.session_lifetime} hint={t('settings.session_lifetime_hint')}>
                        <UnitInput
                            value={data.session_lifetime}
                            onChange={(value) => setData('session_lifetime', value)}
                            unit={t('settings.unit_min')}
                            min={5}
                            max={43200}
                        />
                    </Field>
                    <Field label={t('settings.max_login_attempts')} error={errors.login_max_attempts}>
                        <UnitInput
                            value={data.login_max_attempts}
                            onChange={(value) => setData('login_max_attempts', value)}
                            unit={t('settings.unit_tries')}
                            min={1}
                            max={100}
                        />
                    </Field>
                    <Field label={t('settings.lockout_duration')} error={errors.lockout_minutes}>
                        <UnitInput
                            value={data.lockout_minutes}
                            onChange={(value) => setData('lockout_minutes', value)}
                            unit={t('settings.unit_min')}
                            min={1}
                            max={1440}
                        />
                    </Field>
                    <Field label={t('settings.session_warning_minutes')} error={errors.session_warning_minutes} hint={t('settings.session_warning_hint')}>
                        <UnitInput
                            value={data.session_warning_minutes}
                            onChange={(value) => setData('session_warning_minutes', value)}
                            unit={t('settings.unit_min')}
                            min={1}
                            max={120}
                        />
                    </Field>
                </div>
                <div className="mt-4">
                    <ToggleRow
                        label={t('settings.session_extend_on_activity')}
                        description={t('settings.session_extend_on_activity_desc')}
                        checked={data.session_extend_on_activity}
                        onChange={(checked) => setData('session_extend_on_activity', checked)}
                    />
                </div>
            </SettingsCard>
        </>
    );
}
