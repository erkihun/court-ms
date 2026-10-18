import type { ReactNode } from 'react';
import { PartyHeader } from '@/components/layout/party/PartyHeader';
import { SessionTimeoutWarning } from '@/components/layout/SessionTimeoutWarning';
import { ToastProvider } from '@/components/ui/Toaster';
import { useRouteIs } from '@/hooks/useRouteIs';
import { useShared } from '@/hooks/useShared';
import { cn } from '@/lib/cn';
import { t } from '@/lib/i18n';
import { initials } from '@/lib/str';

/** Case pages use the case document typography. */
const CASE_ROUTES = ['applicant.cases.*', 'applicant.respondent.cases.*', 'respondent.cases.*'];

/**
 * Applicant / respondent portal shell, used as a persistent layout so the
 * header (and its notification polling) survives page visits.
 */
export default function PartyLayout({ children }: { children: ReactNode }) {
    const { app, auth } = useShared();
    const routeIs = useRouteIs();
    const respondent = auth.applicant?.actingAsRespondent ?? false;

    return (
        <ToastProvider>
            <div className="ui-shell min-h-screen font-ui text-(--text)">
                <PartyHeader />

                <main className={cn('page-enter mx-auto max-w-[1600px] px-4 py-8 sm:py-10', routeIs(...CASE_ROUTES) && 'case-font-scope case-typography')}>
                    <div className="space-y-6">{children}</div>
                </main>

                <footer className="mt-12 border-t backdrop-blur-sm" style={{ borderColor: 'var(--border)', background: 'color-mix(in srgb, var(--surface-strong) 95%, transparent)' }}>
                    <div className="mx-auto max-w-[1600px] px-4 py-5">
                        <div className="rounded-2xl border px-4 py-4 shadow-xs" style={{ borderColor: 'var(--border)', background: 'var(--surface)' }}>
                            <div className="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                                <div className="flex items-center gap-3">
                                    <span className="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-[rgb(var(--ac))] text-sm font-bold text-white">
                                        {initials(app.shortName, 2)}
                                    </span>
                                    <div className="text-sm text-(--text-muted)">
                                        <div className="font-semibold text-(--text)">{app.name}</div>
                                        <div>{app.footerText}</div>
                                    </div>
                                </div>
                                <div className="flex flex-wrap items-center gap-2 text-xs">
                                    <span className="inline-flex items-center gap-1.5 rounded-full border border-[rgb(var(--ac)/0.25)] bg-[rgb(var(--ac)/0.12)] px-3 py-1 font-semibold text-[rgb(var(--ac))]">
                                        <span className="h-1.5 w-1.5 rounded-full bg-[rgb(var(--ac))]" />
                                        {t('app.court_portal')}
                                    </span>
                                    {respondent && (
                                        <span
                                            className="rounded-full px-3 py-1 font-semibold"
                                            style={{
                                                background: 'color-mix(in srgb, var(--warning) 14%, transparent)',
                                                color: 'var(--warning)',
                                                border: '1px solid color-mix(in srgb, var(--warning) 30%, transparent)',
                                            }}
                                        >
                                            {t('respondent.respondent_view')}
                                        </span>
                                    )}
                                    <span className="rounded-full border border-(--border) bg-(--surface-strong) px-3 py-1 font-medium text-(--text-muted)">{app.today.short}</span>
                                    <span className="rounded-full border border-(--border) bg-(--surface-strong) px-3 py-1 font-medium text-(--text-muted)">{app.today.time}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </footer>

                <SessionTimeoutWarning />
            </div>
        </ToastProvider>
    );
}
