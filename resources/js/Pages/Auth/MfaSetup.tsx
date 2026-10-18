import { Link, useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';
import { AuthAlerts } from '@/components/auth/AuthAlerts';
import { AuthCardLayout } from '@/components/auth/AuthCardLayout';
import { AuthField, fieldAria } from '@/components/auth/AuthField';
import { t } from '@/lib/i18n';
import { route } from '@/lib/route';

interface Props {
    confirmed: boolean;
    /** Pending (unconfirmed) secret, or null. */
    secret: string | null;
    /** QR code for the pending secret as an SVG data: URI. */
    qrCode: string | null;
    /** Plain recovery codes, present only on the visit right after enabling MFA. */
    recoveryCodes: string[];
}

/** Staff two-factor setup: start, scan the QR code, confirm with a code, keep the recovery codes. */
export default function MfaSetup({ confirmed, secret, qrCode, recoveryCodes }: Props) {
    const begin = useForm({});
    const form = useForm({ code: '' });

    const confirm = (event: FormEvent) => {
        event.preventDefault();
        form.post(route('mfa.setup.confirm'), { onError: () => form.setData('code', '') });
    };

    return (
        <AuthCardLayout title={t('auth.mfa_title')} subtitle={t('auth.mfa_setup_hint')} accent="blue">
            <AuthAlerts errors="none" />

            {recoveryCodes.length > 0 && (
                <div className="auth-alert auth-alert-warning mb-4">
                    <strong>{t('auth.mfa_recovery_codes')}</strong>
                    <p className="mt-1">{t('auth.mfa_recovery_warning')}</p>
                    <div className="mt-3 grid grid-cols-2 gap-2">
                        {recoveryCodes.map((code) => (
                            <code key={code} className="rounded-md border border-amber-200 bg-white p-2 text-center font-mono text-xs">
                                {code}
                            </code>
                        ))}
                    </div>
                </div>
            )}

            {confirmed ? (
                <div className="auth-alert auth-alert-success">
                    <strong>{t('auth.mfa_active')}</strong>
                    <p className="mt-1">{t('auth.mfa_active_hint')}</p>
                </div>
            ) : secret ? (
                <>
                    {qrCode && (
                        <div className="mb-5 grid place-items-center rounded-xl border border-slate-200 bg-white p-4">
                            <img src={qrCode} alt={t('auth.mfa_scan_qr')} width={220} height={220} />
                        </div>
                    )}
                    <h2 className="text-base font-semibold text-slate-900">{t('auth.mfa_scan_qr')}</h2>
                    <p className="mt-1 mb-3 text-sm leading-relaxed text-slate-500">{t('auth.mfa_scan_qr_hint')}</p>
                    <code className="block rounded-lg bg-slate-100 px-3 py-2 font-mono text-xs font-semibold break-all text-slate-700">{secret}</code>

                    <form onSubmit={confirm} className="mt-4 space-y-4">
                        <AuthField id="code" label={t('auth.mfa_authentication_code')} error={form.errors.code}>
                            <input
                                {...fieldAria('code', form.errors.code)}
                                className="auth-input tracking-[0.35em]"
                                name="code"
                                inputMode="numeric"
                                autoComplete="one-time-code"
                                pattern="[0-9]{6}"
                                maxLength={6}
                                value={form.data.code}
                                onChange={(event) => form.setData('code', event.target.value.replace(/\D/g, ''))}
                                required
                                autoFocus
                            />
                        </AuthField>
                        <button type="submit" className="auth-primary-btn" disabled={form.processing || form.data.code.length !== 6}>
                            {t('auth.mfa_confirm_enable')}
                        </button>
                    </form>
                </>
            ) : (
                <>
                    <h2 className="text-base font-semibold text-slate-900">{t('auth.mfa_not_configured')}</h2>
                    <p className="mt-1 mb-4 text-sm leading-relaxed text-slate-500">{t('auth.mfa_begin_hint')}</p>
                    <button type="button" className="auth-primary-btn" disabled={begin.processing} onClick={() => begin.post(route('mfa.setup.begin'))}>
                        {t('auth.mfa_begin')}
                    </button>
                </>
            )}

            <div className="mt-5 text-center">
                <Link href={route('profile.edit')} className="auth-link">
                    ← {t('auth.mfa_back_profile')}
                </Link>
            </div>
        </AuthCardLayout>
    );
}
