import { PrinterIcon } from '@heroicons/react/24/outline';
import { Head } from '@inertiajs/react';
import { RichText } from '@/components/ui/RichText';
import { t } from '@/lib/i18n';

/** CMS user manual; the HTML is purified on the server. */
export default function PublicUserManual({ title, contentHtml }: { title: string; contentHtml: string }) {
    return (
        <>
            <Head title={title} />
            <div className="bg-slate-50 px-4 py-10 sm:px-6 sm:py-16">
                <article className="pub-print-plain mx-auto max-w-5xl overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-xs">
                    <header className="flex flex-wrap items-end justify-between gap-4 bg-linear-to-r from-slate-950 to-blue-950 px-6 py-10 text-white sm:px-10">
                        <div>
                            <p className="text-xs font-semibold tracking-[0.2em] text-blue-300 uppercase">{t('home.nav.user_manual')}</p>
                            <h1 className="mt-3 text-3xl font-bold sm:text-4xl">{title}</h1>
                        </div>
                        <button
                            type="button"
                            onClick={() => window.print()}
                            className="pub-no-print inline-flex items-center gap-2 rounded-xl border border-white/20 bg-white/10 px-4 py-2 text-sm font-semibold hover:bg-white/20"
                        >
                            <PrinterIcon className="h-4 w-4" />
                            {t('home.accessibility.print_page')}
                        </button>
                    </header>
                    <div className="px-6 py-8 sm:px-10 sm:py-12">
                        <RichText html={contentHtml} className="pub-prose" />
                    </div>
                </article>
            </div>
        </>
    );
}
