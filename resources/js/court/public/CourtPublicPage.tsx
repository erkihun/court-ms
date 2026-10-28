import type { ReactNode } from 'react';
import { useLocale } from '@/hooks/useLocale';

/*
 * The public page grid and title band, copied from the EUISIS
 * Components/public/PublicPage under court-ms names, so the court's public
 * pages share the EUISIS home page's width, gutters and hero face
 * (resources/css/public-site.css).
 */

export function CourtPublicContainer({ children, narrow = false, className = '' }: {
    children: ReactNode;
    /** Reading width for long text. */
    narrow?: boolean;
    className?: string;
}) {
    return (
        <div className={`mx-auto min-w-0 w-full px-4 sm:px-6 lg:px-8 ${narrow ? 'max-w-3xl' : 'max-w-5xl'} ${className}`}>
            {children}
        </div>
    );
}

export type CourtCrumb = { label: string; href?: string };

/** Home → Page, on the hero band; plain anchors, as the header's links. */
export function CourtPublicBreadcrumbs({ items }: { items: CourtCrumb[] }) {
    const { t } = useLocale();

    return (
        <nav aria-label={t('publicSite.breadcrumb')} className="mb-4">
            <ol className="flex flex-wrap items-center gap-1 text-sm text-blue-200">
                <li><a href={route('landing.home')} className="text-blue-100 hover:text-white hover:underline">{t('cm.home.nav.home')}</a></li>
                {items.map((item, index) => {
                    const last = index === items.length - 1;
                    return (
                        <li key={`${item.label}-${index}`} className="flex min-w-0 items-center gap-1">
                            <span aria-hidden="true" className="text-blue-300/60">/</span>
                            {item.href && !last ? (
                                <a href={item.href} className="text-blue-100 hover:text-white hover:underline">{item.label}</a>
                            ) : (
                                <span aria-current={last ? 'page' : undefined} className="truncate text-white">{item.label}</span>
                            )}
                        </li>
                    );
                })}
            </ol>
        </nav>
    );
}

/** The page title block: the home hero's navy band, shorter than home. */
export function CourtPublicPageHeader({ title, eyebrow, meta, breadcrumbs, actions }: {
    title: string;
    eyebrow?: string;
    meta?: ReactNode;
    breadcrumbs?: CourtCrumb[];
    actions?: ReactNode;
}) {
    return (
        <section aria-labelledby="page-heading" className="public-hero py-8 sm:py-14">
            <div className="public-hero-grid" aria-hidden="true" />
            <div className="public-hero-orb" aria-hidden="true" />

            <CourtPublicContainer className="relative">
                {breadcrumbs && breadcrumbs.length > 0 && (
                    <div className="hidden sm:block">
                        <CourtPublicBreadcrumbs items={breadcrumbs} />
                    </div>
                )}
                <div className="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
                    <div className="min-w-0 max-w-3xl">
                        {eyebrow && (
                            <span className="public-hero-enter mb-4 inline-block rounded-full border border-orange-400/30 bg-orange-500/20 px-3 py-1 text-xs font-semibold text-orange-200">
                                {eyebrow}
                            </span>
                        )}
                        <h1 id="page-heading" className="public-hero-enter text-[28px] font-extrabold text-white wrap-anywhere sm:text-4xl">
                            {title}
                        </h1>
                        {meta && <div className="public-hero-enter mt-3 text-sm text-blue-200">{meta}</div>}
                    </div>
                    {actions && <div className="public-hero-enter flex shrink-0 flex-wrap gap-3">{actions}</div>}
                </div>
            </CourtPublicContainer>
        </section>
    );
}

/** The reading card under the band, as the Blade pages framed their text. */
export const courtPublicCardClass =
    'public-card relative -mt-6 min-w-0 overflow-hidden rounded-panel border border-gray-200/80 bg-white p-6 wrap-anywhere sm:-mt-10 sm:p-10 dark:border-slate-800/80 dark:bg-slate-900';
