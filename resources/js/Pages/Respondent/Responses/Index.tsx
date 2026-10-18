import { Head, Link, router } from '@inertiajs/react';
import { useState } from 'react';
import { ReviewStatusPill, type ReviewStatus } from '@/components/party/StatusPill';
import { ConfirmDialog } from '@/components/ui/ConfirmDialog';
import { t } from '@/lib/i18n';
import { route } from '@/lib/route';

interface ResponseRow {
    id: number;
    title: string;
    caseNumber: string | null;
    reviewStatus: ReviewStatus;
    reviewNote: string | null;
    hasPdf: boolean;
    uploaded: string;
    locked: boolean;
}

/** The respondent's submitted responses. */
export default function ResponsesIndex({ responses }: { responses: ResponseRow[] }) {
    const [deleting, setDeleting] = useState<ResponseRow | null>(null);

    return (
        <div className="mx-auto w-full max-w-[1800px] space-y-8">
            <Head title={t('respondent.responses')} />
            <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p className="text-sm font-medium tracking-wide text-blue-600 uppercase">{t('respondent.responses')}</p>
                    <h1 className="text-3xl font-bold text-(--text)">{t('respondent.responses')}</h1>
                </div>
                <Link href={route('respondent.case.search')} className="inline-flex items-center gap-2 rounded-xl bg-blue-700 px-5 py-2.5 font-semibold text-white shadow-md hover:bg-blue-800">
                    {t('respondent.create_response')}
                </Link>
            </div>

            {responses.length === 0 ? (
                <div className="rounded-xl border-2 border-dashed border-(--border) bg-(--surface-strong) p-10 text-center text-(--text-subtle) shadow-xs">
                    <p className="text-lg font-semibold">{t('respondent.no_responses')}</p>
                    <p className="text-sm">{t('respondent.start_first_response')}</p>
                </div>
            ) : (
                <div className="overflow-hidden rounded-2xl border border-(--border) bg-(--surface-strong) shadow-xl">
                    <div className="overflow-x-auto">
                        <table className="min-w-full divide-y divide-(--border) text-sm">
                            <thead className="bg-(--surface-soft) text-xs tracking-wider text-(--text-muted) uppercase">
                                <tr>
                                    <th scope="col" className="w-1/4 px-6 py-3 text-left font-medium">{t('respondent.responses')}</th>
                                    <th scope="col" className="w-1/6 px-6 py-3 text-left font-medium">{t('respondent.review_status_label')}</th>
                                    <th scope="col" className="w-1/5 px-6 py-3 text-left font-medium">{t('respondent.case_number_label')}</th>
                                    <th scope="col" className="w-1/6 px-6 py-3 text-left font-medium">{t('respondent.uploaded')}</th>
                                    <th scope="col" className="px-6 py-3 text-right font-medium">{t('app.Actions')}</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-(--border) text-(--text)">
                                {responses.map((response) => (
                                    <tr key={response.id} className="hover:bg-blue-50/50">
                                        <td className="px-6 py-4">
                                            <div className="font-semibold">{response.title}</div>
                                            {response.reviewNote && (
                                                <div className="mt-1 text-xs text-(--text-muted)">
                                                    <span className="font-semibold text-(--text)">{t('cases.reviewer_note')}</span> {response.reviewNote}
                                                </div>
                                            )}
                                            {response.hasPdf && (
                                                <div className="mt-1 inline-flex items-center rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-semibold text-blue-800">{t('respondent.pdf_submitted')}</div>
                                            )}
                                        </td>
                                        <td className="px-6 py-4">
                                            <ReviewStatusPill status={response.reviewStatus} />
                                        </td>
                                        <td className="px-6 py-4 text-(--text-muted)">{response.caseNumber ?? t('cases.labels.not_available')}</td>
                                        <td className="px-6 py-4 text-(--text-muted)">{response.uploaded}</td>
                                        <td className="px-6 py-4">
                                            <div className="flex items-center justify-end gap-2">
                                                <Link
                                                    href={route('respondent.responses.show', response.id)}
                                                    className="inline-flex items-center rounded-full border border-(--border) px-3 py-1.5 text-xs font-semibold text-blue-700 hover:bg-blue-50"
                                                >
                                                    {t('respondent.view_details')}
                                                </Link>
                                                {!response.locked && (
                                                    <>
                                                        <Link
                                                            href={route('respondent.responses.edit', response.id)}
                                                            className="inline-flex items-center rounded-full border border-amber-200 bg-amber-50 px-3 py-1.5 text-xs font-semibold text-amber-700 hover:bg-amber-100"
                                                        >
                                                            {t('respondent.edit_response')}
                                                        </Link>
                                                        <button
                                                            type="button"
                                                            onClick={() => setDeleting(response)}
                                                            className="inline-flex items-center rounded-full border border-red-200 bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-700 hover:bg-red-100"
                                                        >
                                                            {t('respondent.delete')}
                                                        </button>
                                                    </>
                                                )}
                                            </div>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                </div>
            )}

            <ConfirmDialog
                open={deleting !== null}
                title={t('respondent.delete')}
                message={deleting?.title}
                confirmLabel={t('respondent.delete')}
                onCancel={() => setDeleting(null)}
                onConfirm={() => deleting && router.delete(route('respondent.responses.destroy', deleting.id), { onFinish: () => setDeleting(null) })}
            />
        </div>
    );
}
