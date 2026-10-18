import { ArrowDownTrayIcon, ArrowTopRightOnSquareIcon, ClipboardDocumentListIcon, DocumentIcon, LinkIcon, NewspaperIcon } from '@heroicons/react/24/outline';
import type { ComponentType, SVGProps } from 'react';
import { cn } from '@/lib/cn';
import { t } from '@/lib/i18n';

export interface PublicResource {
    id: number;
    type: 'post' | 'form' | 'document' | 'link';
    title: string;
    description: string | null;
    imageUrl: string | null;
    featured: boolean;
    published: string | null;
    downloadUrl: string | null;
    externalUrl: string | null;
}

const TYPES: Record<PublicResource['type'], { icon: ComponentType<SVGProps<SVGSVGElement>>; color: string }> = {
    post: { icon: NewspaperIcon, color: 'text-violet-600' },
    form: { icon: ClipboardDocumentListIcon, color: 'text-emerald-600' },
    document: { icon: DocumentIcon, color: 'text-blue-600' },
    link: { icon: LinkIcon, color: 'text-orange-600' },
};

export function ResourceCard({ resource, headingLevel = 'h3' }: { resource: PublicResource; headingLevel?: 'h2' | 'h3' }) {
    const { icon: Icon, color } = TYPES[resource.type];
    const Heading = headingLevel;

    return (
        <article className="group flex flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white transition hover:border-slate-300 hover:shadow-xs">
            {resource.imageUrl && (
                <div className="h-40 overflow-hidden">
                    <img
                        src={resource.imageUrl}
                        alt=""
                        loading="lazy"
                        className="h-full w-full object-cover transition duration-300 group-hover:scale-105 motion-reduce:transition-none"
                    />
                </div>
            )}
            <div className="flex flex-1 flex-col p-5">
                <div className="mb-3 flex items-center gap-2">
                    <span className={cn('flex h-8 w-8 items-center justify-center rounded-xl bg-slate-100', color)}>
                        <Icon className="h-4 w-4" />
                    </span>
                    <span className="text-[10px] font-semibold tracking-widest text-slate-500 uppercase">{t(`home.resources.types.${resource.type}`)}</span>
                    {resource.featured && (
                        <span className="ms-auto rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-semibold text-amber-700">
                            {t('home.resources.featured')}
                        </span>
                    )}
                </div>
                <Heading className="mb-2 flex-1 text-sm leading-snug font-semibold text-slate-900">{resource.title}</Heading>
                {resource.description && <p className="mb-4 line-clamp-3 text-xs leading-relaxed text-slate-500">{resource.description}</p>}
                <div className="mt-auto flex items-center justify-between gap-2 border-t border-slate-100 pt-3">
                    <span className="text-[11px] text-slate-500">{resource.published}</span>
                    <div className="flex gap-3">
                        {resource.downloadUrl && (
                            <a
                                href={resource.downloadUrl}
                                download
                                className="pub-ac-text inline-flex items-center gap-1 text-xs font-semibold hover:opacity-80"
                            >
                                <ArrowDownTrayIcon className="h-3.5 w-3.5" />
                                {t('home.resources.download')}
                            </a>
                        )}
                        {resource.externalUrl && (
                            <a
                                href={resource.externalUrl}
                                target="_blank"
                                rel="noopener noreferrer"
                                className="pub-ac-text inline-flex items-center gap-1 text-xs font-semibold hover:opacity-80"
                            >
                                <ArrowTopRightOnSquareIcon className="h-3.5 w-3.5" />
                                {t('home.resources.visit')}
                            </a>
                        )}
                    </div>
                </div>
            </div>
        </article>
    );
}
