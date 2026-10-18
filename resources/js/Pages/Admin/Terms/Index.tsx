import { ArrowTopRightOnSquareIcon, DocumentTextIcon, PlusIcon } from '@heroicons/react/24/outline';
import { Head } from '@inertiajs/react';
import { PublishedBadge } from '@/components/admin/content/PublishedBadge';
import { LinkButton } from '@/components/ui/Button';
import { ConfirmButton } from '@/components/ui/ConfirmButton';
import { EmptyState } from '@/components/ui/EmptyState';
import { PageHeader } from '@/components/ui/PageHeader';
import { Pagination } from '@/components/ui/Pagination';
import { Table } from '@/components/ui/Table';
import { t } from '@/lib/i18n';
import { route } from '@/lib/route';
import type { Paginated } from '@/types';

interface TermRow {
    id: number;
    title: string;
    excerpt: string;
    isPublished: boolean;
    published: string | null;
    editedRelative: string;
    updatedDate: string;
    updatedTime: string;
}

interface Props {
    terms: Paginated<TermRow>;
    publicUrl: string;
}

export default function TermsIndex({ terms, publicUrl }: Props) {
    const publishedOnPage = terms.data.filter((term) => term.isPublished).length;
    const createButton = (
        <LinkButton href={route('terms.create')}>
            <PlusIcon className="h-4 w-4" />
            {t('terms.create_new')}
        </LinkButton>
    );

    return (
        <>
            <Head title={t('terms.title')} />
            <PageHeader
                title={t('terms.title')}
                description={
                    <>
                        {t('terms.manage_description')} {t('terms.public_page_hint')}
                    </>
                }
                className="mb-6"
                actions={
                    <>
                        <LinkButton href={publicUrl} external target="_blank" variant="secondary">
                            <ArrowTopRightOnSquareIcon className="h-4 w-4" />
                            {t('terms.preview_public')}
                        </LinkButton>
                        {createButton}
                    </>
                }
            >
                <dl className="mt-4 grid max-w-md grid-cols-2 gap-3 text-sm">
                    <PageCount label={t('terms.published_count')} value={publishedOnPage} />
                    <PageCount label={t('terms.drafts_count')} value={terms.data.length - publishedOnPage} />
                </dl>
            </PageHeader>

            {terms.data.length === 0 ? (
                <EmptyState icon={<DocumentTextIcon className="h-6 w-6" />} title={t('terms.empty_state')} description={t('terms.empty_state_help')}>
                    {createButton}
                </EmptyState>
            ) : (
                <Table
                    head={
                        <tr>
                            <th scope="col">{t('terms.table_title')}</th>
                            <th scope="col">{t('terms.table_published')}</th>
                            <th scope="col">{t('terms.table_updated')}</th>
                            <th scope="col">
                                <span className="sr-only">{t('terms.table_actions')}</span>
                            </th>
                        </tr>
                    }
                    footer={terms.last_page > 1 ? <Pagination meta={terms} /> : undefined}
                >
                    {terms.data.map((term) => (
                        <tr key={term.id} className="align-top">
                            <td className="max-w-md">
                                <div className="font-semibold text-(--text)">{term.title}</div>
                                <p className="mt-1 text-xs leading-relaxed text-(--text-subtle)">{term.excerpt}</p>
                            </td>
                            <td>
                                <PublishedBadge published={term.isPublished} />
                                <div className="mt-1 text-xs text-(--text-subtle)">
                                    {term.isPublished ? term.published : t('terms.last_edited', { when: term.editedRelative })}
                                </div>
                            </td>
                            <td className="whitespace-nowrap">
                                <div className="font-medium text-(--text)">{term.updatedDate}</div>
                                <div className="text-xs text-(--text-subtle)">{term.updatedTime}</div>
                            </td>
                            <td className="whitespace-nowrap">
                                <div className="flex justify-end gap-2">
                                    <LinkButton href={route('terms.edit', term.id)} variant="secondary" size="sm">
                                        {t('terms.action_edit')}
                                    </LinkButton>
                                    <ConfirmButton url={route('terms.destroy', term.id)} title={t('terms.confirm_delete')} message={term.title}>
                                        {t('terms.action_delete')}
                                    </ConfirmButton>
                                </div>
                            </td>
                        </tr>
                    ))}
                </Table>
            )}
        </>
    );
}

function PageCount({ label, value }: { label: string; value: number }) {
    return (
        <div className="rounded-lg border border-(--border) bg-(--surface-soft) px-4 py-2">
            <dt className="text-[11px] tracking-wide text-(--text-subtle) uppercase">{t('terms.on_this_page')}</dt>
            <dd className="mt-0.5 flex items-center justify-between">
                <span className="font-medium text-(--text-muted)">{label}</span>
                <span className="text-lg font-bold text-(--text)">{value}</span>
            </dd>
        </div>
    );
}
