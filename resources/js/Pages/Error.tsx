import { Head } from '@inertiajs/react';
import { buttonClasses } from '@/components/ui/Button';
import { t } from '@/lib/i18n';
import { route } from '@/lib/route';

const known = [403, 404, 429, 500, 503] as const;

/** Error page for Inertia visits (full page loads use the Blade error views). */
export default function ErrorPage({ status }: { status: number }) {
    const code = (known as readonly number[]).includes(status) ? status : 500;

    return (
        <div className="ui-shell grid min-h-screen place-items-center px-4">
            <Head title={String(code)} />
            <div className="ui-card w-full max-w-md p-8 text-center">
                <p className="text-5xl font-black tracking-tight text-(--text)">{code}</p>
                <p className="mt-3 text-sm text-(--text-muted)">{t(`ui.error.${code}`)}</p>
                <a href={route('root')} className={buttonClasses('primary', 'md', 'mt-6')}>
                    {t('ui.error.home')}
                </a>
            </div>
        </div>
    );
}
