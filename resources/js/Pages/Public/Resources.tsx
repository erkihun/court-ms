import { Head } from '@inertiajs/react';
import { useState } from 'react';
import { type PublicResource, ResourceCard } from '@/components/public/ResourceCard';
import { cn } from '@/lib/cn';
import { t } from '@/lib/i18n';

type Filter = 'all' | PublicResource['type'];

const FILTERS: Filter[] = ['all', 'post', 'form', 'document', 'link'];

export default function PublicResources({ resources }: { resources: PublicResource[] }) {
    const [filter, setFilter] = useState<Filter>('all');
    const shown = filter === 'all' ? resources : resources.filter((resource) => resource.type === filter);

    return (
        <>
            <Head title={t('home.resources.page_title')} />

            <div className="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
                <div className="mb-10 max-w-2xl">
                    <p className="pub-ac-text mb-3 text-xs font-semibold tracking-[0.2em] uppercase">{t('home.resources.section_badge')}</p>
                    <h1 className="text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl">{t('home.resources.page_title')}</h1>
                    <p className="mt-3 text-base text-slate-500">{t('home.resources.page_description')}</p>
                </div>

                <div role="group" aria-label={t('home.resources.filter_label')} className="mb-8 flex flex-wrap gap-2">
                    {FILTERS.map((value) => (
                        <button
                            key={value}
                            type="button"
                            onClick={() => setFilter(value)}
                            aria-pressed={filter === value}
                            className={cn(
                                'rounded-xl border px-4 py-2 text-sm font-semibold transition',
                                filter === value ? 'pub-ac-btn border-transparent' : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50',
                            )}
                        >
                            {value === 'all' ? t('home.resources.filter_all') : t(`home.resources.types.${value}`)}
                        </button>
                    ))}
                </div>

                {shown.length === 0 ? (
                    <p className="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-16 text-center text-slate-500">{t('home.resources.empty')}</p>
                ) : (
                    <div className="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                        {shown.map((resource) => (
                            <ResourceCard key={resource.id} resource={resource} headingLevel="h2" />
                        ))}
                    </div>
                )}
            </div>
        </>
    );
}
