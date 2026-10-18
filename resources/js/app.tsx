import '../css/app.css';

import { createInertiaApp } from '@inertiajs/react';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import type { ComponentType, ReactNode } from 'react';
import { createRoot } from 'react-dom/client';
import { loadTranslations } from '@/lib/i18n';
import type { SharedProps } from '@/types';

type PageModule = { default: ComponentType & { layout?: (page: ReactNode) => ReactNode } };

type LayoutModule = { default: ComponentType<{ children: ReactNode }> };

/** Each portal's layout is its own chunk, so applicants never download the admin shell and vice versa. */
const layouts: Record<'admin' | 'party' | 'public', () => Promise<LayoutModule>> = {
    admin: () => import('@/layouts/AdminLayout'),
    party: () => import('@/layouts/PartyLayout'),
    public: () => import('@/layouts/PublicLayout'),
};

function layoutKey(name: string): keyof typeof layouts | null {
    if (name.startsWith('Admin/')) return 'admin';
    if (name.startsWith('Applicant/') || name.startsWith('Respondent/')) return 'party';
    if (name.startsWith('Public/')) return 'public';
    return null;
}

/** Pages opt out of the folder's default layout by setting `Page.layout` themselves. */
async function defaultLayout(name: string): Promise<((page: ReactNode) => ReactNode) | undefined> {
    const key = layoutKey(name);
    if (key === null) return undefined;

    const { default: Layout } = await layouts[key]();
    return (page) => <Layout>{page}</Layout>;
}

const initialPage = JSON.parse(document.getElementById('app')?.dataset.page ?? '{}') as { props?: SharedProps };
const appName = initialPage.props?.app?.name ?? document.title;

createInertiaApp({
    title: (title) => (title ? `${title} | ${appName}` : appName),
    resolve: async (name) => {
        const [page, layout] = await Promise.all([
            resolvePageComponent<PageModule>(`./Pages/${name}.tsx`, import.meta.glob<PageModule>('./Pages/**/*.tsx')),
            defaultLayout(name),
        ]);
        page.default.layout ??= layout;
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
