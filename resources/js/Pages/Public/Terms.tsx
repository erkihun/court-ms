import { Head } from '@inertiajs/react';
import { RichText } from '@/components/ui/RichText';

/** Published terms and conditions; the HTML is purified on the server. */
export default function PublicTerms({ title, published, bodyHtml }: { title: string; published: string | null; bodyHtml: string }) {
    return (
        <>
            <Head title={title} />
            <div className="bg-slate-50 px-4 py-10 sm:px-6 sm:py-16">
                <article className="mx-auto max-w-3xl rounded-2xl border border-slate-200 bg-white p-6 shadow-xs md:p-10">
                    <h1 className="text-3xl font-semibold text-slate-900">{title}</h1>
                    {published && <p className="mt-1 text-sm text-slate-500">{published}</p>}
                    <RichText html={bodyHtml} className="pub-prose mt-6" />
                </article>
            </div>
        </>
    );
}
