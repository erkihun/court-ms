import { Head } from '@inertiajs/react';
import CourtAuthLayout, { CourtEmblem } from '@/court/components/auth/CourtAuthLayout';
import { useLocale } from '@/hooks/useLocale';

/**
 * Shown for every applicant and respondent portal page once employees work on
 * their cases in the EUISIS employee portal (EUISIS_PORTAL_IN_EUISIS): where to
 * go instead, and the way in for court staff.
 */
export default function PortalMoved({ portalUrl }: { portalUrl: string | null }) {
    const { t } = useLocale();
    const text = (key: string) => t(`court.signIn.${key}`);

    return (
        <CourtAuthLayout>
            <Head title={text('movedTitle')} />
            <div className="login-rise overflow-hidden rounded-xl border border-slate-200/80 bg-white shadow-[0_24px_60px_-30px_rgba(12,26,49,0.45)] dark:border-slate-800 dark:bg-[#0f1b31]">
                <div aria-hidden="true" className="h-1 bg-gradient-to-r from-(--court-navy) via-(--court-brass) to-(--court-navy)" />
                <div className="px-6 pb-7 pt-7 text-center sm:px-8">
                    <span className="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-(--court-ink) text-(--court-brass)"><CourtEmblem className="h-10 w-10" /></span>
                    <h1 className="mt-5 text-xl font-semibold tracking-tight text-(--court-ink) dark:text-white">{text('movedTitle')}</h1>
                    <p className="mt-2 text-sm leading-relaxed text-slate-600 dark:text-slate-300">{text('movedText')}</p>
                    {portalUrl ? (
                        <a href={portalUrl} className="mt-6 inline-flex w-full items-center justify-center gap-2 rounded-md bg-(--court-navy) px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-(--court-navy-hover) focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-(--court-brass)">
                            {text('movedAction')} <span aria-hidden="true">›</span>
                        </a>
                    ) : (
                        <p className="mt-6 rounded-md bg-(--court-brass)/10 px-4 py-3 text-sm text-slate-700 dark:text-slate-200">{text('movedNoLink')}</p>
                    )}
                    <a href={route('login')} className="mt-4 inline-block text-sm font-medium text-(--court-navy) underline-offset-2 hover:underline dark:text-(--court-brass-soft)">{text('staffLink')}</a>
                </div>
            </div>
        </CourtAuthLayout>
    );
}
