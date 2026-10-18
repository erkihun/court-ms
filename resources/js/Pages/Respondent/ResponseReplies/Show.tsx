import { Head, Link } from '@inertiajs/react';
import { t } from '@/lib/i18n';
import { route } from '@/lib/route';

interface Props {
    reply: {
        id: number;
        caseNumber: string;
        responseNumber: string | null;
        applicantName: string | null;
        description: string;
        downloadUrl: string;
        inlineUrl: string;
    };
}

/** One accepted applicant reply, with its PDF. */
export default function ResponseReplyShow({ reply }: Props) {
    const notAvailable = t('cases.labels.not_available');
    const facts: Array<[string, string, boolean?]> = [
        [t('cases.case_number'), reply.caseNumber],
        [t('respondent.response_number_label'), reply.responseNumber ?? notAvailable],
        [t('cases.applicant_full_name'), reply.applicantName ?? notAvailable, true],
    ];

    return (
        <div className="mx-auto w-full max-w-[1600px] space-y-6">
            <Head title={t('respondent.response_of_response')} />
            <div className="rounded-2xl border border-(--border) bg-(--surface-strong) p-6 shadow-xs">
                <div className="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <p className="text-xs font-semibold tracking-wide text-(--text-subtle) uppercase">{t('respondent.response_of_response')}</p>
                        <h1 className="mt-1 text-2xl font-semibold text-(--text)">{t('respondent.response_of_response')}</h1>
                    </div>
                    <Link href={route('respondent.response-replies.index')} className="inline-flex items-center rounded-lg border border-(--border) px-4 py-2 text-sm font-semibold text-(--text-muted) hover:bg-(--surface-soft)">
                        {t('respondent.back_to_responses')}
                    </Link>
                </div>
                <dl className="mt-6 grid gap-4 sm:grid-cols-2">
                    {facts.map(([label, value, wide]) => (
                        <div key={label} className={`rounded-xl border border-(--border) bg-(--surface-soft) p-4 ${wide ? 'sm:col-span-2' : ''}`}>
                            <dt className="text-xs font-semibold tracking-wide text-(--text-subtle) uppercase">{label}</dt>
                            <dd className="mt-1 text-sm font-semibold text-(--text)">{value}</dd>
                        </div>
                    ))}
                </dl>
            </div>

            <div className="rounded-2xl border border-(--border) bg-(--surface-strong) p-6 shadow-xs">
                <h2 className="text-lg font-semibold text-(--text)">{t('respondent.response_of_response_description')}</h2>
                <div className="mt-3 text-sm whitespace-pre-line text-(--text-muted)">{reply.description}</div>
            </div>

            <div className="rounded-2xl border border-(--border) bg-(--surface-strong) p-6 shadow-xs">
                <div className="flex items-center justify-between gap-3">
                    <h2 className="text-lg font-semibold text-(--text)">{t('cases.attachment')}</h2>
                    <a href={reply.downloadUrl} className="text-sm font-semibold text-blue-700 hover:underline">
                        {t('respondent.download')}
                    </a>
                </div>
                <iframe src={reply.inlineUrl} className="mt-4 min-h-[80vh] w-full rounded-xl border border-(--border)" title={t('respondent.response_of_response')} />
            </div>
        </div>
    );
}
