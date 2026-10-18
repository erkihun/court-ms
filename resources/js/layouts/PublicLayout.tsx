import '../../css/public.css';
import { type ReactNode, useEffect } from 'react';
import { AccentSwitcher, PublicFooter, PublicHeader } from '@/components/public/PublicChrome';
import { t } from '@/lib/i18n';

/** Shell for the public, signed-out pages (landing, resources, manual, terms). Always light. */
export default function PublicLayout({ children }: { children: ReactNode }) {
    // Reached by an in-app visit from a dark portal page: switch to light while here.
    useEffect(() => {
        const root = document.documentElement;
        const wasDark = root.classList.contains('dark');
        root.classList.remove('dark');
        return () => {
            if (wasDark) root.classList.add('dark');
        };
    }, []);

    return (
        <div className="min-h-screen bg-white font-ui text-slate-900 antialiased">
            <a
                href="#main-content"
                className="sr-only z-[100] rounded-lg bg-orange-500 px-4 py-2 text-sm font-semibold text-white focus:not-sr-only focus:fixed focus:start-4 focus:top-4"
            >
                {t('home.accessibility.skip_to_content')}
            </a>
            <PublicHeader />
            <main id="main-content" tabIndex={-1} className="focus:outline-none">
                {children}
            </main>
            <div className="pub-no-print">
                <PublicFooter />
                <AccentSwitcher />
            </div>
        </div>
    );
}
