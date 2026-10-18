import { UserIcon } from '@heroicons/react/24/outline';
import { Head, Link, router, useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';
import { Detail, DocumentList, Empty, HearingList, Labelled, LetterList, Panel, SectionTitle } from '@/components/party/case/CaseSections';
import { CaseStatusPill, ReviewStatusPill, type ReviewStatus } from '@/components/party/StatusPill';
import { CopyButton } from '@/components/ui/CopyButton';
import { RichText } from '@/components/ui/RichText';
import { cn } from '@/lib/cn';
import { t } from '@/lib/i18n';
import { route } from '@/lib/route';

interface Props {
    case: {
        id: number;
        caseNumber: string;
        code: string | null;
        status: string;
        reviewStatus: ReviewStatus;
        reviewNote: string | null;
        title: string;
        caseType: string | null;
        filed: string;
        respondentName: string | null;
        respondentAddress: string | null;
        descriptionHtml: string;
        reliefHtml: string | null;
        canEdit: boolean;
    };
    applicantAddress: string | null;
    documents: Array<{ id: number; title: string; mime: string | null; sizeKb: number | null; created: string; descriptionHtml: string | null; downloadUrl: string | null }>;
    witnesses: Array<{ id: number; fullName: string; phone: string | null; email: string | null; address: string | null }>;
    hearings: Array<{ id: number; at: string; type: string | null; location: string | null; notes: string | null; icsUrl: string }>;
    letters: Array<{ id: number; title: string; status: string; reference: string | null; template: string | null; author: string | null; created: string; previewUrl: string }>;
    responses: Array<{ id: number; title: string; caseNumber: string; responseNumber: string | null; created: string; excerpt: string | null; href: string; pdfUrl: string | null }>;
    messages: Array<{ id: number; mine: boolean; author: string | null; at: string; body: string }>;
    audits: Array<{ id: number; at: string; action: string; actor: string; meta: string | null }>;
}

const reviewNotice: Partial<Record<ReviewStatus, string>> = {
    returned: 'border-yellow-200 bg-yellow-50 text-yellow-900',
    rejected: 'border-red-200 bg-red-50 text-red-900',
    awaiting_review: 'border-amber-200 bg-amber-50 text-amber-900',
};

const letterTone: Record<string, string> = {
    approved: 'border-emerald-200 bg-emerald-100 text-emerald-800',
    returned: 'border-amber-200 bg-amber-100 text-amber-800',
    rejected: 'border-rose-200 bg-rose-100 text-rose-800',
};

/** One of the applicant's cases with its documents, hearings, letters, responses and messages. */
export default function CaseShow(props: Props) {
    const { case: item, documents, witnesses, hearings, letters, responses, messages, audits } = props;
    const message = useForm({ body: '' });

    const send = (event: FormEvent) => {
        event.preventDefault();
        message.post(route('applicant.cases.messages.post', item.id), { preserveScroll: true, onSuccess: () => message.reset() });
    };

    return (
        <>
            <Head title={item.caseNumber} />
            <div className="case-typography grid grid-cols-1 gap-2 lg:grid-cols-3">
                <section className="overflow-hidden rounded-xl border border-(--border) bg-(--surface-strong) shadow-lg lg:col-span-3">
                    <div className="flex flex-wrap items-center justify-between gap-3 border-b border-(--border) px-5 py-4">
                        <div className="flex items-start gap-6 md:items-center">
                            <Labelled label={t('cases.case_number')}>
                                <span className="text-xl font-semibold tracking-tight text-(--text)">{item.caseNumber}</span>
                                <CopyButton value={item.caseNumber} />
                            </Labelled>
                            {item.code && (
                                <Labelled label={t('cases.access_code')}>
                                    <span className="text-lg font-semibold tracking-tight text-(--text)">{item.code}</span>
                                    <CopyButton value={item.code} />
                                </Labelled>
                            )}
                            <div className="flex flex-wrap items-center gap-2">
                                <CaseStatusPill status={item.status} withIcon={false} />
                                <ReviewStatusPill status={item.reviewStatus} />
                            </div>
                        </div>
                        <div className="flex flex-wrap items-center gap-2">
                            {item.canEdit && (
                                <Link
                                    href={route('applicant.cases.edit', item.id)}
                                    className="inline-flex items-center rounded-md border border-blue-200 bg-blue-50 px-3 py-1.5 text-xs font-medium text-blue-700 hover:bg-blue-100"
                                >
                                    {t('cases.edit_case')}
                                </Link>
                            )}
                            <a
                                href={route('applicant.cases.receipt.pdf', item.id)}
                                className="inline-flex items-center rounded-md border border-(--border) bg-(--surface-strong) px-3 py-1.5 text-xs font-medium text-(--text-muted) hover:bg-(--surface-soft)"
                            >
                                {t('cases.download_pdf')}
                            </a>
                            <button
                                type="button"
                                onClick={() => router.post(route('applicant.cases.receipt.email', item.id), {}, { preserveScroll: true })}
                                className="inline-flex items-center rounded-md border border-orange-300 bg-(--surface-strong) px-3 py-1.5 text-xs font-medium text-orange-700 hover:bg-orange-50"
                            >
                                {t('cases.email_pdf')}
                            </button>
                        </div>
                    </div>
                    {item.reviewNote && (
                        <div className="border-t border-(--border) px-5 py-3 text-xs leading-snug text-(--text-muted)">
                            <span className="font-semibold text-(--text)">{t('cases.reviewer_note')}</span> {item.reviewNote}
                            {item.reviewStatus === 'returned' && item.canEdit && (
                                <Link href={route('applicant.cases.edit', item.id)} className="ml-1 font-semibold text-blue-700 hover:underline">
                                    {t('cases.edit_your_case')}
                                </Link>
                            )}
                        </div>
                    )}
                </section>

                <section className="space-y-5 rounded-xl border border-(--border) bg-(--surface-strong) p-5 shadow-xs lg:col-span-2">
                    {reviewNotice[item.reviewStatus] && (
                        <div className={cn('rounded-lg border p-3', reviewNotice[item.reviewStatus])}>{t(`cases.review_messages.${item.reviewStatus}`)}</div>
                    )}

                    <div>
                        <SectionTitle>{t('cases.case_information')}</SectionTitle>
                        <dl className="grid gap-4 md:grid-cols-2">
                            <Detail label={t('cases.applicant_full_name')} value={item.title} />
                            <Detail label={t('cases.applicant_address')} value={props.applicantAddress ?? '-'} />
                            <Detail label={t('cases.case_type')} value={item.caseType ?? '-'} />
                            <Detail label={t('cases.filed')} value={item.filed} />
                        </dl>
                    </div>

                    {(item.respondentName || item.respondentAddress) && (
                        <div>
                            <SectionTitle>{t('cases.respondent_defendant')}</SectionTitle>
                            <dl className="grid gap-4 md:grid-cols-2">
                                <Detail label={t('cases.name')} value={item.respondentName ?? '—'} />
                                <Detail label={t('cases.address')} value={item.respondentAddress ?? '—'} />
                            </dl>
                        </div>
                    )}

                    <div>
                        <SectionTitle>{t('cases.case_details')}</SectionTitle>
                        <RichText html={item.descriptionHtml} className="text-(--text)" />
                    </div>

                    {item.reliefHtml && (
                        <div>
                            <SectionTitle>{t('cases.relief_requested')}</SectionTitle>
                            <RichText html={item.reliefHtml} className="text-(--text)" />
                        </div>
                    )}

                    <DocumentList documents={documents} />

                    <div>
                        <SectionTitle count={witnesses.length}>{t('cases.witnesses_section.title')}</SectionTitle>
                        {witnesses.length === 0 ? (
                            <Empty>{t('cases.no_witnesses_listed')}</Empty>
                        ) : (
                            <div className="-mx-2 overflow-x-auto md:mx-0">
                                <table className="min-w-full border-collapse">
                                    <thead>
                                        <tr className="bg-(--surface-soft) text-(--text-muted)">
                                            {['name', 'phone', 'email', 'address'].map((column) => (
                                                <th key={column} scope="col" className="border-b border-(--border) px-3 py-2 text-left font-medium">
                                                    {t(`cases.labels.${column}`)}
                                                </th>
                                            ))}
                                        </tr>
                                    </thead>
                                    <tbody className="divide-y divide-(--border)">
                                        {witnesses.map((witness) => (
                                            <tr key={witness.id} className="hover:bg-(--surface-soft)">
                                                <td className="px-3 py-2 font-medium text-(--text)">{witness.fullName}</td>
                                                <td className="px-3 py-2 text-(--text-muted)">{witness.phone ?? '—'}</td>
                                                <td className="px-3 py-2 text-(--text-muted)">
                                                    {witness.email ? (
                                                        <a href={`mailto:${witness.email}`} className="text-blue-700 hover:underline">
                                                            {witness.email}
                                                        </a>
                                                    ) : (
                                                        '—'
                                                    )}
                                                </td>
                                                <td className="px-3 py-2 text-(--text-muted)">{witness.address ?? '—'}</td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>
                        )}
                    </div>
                </section>

                <div className="space-y-2">
                    <HearingList hearings={hearings} />

                    <LetterList
                        letters={letters.map((letter) => ({
                            ...letter,
                            badge: (
                                <span className={cn('rounded-full border px-2 py-0.5 text-[11px]', letterTone[letter.status] ?? 'border-slate-200 bg-slate-100 text-slate-800')}>
                                    {t(`cases.letters_section.status.${letter.status}`)}
                                </span>
                            ),
                        }))}
                    />

                    <Panel title={t('cases.respondent_responses')} count={responses.length}>
                        {responses.length === 0 ? (
                            <Empty>{t('cases.respondent_responses_empty')}</Empty>
                        ) : (
                            <div className="max-h-64 space-y-3 overflow-y-auto pr-1">
                                {responses.map((response) => (
                                    <div key={response.id} className="rounded-lg border border-(--border) bg-(--surface-soft) p-3">
                                        <div className="flex items-start justify-between gap-2">
                                            <div className="space-y-1">
                                                <div className="font-semibold text-(--text)">{response.title}</div>
                                                <div className="text-xs text-(--text-muted)">
                                                    {t('cases.case_number')}: {response.caseNumber}
                                                </div>
                                                {response.responseNumber && (
                                                    <div className="text-xs font-semibold text-(--text-muted)">
                                                        {t('respondent.response_number_label')}: {response.responseNumber}
                                                    </div>
                                                )}
                                            </div>
                                            <div className="text-right text-xs text-(--text-subtle)">{response.created}</div>
                                        </div>
                                        {response.excerpt && <p className="mt-2 text-(--text-muted)">{response.excerpt}</p>}
                                        <div className="mt-3 flex flex-wrap items-center gap-2 text-xs">
                                            <Link href={response.href} className="inline-flex items-center rounded-md border border-blue-200 bg-blue-50 px-2 py-1 text-[11px] font-semibold text-blue-700 hover:bg-blue-100">
                                                {t('cases.view')}
                                            </Link>
                                            {response.pdfUrl && (
                                                <a href={response.pdfUrl} className="inline-flex items-center rounded-md border border-(--border) px-2 py-1 text-[11px] font-semibold text-(--text-muted) hover:bg-(--surface-strong)">
                                                    {t('cases.download_pdf')}
                                                </a>
                                            )}
                                        </div>
                                    </div>
                                ))}
                            </div>
                        )}
                    </Panel>

                    <aside className="rounded-2xl border border-(--border) bg-(--surface-strong) p-5 shadow-xs">
                        <div className="mb-4 flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <h3 className="font-semibold text-(--text)">{t('cases.messages_section.title')}</h3>
                                <p className="text-xs text-(--text-subtle)">{t('cases.messages_section.subtitle', { count: messages.length })}</p>
                            </div>
                            <span className="text-[11px] text-(--text-subtle)">
                                {messages.length} {t('cases.navigation.messages')}
                            </span>
                        </div>
                        <div className="max-h-80 space-y-3 overflow-auto pr-1.5">
                            {messages.length === 0 ? (
                                <Empty>{t('cases.no_messages_yet')}</Empty>
                            ) : (
                                messages.map((entry) => (
                                    <div key={entry.id} className={cn('flex', entry.mine ? 'justify-end' : 'justify-start')}>
                                        <div className={cn('w-full max-w-[80%] rounded-2xl border px-4 py-3 shadow-xs', entry.mine ? 'border-blue-200 bg-blue-50 text-right' : 'border-(--border) bg-(--surface-strong) text-left')}>
                                            <div className="flex items-start justify-between gap-2">
                                                <div className="flex items-center gap-3">
                                                    <div className="flex h-8 w-8 items-center justify-center rounded-full border border-(--border) bg-white text-[11px] font-semibold text-slate-600">
                                                        {entry.mine ? t('cases.you') : <UserIcon className="h-4 w-4 text-orange-500" />}
                                                    </div>
                                                    <div>
                                                        <p className="text-[11px] tracking-wide text-slate-500 uppercase">{entry.mine ? t('cases.you') : (entry.author ?? t('cases.court_staff'))}</p>
                                                        <p className="text-xs leading-tight font-semibold text-slate-800">{entry.at}</p>
                                                    </div>
                                                </div>
                                                <span className={cn('rounded-full border px-2 py-0.5 text-[11px]', entry.mine ? 'border-blue-200 bg-blue-100 text-blue-700' : 'border-orange-200 bg-orange-50 text-orange-700')}>
                                                    {entry.mine ? t('cases.sent') : t('cases.received')}
                                                </span>
                                            </div>
                                            <p className="mt-2 whitespace-pre-line text-slate-800">{entry.body}</p>
                                        </div>
                                    </div>
                                ))
                            )}
                        </div>
                        <form onSubmit={send} className="mt-4 space-y-3">
                            <label htmlFor="case-message" className="block text-xs font-semibold text-(--text-subtle)">
                                {t('cases.new_message')}
                            </label>
                            <textarea
                                id="case-message"
                                rows={4}
                                maxLength={5000}
                                value={message.data.body}
                                onChange={(event) => message.setData('body', event.target.value)}
                                placeholder={t('cases.write_message_placeholder')}
                                aria-invalid={message.errors.body ? true : undefined}
                                className="w-full rounded-2xl border border-(--border) bg-(--surface-strong) px-3 py-2 text-(--text) focus:border-blue-600 focus:ring-2 focus:ring-blue-600 focus:outline-hidden"
                                required
                            />
                            {message.errors.body && <p className="text-xs text-red-600">{message.errors.body}</p>}
                            <button
                                type="submit"
                                disabled={message.processing || message.data.body.trim() === ''}
                                className="w-full rounded-2xl bg-linear-to-r from-orange-500 to-rose-500 px-3 py-2 font-semibold text-white shadow-md transition hover:opacity-90 disabled:opacity-60"
                            >
                                {t('cases.send')}
                            </button>
                        </form>
                    </aside>

                    <Panel title={t('cases.case_audit_trail')} count={audits.length}>
                        {audits.length === 0 ? (
                            <Empty>{t('cases.no_audit_records')}</Empty>
                        ) : (
                            <div className="max-h-64 space-y-3 overflow-y-auto">
                                {audits.map((audit) => (
                                    <div key={audit.id} className="rounded-lg border border-(--border) bg-(--surface-soft) p-3">
                                        <div className="flex items-center gap-2 text-xs text-(--text-subtle)">
                                            <span>{audit.at}</span>
                                            <span className="rounded-full border border-(--border) bg-(--surface-strong) px-2 py-0.5 text-(--text-muted)">{audit.action}</span>
                                        </div>
                                        <div className="mt-1 text-[11px] text-(--text-muted)">
                                            {t('cases.audit_actor')} {audit.actor}
                                        </div>
                                        {audit.meta && <pre className="mt-2 rounded-sm border border-(--border) bg-(--surface-strong) px-2 py-1 text-[11px] whitespace-pre-wrap">{audit.meta}</pre>}
                                    </div>
                                ))}
                            </div>
                        )}
                    </Panel>
                </div>
            </div>
        </>
    );
}
