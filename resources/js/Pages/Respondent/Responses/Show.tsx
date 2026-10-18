import { Head, Link } from '@inertiajs/react';
import { ReviewStatusPill, type ReviewStatus } from '@/components/party/StatusPill';
import { RichText } from '@/components/ui/RichText';
import { t } from '@/lib/i18n';
import { route } from '@/lib/route';

interface Props {
    response: {
        id: number;
        title: string;
        reviewStatus: ReviewStatus;
        reviewNote: string | null;
        created: string;
        responseNumber: string | null;
        caseNumber: string | null;
        descriptionHtml: string | null;
        pdf: { downloadUrl: string; inlineUrl: string } | null;
        locked: boolean;
    };
}

/** One of the respondent's responses, with its PDF inline. */
export default function ResponseShow({ response }: Props) {
    return (
        <div className="mx-auto w-full max-w-[1800px] space-y-6 rounded-2xl border border-(--border) bg-(--surface-strong) p-6 shadow-sm">
            <Head title={t('respondent.response_title', { title: response.title })} />

            <div className="flex flex-col gap-1">
                <p className="text-xs tracking-wide text-(--text-subtle) uppercase">{t('respondent.response_title_label')}</p>
                <div className="flex flex-wrap items-center gap-2">
                    <h1 className="text-2xl font-semibold text-(--text)">{response.title}</h1>
                    <ReviewStatusPill status={response.reviewStatus} />
                </div>
                <p className="text-sm text-(--text-subtle)">{response.created}</p>
                {response.responseNumber && (
                    <p className="text-xs font-semibold text-(--text-muted)">
                        {t('respondent.response_number_label')}: {response.responseNumber}
                    </p>
                )}
                {response.caseNumber && (
                    <p className="text-xs text-(--text-subtle)">
                        {t('respondent.case_number_label')}: {response.caseNumber}
                    </p>
                )}
            </div>

            {response.descriptionHtml && (
                <div className="space-y-2">
                    <h2 className="text-base font-semibold text-(--text)">{t('respondent.description_label')}</h2>
                    <RichText html={response.descriptionHtml} className="text-(--text-muted)" />
                </div>
            )}

            {response.reviewNote && (
                <div className="text-sm text-(--text-muted)">
                    <span className="font-semibold text-(--text)">{t('cases.reviewer_note')}</span> {response.reviewNote}
                </div>
            )}

            {response.pdf && (
                <div className="space-y-3 rounded-xl border border-(--border) bg-(--surface-soft) p-4">
                    <div className="flex items-center justify-between">
                        <p className="text-sm font-semibold text-(--text)">{t('respondent.response_pdf')}</p>
                        <a href={response.pdf.downloadUrl} className="text-sm text-blue-700 hover:underline">
                            {t('respondent.download')}
                        </a>
                    </div>
                    <div className="overflow-hidden rounded-lg border border-(--border) bg-white">
                        <iframe src={`${response.pdf.inlineUrl}#toolbar=0&navpanes=0&scrollbar=0`} loading="lazy" className="min-h-[420px] w-full" title={response.title} />
                    </div>
                </div>
            )}

            <div className="flex justify-end gap-2">
                {!response.locked && (
                    <Link href={route('respondent.responses.edit', response.id)} className="inline-flex items-center rounded-full border border-amber-200 bg-amber-50 px-4 py-2 text-sm font-semibold text-amber-700 hover:bg-amber-100">
                        {t('respondent.edit_response')}
                    </Link>
                )}
                <Link href={route('respondent.responses.index')} className="inline-flex items-center rounded-full border border-(--border) px-4 py-2 text-sm font-semibold text-(--text-muted) hover:bg-(--surface-soft)">
                    {t('respondent.back_to_responses')}
                </Link>
            </div>
        </div>
    );
}
