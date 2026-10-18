import { Head, Link } from '@inertiajs/react';
import { Pagination } from '@/components/ui/Pagination';
import { t } from '@/lib/i18n';
import { route } from '@/lib/route';
import type { Paginated } from '@/types';

interface ReplyRow {
    id: number;
    caseNumber: string;
    responseNumber: string | null;
    applicantName: string | null;
    uploaded: string;
}

/** Applicants' replies to this respondent's responses, once the court accepted them. */
export default function ResponseRepliesIndex({ replies }: { replies: Paginated<ReplyRow> }) {
    const notAvailable = t('cases.labels.not_available');

    return (
        <div className="mx-auto w-full max-w-[1800px] space-y-6">
            <Head title={t('respondent.response_of_response')} />
            <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p className="text-sm tracking-wide text-(--text-subtle) uppercase">{t('respondent.quick_actions')}</p>
                    <h1 className="text-2xl font-semibold text-(--text)">{t('respondent.response_of_response')}</h1>
                </div>
                <Link href={route('respondent.dashboard')} className="inline-flex items-center rounded-full border border-(--border) px-4 py-2 text-sm font-semibold text-(--text-muted) hover:bg-(--surface-soft)">
                    {t('respondent.dashboard')}
                </Link>
            </div>

            {replies.data.length === 0 ? (
                <div className="rounded-xl border border-dashed border-(--border) bg-(--surface-strong) p-8 text-center text-sm text-(--text-subtle)">{t('respondent.no_applicant_response_replies')}</div>
            ) : (
                <>
                    <div className="overflow-hidden rounded-2xl border border-(--border) bg-(--surface-strong) shadow-xl">
                        <div className="overflow-x-auto">
                            <table className="min-w-full divide-y divide-(--border) text-sm">
                                <thead className="bg-(--surface-soft) text-xs tracking-wider text-(--text-muted) uppercase">
                                    <tr>
                                        <th scope="col" className="px-6 py-3 text-left font-medium">{t('cases.case_number')}</th>
                                        <th scope="col" className="px-6 py-3 text-left font-medium">{t('respondent.response_number_label')}</th>
                                        <th scope="col" className="px-6 py-3 text-left font-medium">{t('cases.applicant_full_name')}</th>
                                        <th scope="col" className="px-6 py-3 text-left font-medium">{t('respondent.uploaded')}</th>
                                        <th scope="col" className="px-6 py-3 text-right font-medium">{t('cases.labels.actions')}</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-(--border) text-(--text)">
                                    {replies.data.map((reply) => (
                                        <tr key={reply.id} className="hover:bg-(--surface-soft)">
                                            <td className="px-6 py-4 font-semibold">{reply.caseNumber}</td>
                                            <td className="px-6 py-4 text-(--text-muted)">{reply.responseNumber ?? notAvailable}</td>
                                            <td className="px-6 py-4 text-(--text-muted)">{reply.applicantName ?? notAvailable}</td>
                                            <td className="px-6 py-4 text-(--text-muted)">{reply.uploaded}</td>
                                            <td className="px-6 py-4">
                                                <div className="flex justify-end">
                                                    <Link
                                                        href={route('respondent.response-replies.show', reply.id)}
                                                        className="inline-flex items-center rounded-full border border-(--border) px-3 py-1.5 text-xs font-semibold text-blue-700 hover:bg-blue-50"
                                                    >
                                                        {t('respondent.view_details')}
                                                    </Link>
                                                </div>
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <Pagination meta={replies} />
                </>
            )}
        </div>
    );
}
