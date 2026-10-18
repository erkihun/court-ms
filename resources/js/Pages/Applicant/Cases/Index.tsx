import { EyeIcon, PlusIcon, TrashIcon } from '@heroicons/react/24/outline';
import { Head, Link, router } from '@inertiajs/react';
import { useState } from 'react';
import { CaseStatusPill, ReviewStatusPill, type ReviewStatus } from '@/components/party/StatusPill';
import { ConfirmDialog } from '@/components/ui/ConfirmDialog';
import { Pagination } from '@/components/ui/Pagination';
import { t } from '@/lib/i18n';
import { route } from '@/lib/route';
import type { Paginated } from '@/types';

interface CaseRow {
    id: number;
    caseNumber: string;
    title: string;
    caseType: string | null;
    status: string;
    reviewStatus: ReviewStatus;
    reviewNote: string | null;
    filed: string;
    canDelete: boolean;
}

/** The signed-in applicant's cases. */
export default function CasesIndex({ cases }: { cases: Paginated<CaseRow> }) {
    const [deleting, setDeleting] = useState<CaseRow | null>(null);
    const [processing, setProcessing] = useState(false);

    const destroy = () => {
        if (!deleting) return;
        router.delete(route('applicant.cases.destroy', deleting.id), {
            preserveScroll: true,
            onStart: () => setProcessing(true),
            onFinish: () => {
                setProcessing(false);
                setDeleting(null);
            },
        });
    };

    const headers = [t('cases.table.case_number'), t('cases.table.title'), t('cases.table.type'), t('cases.table.status'), t('cases.review_column'), t('cases.table.filed'), t('cases.table.actions')];

    return (
        <>
            <Head title={t('cases.my_cases')} />
            <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <h1 className="text-xl font-semibold text-(--text) sm:text-2xl">{t('cases.my_cases')}</h1>
                <Link
                    href={route('applicant.cases.create')}
                    className="inline-flex items-center gap-1.5 rounded-md bg-orange-500 px-3.5 py-2 font-medium text-white shadow-xs hover:bg-orange-600 focus:ring-2 focus:ring-orange-400 focus:ring-offset-1 focus:outline-hidden"
                >
                    <PlusIcon className="h-4 w-4" />
                    {t('cases.new_case')}
                </Link>
            </div>

            <div className="overflow-x-auto rounded-xl border border-(--border) bg-(--surface-strong) shadow-xs">
                <table className="min-w-full">
                    <thead className="bg-(--surface-soft)">
                        <tr className="border-b border-(--border)">
                            {headers.map((header) => (
                                <th key={header} scope="col" className="p-3 text-left text-[11px] font-semibold tracking-wide text-(--text-subtle) uppercase">
                                    {header}
                                </th>
                            ))}
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-(--border)">
                        {cases.data.length === 0 ? (
                            <tr>
                                <td colSpan={headers.length} className="p-6 text-center text-(--text-subtle)">
                                    {t('cases.table.no_cases_yet')}
                                </td>
                            </tr>
                        ) : (
                            cases.data.map((item) => (
                                <tr key={item.id} className="hover:bg-(--surface-soft)">
                                    <td className="p-3 font-mono text-xs text-(--text)">{item.caseNumber}</td>
                                    <td className="p-3 text-(--text)">
                                        <div className="line-clamp-2">{item.title}</div>
                                    </td>
                                    <td className="p-3 text-(--text-muted)">{item.caseType}</td>
                                    <td className="p-3">
                                        <CaseStatusPill status={item.status} withIcon={false} />
                                    </td>
                                    <td className="p-3">
                                        <div className="space-y-1">
                                            <ReviewStatusPill status={item.reviewStatus} />
                                            {item.reviewNote && <div className="line-clamp-2 text-[11px] leading-snug text-(--text-muted)">{item.reviewNote}</div>}
                                        </div>
                                    </td>
                                    <td className="p-3 whitespace-nowrap text-(--text-muted)">{item.filed}</td>
                                    <td className="p-3">
                                        <div className="flex items-center gap-2">
                                            <Link
                                                href={route('applicant.cases.show', item.id)}
                                                className="inline-flex items-center gap-1.5 rounded-md bg-blue-600 px-2.5 py-1 text-xs font-medium text-white hover:bg-blue-700"
                                            >
                                                <EyeIcon className="h-3.5 w-3.5" />
                                                {t('cases.table.view')}
                                            </Link>
                                            {item.canDelete && (
                                                <button
                                                    type="button"
                                                    onClick={() => setDeleting(item)}
                                                    className="inline-flex items-center gap-1.5 rounded-md bg-red-600 px-2.5 py-1 text-xs font-medium text-white hover:bg-red-700"
                                                >
                                                    <TrashIcon className="h-3.5 w-3.5" />
                                                    {t('cases.general.delete')}
                                                </button>
                                            )}
                                        </div>
                                    </td>
                                </tr>
                            ))
                        )}
                    </tbody>
                </table>
            </div>

            <Pagination meta={cases} />

            <ConfirmDialog
                open={deleting !== null}
                title={t('cases.general.delete')}
                message={deleting ? `${deleting.caseNumber} — ${t('cases.table.delete_confirm')}` : null}
                confirmLabel={t('cases.general.delete')}
                processing={processing}
                onCancel={() => setDeleting(null)}
                onConfirm={destroy}
            />
        </>
    );
}
