import { BoltIcon, BriefcaseIcon, ChatBubbleLeftRightIcon, ClockIcon, DocumentTextIcon, EnvelopeIcon, MagnifyingGlassIcon } from '@heroicons/react/24/outline';
import { Head, Link } from '@inertiajs/react';
import { type ComponentType, type ReactNode, type SVGProps, useState } from 'react';
import { tintStyle } from '@/components/party/StatusPill';
import { cn } from '@/lib/cn';
import { t } from '@/lib/i18n';
import { route } from '@/lib/route';

type Panel = 'recent' | 'letters' | 'responseReplies';

interface Props {
    stats: { cases: number; responses: number; responseReplies: number; notifications: number };
    recentCases: Array<{ caseNumber: string; title: string | null; status: string | null; href: string }>;
    letters: Array<{
        id: number;
        title: string;
        caseNumber: string | null;
        referenceNumber: string | null;
        templateTitle: string | null;
        authorName: string | null;
        date: string;
        previewUrl: string;
    }>;
    responseReplies: Array<{ id: number; caseNumber: string | null; responseNumber: string | null; applicantName: string | null; href: string }>;
}

export default function RespondentDashboard({ stats, recentCases, letters, responseReplies }: Props) {
    const [panel, setPanel] = useState<Panel>('recent');
    const notAvailable = t('cases.labels.not_available');

    const toggles: Array<{ panel: Panel; label: string; Icon: ComponentType<SVGProps<SVGSVGElement>>; count?: number }> = [
        { panel: 'recent', label: t('respondent.recent_cases'), Icon: ClockIcon },
        { panel: 'letters', label: t('respondent.letters'), Icon: EnvelopeIcon },
        { panel: 'responseReplies', label: t('respondent.response_of_response'), Icon: DocumentTextIcon, count: stats.responseReplies },
    ];

    return (
        <div className="mx-auto w-full max-w-[1800px] space-y-6">
            <Head title={t('respondent.dashboard')} />

            <div className="rounded-2xl p-5 text-white shadow-lg sm:p-6" style={{ background: 'linear-gradient(135deg, rgb(var(--ac)), var(--primary-strong))' }}>
                <div className="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div className="min-w-0">
                        <h1 className="text-xl font-bold sm:text-2xl">{t('respondent.dashboard_title')}</h1>
                        <p className="mt-1 text-sm text-white/80">{t('respondent.welcome_back')}</p>
                    </div>
                    <div className="grid grid-cols-3 gap-2 sm:gap-3 lg:w-2/5">
                        {[
                            [t('respondent.my_cases'), stats.cases],
                            [t('respondent.my_responses'), stats.responses],
                            [t('respondent.notifications'), stats.notifications],
                        ].map(([label, value]) => (
                            <div key={label} className="rounded-xl bg-white/15 px-3 py-2.5 backdrop-blur-xs">
                                <div className="text-[11px] tracking-wide text-white/70 uppercase">{label}</div>
                                <div className="mt-1 text-xl font-bold">{value}</div>
                            </div>
                        ))}
                    </div>
                </div>
            </div>

            <div className="grid gap-6 lg:grid-cols-[3fr_9fr]">
                <div className="ui-card ui-card-body">
                    <div className="mb-5 flex items-center gap-3">
                        <div className="dash-stat-icon" style={{ background: 'rgb(var(--ac) / 0.12)', color: 'rgb(var(--ac))' }}>
                            <BoltIcon className="h-5 w-5" />
                        </div>
                        <div>
                            <div className="text-[11px] font-semibold tracking-wider text-(--text-subtle) uppercase">{t('respondent.quick_actions')}</div>
                            <h2 className="text-base font-semibold text-(--text)">{t('respondent.dashboard')}</h2>
                        </div>
                    </div>
                    <div className="space-y-2.5">
                        <Link href={route('respondent.case.search')} className="dash-action dash-action-primary">
                            <MagnifyingGlassIcon className="h-5 w-5 shrink-0" />
                            <span>{t('respondent.find_case')}</span>
                        </Link>
                        <Link href={route('respondent.responses.index')} className="dash-action">
                            <ChatBubbleLeftRightIcon className="h-5 w-5 shrink-0" />
                            <span>{t('respondent.my_responses')}</span>
                        </Link>
                        <Link href={route('respondent.cases.my')} className="dash-action">
                            <BriefcaseIcon className="h-5 w-5 shrink-0" />
                            <span>{t('respondent.my_cases')}</span>
                        </Link>
                    </div>
                    <div className="mt-3 space-y-2.5 border-t border-(--border) pt-3">
                        {toggles.map((toggle) => (
                            <button
                                key={toggle.panel}
                                type="button"
                                onClick={() => setPanel(toggle.panel)}
                                className={cn('dash-action', panel === toggle.panel && 'dash-action-active')}
                                aria-pressed={panel === toggle.panel}
                            >
                                <toggle.Icon className="h-5 w-5 shrink-0" />
                                <span>{toggle.label}</span>
                                {toggle.count !== undefined && <span className="dash-action-count">{toggle.count}</span>}
                            </button>
                        ))}
                    </div>
                </div>

                <div className="ui-card ui-card-body">
                    {panel === 'recent' && (
                        <Section
                            eyebrow={t('respondent.my_cases')}
                            title={t('respondent.case_found')}
                            action={<Link href={route('respondent.cases.my')} className="text-sm font-medium text-[rgb(var(--ac))]">{t('respondent.view_case_details')}</Link>}
                            empty={t('respondent.no_notifications')}
                        >
                            {recentCases.map((item) => (
                                <div key={item.caseNumber} className="dash-list-item flex items-start justify-between gap-3">
                                    <div className="min-w-0">
                                        <div className="text-sm font-semibold text-(--text)">{item.caseNumber}</div>
                                        <div className="truncate text-xs text-(--text-muted)">{item.title ?? '-'}</div>
                                        <div className="mt-1 text-[11px] text-(--text-subtle)">
                                            {t('respondent.status_label')}: {item.status ? t(`cases.status.${item.status}`) : '-'}
                                        </div>
                                    </div>
                                    <Link href={item.href} className="shrink-0 text-sm font-medium text-[rgb(var(--ac))]">
                                        {t('respondent.view_case_details')}
                                    </Link>
                                </div>
                            ))}
                        </Section>
                    )}

                    {panel === 'letters' && (
                        <Section
                            eyebrow={t('respondent.letters')}
                            title={t('respondent.recent_approved_letters')}
                            action={
                                <button type="button" onClick={() => setPanel('recent')} className="text-sm font-medium text-[rgb(var(--ac))]">
                                    {t('respondent.view_case_details')}
                                </button>
                            }
                            empty={t('respondent.no_letters_available')}
                        >
                            {letters.map((letter) => (
                                <div key={letter.id} className="dash-list-item flex items-start justify-between gap-3">
                                    <div className="min-w-0 space-y-1">
                                        <div className="flex items-center gap-2">
                                            <div className="text-sm font-semibold text-(--text)">{letter.title}</div>
                                            <span className="rounded-full border px-2 py-0.5 text-[11px]" style={tintStyle('var(--success)')}>
                                                {t('respondent.approved')}
                                            </span>
                                        </div>
                                        <div className="flex flex-wrap gap-3 text-xs text-(--text-muted)">
                                            <span>
                                                {t('respondent.case_label')}: {letter.caseNumber ?? '-'}
                                            </span>
                                            <span>
                                                {t('respondent.reference_label')}: {letter.referenceNumber ?? '-'}
                                            </span>
                                            <span>
                                                {t('respondent.template_label')}: {letter.templateTitle ?? '-'}
                                            </span>
                                            <span>
                                                {t('respondent.author_label')}: {letter.authorName ?? '-'}
                                            </span>
                                        </div>
                                        <div className="text-[11px] text-(--text-subtle)">{letter.date}</div>
                                    </div>
                                    <a href={letter.previewUrl} target="_blank" rel="noopener" className="shrink-0 text-sm font-medium text-[rgb(var(--ac))]">
                                        {t('respondent.view_details')}
                                    </a>
                                </div>
                            ))}
                        </Section>
                    )}

                    {panel === 'responseReplies' && (
                        <Section
                            eyebrow={t('respondent.response_of_response')}
                            title={t('respondent.applicant_response_replies')}
                            action={<Link href={route('respondent.response-replies.index')} className="text-sm font-medium text-[rgb(var(--ac))]">{t('respondent.view_all')}</Link>}
                            empty={t('respondent.no_applicant_response_replies')}
                        >
                            {responseReplies.map((reply) => (
                                <div key={reply.id} className="dash-list-item flex items-start justify-between gap-3">
                                    <div className="min-w-0 space-y-1">
                                        <div className="text-sm font-semibold text-(--text)">{reply.caseNumber ?? '-'}</div>
                                        <div className="text-xs text-(--text-muted)">
                                            {t('respondent.response_number_label')}: {reply.responseNumber ?? notAvailable}
                                        </div>
                                        <div className="text-xs text-(--text-subtle)">
                                            {t('cases.applicant_full_name')}: {reply.applicantName ?? notAvailable}
                                        </div>
                                    </div>
                                    <Link href={reply.href} className="shrink-0 text-sm font-medium text-[rgb(var(--ac))]">
                                        {t('respondent.view_details')}
                                    </Link>
                                </div>
                            ))}
                        </Section>
                    )}
                </div>
            </div>
        </div>
    );
}

function Section({ eyebrow, title, action, empty, children }: { eyebrow: string; title: string; action: ReactNode; empty: string; children: ReactNode[] }) {
    return (
        <div>
            <div className="mb-4 flex items-center justify-between">
                <div>
                    <div className="text-[11px] font-semibold tracking-wider text-(--text-subtle) uppercase">{eyebrow}</div>
                    <h2 className="text-base font-semibold text-(--text)">{title}</h2>
                </div>
                {action}
            </div>
            {children.length > 0 ? <div className="space-y-2.5">{children}</div> : <div className="dash-empty">{empty}</div>}
        </div>
    );
}
