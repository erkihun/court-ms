import { Head } from '@inertiajs/react';
import { ReplyActions } from '@/components/party/ReplyActions';
import { ReviewStatusPill } from '@/components/party/StatusPill';
import { Pagination } from '@/components/ui/Pagination';
import { t } from '@/lib/i18n';
import type { Paginated } from '@/types';
import type { ResponseReplyData } from '@/types/party';

type Row = ResponseReplyData & { caseNumber: string; responseNumber: string | null };

/** Every reply this applicant has written to respondent responses. */
export default function ResponseRepliesIndex({ replies }: { replies: Paginated<Row> }) {
    return (
        <div className="space-y-6">
            <Head title={t('respondent.response_of_response')} />
            <div>
                <p className="text-xs font-semibold tracking-wide text-(--text-subtle) uppercase">{t('respondent.response_of_response')}</p>
                <h1 className="text-2xl font-semibold text-(--text)">{t('respondent.applicant_response_replies')}</h1>
            </div>

            {replies.data.length === 0 ? (
                <div className="rounded-xl border border-dashed border-(--border) bg-(--surface-strong) p-8 text-center text-sm text-(--text-subtle)">{t('respondent.no_applicant_response_replies')}</div>
            ) : (
                <>
                    <div className="overflow-x-auto rounded-2xl border border-(--border) bg-(--surface-strong) shadow-xs">
                        <table className="min-w-full divide-y divide-(--border) text-sm">
                            <thead className="bg-(--surface-soft) text-xs tracking-wide text-(--text-muted) uppercase">
                                <tr>
                                    <th scope="col" className="px-4 py-3 text-left font-semibold">{t('cases.case_number')}</th>
                                    <th scope="col" className="px-4 py-3 text-left font-semibold">{t('respondent.response_number_label')}</th>
                                    <th scope="col" className="px-4 py-3 text-left font-semibold">{t('cases.status.new_status')}</th>
                                    <th scope="col" className="px-4 py-3 text-left font-semibold">{t('cases.filed')}</th>
                                    <th scope="col" className="px-4 py-3 text-right font-semibold">{t('cases.labels.actions')}</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-(--border)">
                                {replies.data.map((reply) => (
                                    <tr key={reply.id}>
                                        <td className="px-4 py-3 font-semibold text-(--text)">{reply.caseNumber}</td>
                                        <td className="px-4 py-3 text-(--text-muted)">{reply.responseNumber ?? t('cases.labels.not_available')}</td>
                                        <td className="px-4 py-3">
                                            <ReviewStatusPill status={reply.reviewStatus} />
                                        </td>
                                        <td className="px-4 py-3 text-(--text-muted)">{reply.created}</td>
                                        <td className="px-4 py-3">
                                            <div className="flex justify-end">
                                                <ReplyActions reply={reply} />
                                            </div>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                    <Pagination meta={replies} />
                </>
            )}
        </div>
    );
}
