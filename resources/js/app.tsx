import '../css/app.css';

import { createInertiaApp } from '@inertiajs/react';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import type { ComponentType, ReactNode } from 'react';
import { createRoot } from 'react-dom/client';
import AdminLayout from '@/layouts/AdminLayout';
import PartyLayout from '@/layouts/PartyLayout';
import { loadTranslations } from '@/lib/i18n';
import type { SharedProps } from '@/types';

type PageModule = { default: ComponentType & { layout?: (page: ReactNode) => ReactNode } };

/** Pages opt out of the folder's default layout by setting `Page.layout` themselves. */
function defaultLayout(name: string): ((page: ReactNode) => ReactNode) | undefined {
    if (name.startsWith('Admin/')) {
        return (page) => <AdminLayout>{page}</AdminLayout>;
    }

    if (name.startsWith('Applicant/') || name.startsWith('Respondent/')) {
        return (page) => <PartyLayout>{page}</PartyLayout>;
    }

    return undefined;
}

const initialPage = JSON.parse(document.getElementById('app')?.dataset.page ?? '{}') as { props?: SharedProps };
const appName = initialPage.props?.app?.name ?? document.title;

createInertiaApp({
    title: (title) => (title ? `${title} | ${appName}` : appName),
    resolve: async (name) => {
        const page = await resolvePageComponent<PageModule>(`./Pages/${name}.tsx`, import.meta.glob<PageModule>('./Pages/**/*.tsx'));
        page.default.layout ??= defaultLayout(name);
        return page;
    },
    setup: async ({ el, App, props }) => {
        await loadTranslations((props.initialPage.props as unknown as SharedProps).app.translationsUrl);
        createRoot(el).render(<App {...props} />);
    },
    progress: {
        color: 'rgb(var(--ac))',
        delay: 150,
    },
});
