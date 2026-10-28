import type { ReactNode } from 'react';
import { Head } from '@inertiajs/react';
import { useLocale } from '@/hooks/useLocale';
import { useSystemSettings } from '@/hooks/useSystemSettings';
import LocalizedUiProvider from '@/Components/ui/LocalizedUiProvider';
import AppToaster from '@/Components/ui/AppToaster';
import CourtPublicHeader from '@/court/public/CourtPublicHeader';
import CourtPublicFooter from '@/court/public/CourtPublicFooter';
import type { PublicSiteChrome } from '@/court/types/public';
import '../../../css/public-site.css';

interface Props {
    /** Browser tab title. */
    title: string;
    description?: string;
    /** Footer and navigation data the server sends with every public page. */
    publicSite?: PublicSiteChrome | null;
    children: ReactNode;
}

/**
 * The court's public site layout, modelled on the EUISIS PublicLayout: skip
 * link, header, main, footer and the toaster, with the `public-site` look
 * (resources/css/public-site.css). Navigation and footer are the court's own:
 * the home page, resources, user manual and terms, the applicant and
 * respondent portals, and the landing manager's footer settings.
 */
export default function PublicLayout({ title, description, publicSite = null, children }: Props) {
    const { t } = useLocale();
    const { getBoolean } = useSystemSettings();

    return (
        <LocalizedUiProvider>
            <Head title={title}>
                {description && <meta head-key="description" name="description" content={description} />}
                <meta head-key="og:title" property="og:title" content={title} />
            </Head>

            <div
                className="public-site flex min-h-screen flex-col bg-(--app-background)"
                data-motion={getBoolean('appearance.enable_ui_animations', true) ? 'on' : 'off'}
            >
                <a
                    href="#main-content"
                    className="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-60 focus:rounded-control focus:bg-(--app-surface) focus:px-4 focus:py-2 focus:text-sm focus:font-medium focus:text-(--app-foreground) focus:shadow-sm focus:outline-hidden focus:ring-2 focus:ring-primary"
                >
                    {t('publicSite.skipToContent')}
                </a>

                <CourtPublicHeader userManualEnabled={publicSite?.userManualEnabled ?? false} />

                <main id="main-content" tabIndex={-1} className="flex-1 focus:outline-hidden">
                    {children}
                </main>

                <CourtPublicFooter footer={publicSite?.footer ?? null} userManualEnabled={publicSite?.userManualEnabled ?? false} />
            </div>

            <AppToaster />
        </LocalizedUiProvider>
    );
}
