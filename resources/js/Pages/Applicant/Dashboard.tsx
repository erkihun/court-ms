import {
    ArrowDownTrayIcon,
    ArrowRightIcon,
    BoltIcon,
    BriefcaseIcon,
    ChatBubbleBottomCenterTextIcon,
    CheckIcon,
    ChevronRightIcon,
    ClockIcon,
    DocumentTextIcon,
    EnvelopeIcon,
    InformationCircleIcon,
    PlusIcon,
    ScaleIcon,
    CheckCircleIcon,
} from '@heroicons/react/24/outline';
import { Head, Link } from '@inertiajs/react';
import { type ComponentType, type ReactNode, type SVGProps, useState } from 'react';
import { CaseStatusPill, caseStatusColor, ReviewStatusPill, type ReviewStatus, tintStyle } from '@/components/party/StatusPill';
import { cn } from '@/lib/cn';
import { t } from '@/lib/i18n';
import { route } from '@/lib/route';

type Icon = ComponentType<SVGProps<SVGSVGElement>>;
type Panel = 'recent' | 'letters' | 'responses' | 'responseReplies' | 'decisions';

interface Props {
    greetingName: string;
    today: string;
    stats: { total: number; pending: number; active: number; closed: number };
    counts: { letters: number; responses: number; decisions: number; responseReplies: number };
    recent: Array<{ id: number; caseNumber: string; title: string; status: string; caseType: string | null; created: string; createdRelative: string }>;
    letters: Array<{ id: number; title: string; caseNumber: string | null; referenceNumber: string | null; date: string; previewUrl: string }>;
    responses: Array<{ id: number; title: string; caseNumber: string | null; when: string; href: string | null }>;
    responseReplies: Array<{ id: number; caseNumber: string; responseNumber: string | null; status: ReviewStatus; reviewNote: string | null; date: string; href: string }>;
    decisions: Array<{ id: number; name: string; caseNumber: string | null; date: string; status: string | null; downloadUrl: string | null }>;
}

export default function ApplicantDashboard(props: Props) {
    const { greetingName, today, stats, counts } = props;
    const [panel, setPanel] = useState<Panel>('recent');
    const pct = (value: number) => (stats.total > 0 ? Math.round((value / stats.total) * 100) : 0);

    const actions: Array<{ panel: Panel; label: string; Icon: Icon; count?: number }> = [
        { panel: 'recent', label: t('dashboard.recent_cases'), Icon: BriefcaseIcon },
        { panel: 'letters', label: t('dashboard.letters'), Icon: EnvelopeIcon, count: counts.letters },
        { panel: 'responses', label: t('dashboard.responses'), Icon: ChatBubbleBottomCenterTextIcon, count: counts.responses },
        { panel: 'responseReplies', label: t('respondent.response_of_response'), Icon: DocumentTextIcon, count: counts.responseReplies },
        { panel: 'decisions', label: t('dashboard.decisions'), Icon: ScaleIcon, count: counts.decisions },
    ];

    return (
        <div className="space-y-6">
            <Head title={t('dashboard.my_dashboard')} />

            <div className="rounded-2xl p-5 text-white shadow-lg sm:p-6" style={{ background: 'linear-gradient(135deg, rgb(var(--ac)), var(--primary-strong))' }}>
                <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <h1 className="min-w-0 truncate text-xl font-bold sm:text-2xl">
                        {t('dashboard.welcome_back')}, {greetingName}
                    </h1>
                    <div className="shrink-0 rounded-xl bg-white/15 px-4 py-2 text-sm backdrop-blur-xs">
                        <div className="text-xs tracking-wide text-white/70 uppercase">{t('dashboard.today')}</div>
                        <div className="font-semibold">{today}</div>
                    </div>
                </div>
            </div>

            <div className="grid grid-cols-2 gap-3 lg:grid-cols-4">
                <Stat label={t('dashboard.total_cases')} value={stats.total} color="rgb(var(--ac))" Icon={BriefcaseIcon} neutralValue />
                <Stat label={t('dashboard.pending')} value={stats.pending} color="var(--warning)" Icon={ClockIcon} percent={pct(stats.pending)} />
                <Stat label={t('dashboard.active')} value={stats.active} color="var(--success)" Icon={CheckCircleIcon} percent={pct(stats.active)} />
                <Stat label={t('dashboard.closed')} value={stats.closed} color="var(--text-subtle)" Icon={CheckIcon} percent={pct(stats.closed)} neutralValue />
            </div>

            <div className="grid grid-cols-1 gap-6 lg:grid-cols-[3fr_9fr]">
                <div className="ui-card ui-card-body">
                    <PanelHeading Icon={BoltIcon} title={t('dashboard.quick_actions')} accent />
                    <div className="space-y-2.5">
                        <Link href={route('applicant.cases.create')} className="dash-action dash-action-primary">
                            <PlusIcon className="h-5 w-5 shrink-0" />
                            <span>{t('dashboard.submit_new_case')}</span>
                        </Link>
                        {actions.map((action) => (
                            <button
                                key={action.panel}
                                type="button"
                                onClick={() => setPanel(action.panel)}
                                className={cn('dash-action', panel === action.panel && 'dash-action-active')}
                                aria-pressed={panel === action.panel}
                            >
                                <action.Icon className="h-5 w-5 shrink-0" />
                                <span>{action.label}</span>
                                {action.count !== undefined && <span className="dash-action-count">{action.count}</span>}
                            </button>
                        ))}
                    </div>
                </div>

                <div className="ui-card ui-card-body">
                    {panel === 'recent' && <RecentCases cases={props.recent} />}
                    {panel === 'letters' && (
                        <PanelSection
                            Icon={EnvelopeIcon}
                            title={t('dashboard.letters')}
                            description={t('dashboard.letters_description')}
                            aside={<Link href={route('applicant.response-replies.index')} className="text-sm font-medium text-[rgb(var(--ac))]">{t('respondent.response_of_response')}</Link>}
                            onBack={() => setPanel('recent')}
                            empty={t('dashboard.no_letters')}
                        >
                            {props.letters.map((letter) => (
                                <div key={letter.id} className="dash-list-item">
                                    <ItemHeading title={letter.title} meta={letter.date} />
                                    <div className="mt-1 flex flex-wrap gap-3 text-xs text-(--text-subtle)">
                                        <span>
                                            {t('dashboard.case_number')}: {letter.caseNumber ?? '--'}
                                        </span>
                                        {letter.referenceNumber && (
                                            <span>
                                                {t('dashboard.reference_number')}: {letter.referenceNumber}
                                            </span>
                                        )}
                                    </div>
                                    <a href={letter.previewUrl} target="_blank" rel="noopener" className="mt-2 inline-flex items-center gap-1 text-sm font-semibold text-[rgb(var(--ac))]">
                                        {t('dashboard.view')}
                                        <ArrowRightIcon className="h-4 w-4" />
                                    </a>
                                </div>
                            ))}
                        </PanelSection>
                    )}
                    {panel === 'responses' && (
                        <PanelSection
                            Icon={ChatBubbleBottomCenterTextIcon}
                            title={t('dashboard.responses')}
                            description={t('dashboard.responses_description')}
                            onBack={() => setPanel('recent')}
                            empty={t('dashboard.no_responses')}
                        >
                            {props.responses.map((response) => (
                                <div key={response.id} className="dash-list-item">
                                    <ItemHeading title={response.title} meta={response.when} />
                                    <div className="mt-1 text-xs text-(--text-subtle)">
                                        {t('dashboard.case_number')}: {response.caseNumber ?? '--'}
                                    </div>
                                    <div className="mt-3 flex justify-end">
                                        {response.href ? (
                                            <Link
                                                href={response.href}
                                                className="inline-flex items-center gap-2 rounded-lg border bg-(--surface-strong) px-3 py-1.5 text-sm font-medium"
                                                style={{ borderColor: 'color-mix(in srgb, var(--success) 30%, transparent)', color: 'var(--success)' }}
                                            >
                                                <ChevronRightIcon className="h-4 w-4" />
                                                {t('dashboard.view')}
                                            </Link>
                                        ) : (
                                            <span className="inline-flex cursor-not-allowed items-center rounded-lg border border-(--border) px-3 py-1.5 text-sm font-medium text-(--text-subtle)">
                                                {t('dashboard.view')}
                                            </span>
                                        )}
                                    </div>
                                </div>
                            ))}
                        </PanelSection>
                    )}
                    {panel === 'responseReplies' && (
                        <PanelSection
                            Icon={DocumentTextIcon}
                            title={t('respondent.response_of_response')}
                            description={t('respondent.applicant_response_replies')}
                            aside={<Link href={route('applicant.response-replies.index')} className="text-sm font-medium text-[rgb(var(--ac))]">{t('dashboard.view_all')}</Link>}
                            empty={t('respondent.no_applicant_response_replies')}
                        >
                            {props.responseReplies.map((reply) => (
                                <div key={reply.id} className="dash-list-item">
                                    <div className="flex items-center justify-between gap-2">
                                        <div className="truncate font-semibold text-(--text)">{reply.caseNumber}</div>
                                        <ReviewStatusPill status={reply.status} />
                                    </div>
                                    <div className="mt-1 flex flex-wrap gap-3 text-xs text-(--text-subtle)">
                                        <span>
                                            {t('respondent.response_number_label')}: {reply.responseNumber ?? t('cases.labels.not_available')}
                                        </span>
                                        <span>{reply.date}</span>
                                    </div>
                                    {reply.reviewNote && (
                                        <div className="mt-2 text-xs text-(--text-muted)">
                                            <span className="font-semibold text-(--text)">{t('cases.reviewer_note')}</span> {reply.reviewNote}
                                        </div>
                                    )}
                                    <div className="mt-3 flex justify-end">
                                        <Link
                                            href={reply.href}
                                            className="inline-flex items-center gap-2 rounded-lg border border-[rgb(var(--ac)/0.3)] bg-(--surface-strong) px-3 py-1.5 text-sm font-medium text-[rgb(var(--ac))]"
                                        >
                                            {t('dashboard.view')}
                                        </Link>
                                    </div>
                                </div>
                            ))}
                        </PanelSection>
                    )}
                    {panel === 'decisions' && (
                        <PanelSection
                            Icon={ScaleIcon}
                            title={t('dashboard.decisions')}
                            description={t('dashboard.decisions_description')}
                            onBack={() => setPanel('recent')}
                            empty={t('dashboard.no_decisions')}
                        >
                            {props.decisions.map((decision) => (
                                <div key={decision.id} className="dash-list-item">
                                    <ItemHeading title={decision.name} meta={decision.date} />
                                    <div className="mt-1 flex flex-wrap items-center gap-3 text-xs text-(--text-subtle)">
                                        <span>
                                            {t('dashboard.case_number')}: {decision.caseNumber ?? '--'}
                                        </span>
                                        {decision.status && (
                                            <span className="inline-flex items-center rounded-full border border-(--border) bg-(--surface-soft) px-2.5 py-0.5 font-semibold text-(--text-muted)">
                                                {t(`decisions.status.${decision.status}`)}
                                            </span>
                                        )}
                                        {decision.downloadUrl && (
                                            <a
                                                href={decision.downloadUrl}
                                                className="inline-flex items-center gap-1 rounded-full border border-emerald-300 bg-emerald-50 px-2.5 py-0.5 font-semibold text-emerald-700"
                                            >
                                                <ArrowDownTrayIcon className="h-3.5 w-3.5" />
                                                {t('decisions.download')}
                                            </a>
                                        )}
                                    </div>
                                </div>
                            ))}
                        </PanelSection>
                    )}
                </div>
            </div>

            <div className="ui-card ui-card-body">
                <PanelHeading Icon={InformationCircleIcon} title={t('dashboard.quick_tips')} accent />
                <div className="grid gap-3 sm:grid-cols-3">
                    {[1, 2, 3].map((tip) => (
                        <div key={tip} className="rounded-xl border border-(--border) bg-(--surface-soft) p-4">
                            <div className="mb-1.5 font-medium text-(--text)">{t(`dashboard.tip${tip}_title`)}</div>
                            <p className="text-sm text-(--text-muted)">{t(`dashboard.tip${tip}_description`)}</p>
                        </div>
                    ))}
                </div>
            </div>
        </div>
    );
}

function Stat({ label, value, color, Icon, percent, neutralValue = false }: { label: string; value: number; color: string; Icon: Icon; percent?: number; neutralValue?: boolean }) {
    const neutral = color === 'var(--text-subtle)';

    return (
        <div className="dash-stat">
            <div className="mb-3 flex items-center justify-between">
                <div className="dash-stat-label" style={neutral || neutralValue ? undefined : { color }}>
                    {label}
                </div>
                <div className="dash-stat-icon" style={neutral ? { background: 'var(--surface-soft)', color } : { background: `color-mix(in srgb, ${color} 14%, transparent)`, color }}>
                    <Icon className="h-5 w-5" />
                </div>
            </div>
            <div className="dash-stat-value" style={neutralValue ? undefined : { color }}>
                {value}
            </div>
            {percent !== undefined && (
                <div className="mt-3 h-1.5 w-full rounded-full" style={{ background: neutral ? 'var(--surface-soft)' : `color-mix(in srgb, ${color} 16%, transparent)` }}>
                    <div className="h-1.5 rounded-full" style={{ width: `${percent}%`, background: color }} />
                </div>
            )}
        </div>
    );
}

function PanelHeading({ Icon, title, description, accent = false }: { Icon: Icon; title: string; description?: string; accent?: boolean }) {
    return (
        <div className="mb-5 flex items-center gap-3">
            <div className="dash-stat-icon" style={accent ? { background: 'rgb(var(--ac) / 0.12)', color: 'rgb(var(--ac))' } : { background: 'var(--surface-soft)', color: 'var(--text-muted)' }}>
                <Icon className="h-5 w-5" />
            </div>
            <div>
                <h2 className="text-base font-semibold text-(--text)">{title}</h2>
                {description && <p className="text-xs text-(--text-subtle)">{description}</p>}
            </div>
        </div>
    );
}

function PanelSection({
    Icon,
    title,
    description,
    aside,
    onBack,
    empty,
    children,
}: {
    Icon: Icon;
    title: string;
    description: string;
    aside?: ReactNode;
    onBack?: () => void;
    empty: string;
    children: ReactNode[];
}) {
    return (
        <div>
            <div className="flex items-start justify-between gap-3">
                <PanelHeading Icon={Icon} title={title} description={description} accent />
                <div className="flex items-center gap-3 pt-1">
                    {aside}
                    {onBack && (
                        <button type="button" onClick={onBack} className="text-sm font-medium text-[rgb(var(--ac))]">
                            {t('dashboard.recent_cases')}
                        </button>
                    )}
                </div>
            </div>
            <div className="space-y-2.5">{children.length > 0 ? children : <div className="dash-empty">{empty}</div>}</div>
        </div>
    );
}

function ItemHeading({ title, meta }: { title: string; meta: string }) {
    return (
        <div className="flex items-center justify-between gap-2">
            <div className="truncate font-semibold text-(--text)">{title}</div>
            <div className="text-xs whitespace-nowrap text-(--text-subtle)">{meta}</div>
        </div>
    );
}

function RecentCases({ cases }: { cases: Props['recent'] }) {
    const shown = cases.slice(0, 3);

    return (
        <div>
            <div className="mb-5 flex items-center justify-between">
                <PanelHeading Icon={DocumentTextIcon} title={t('dashboard.recent_cases')} />
                <Link href={route('applicant.cases.index')} className="-mt-5 inline-flex items-center gap-1 text-sm font-medium text-[rgb(var(--ac))]">
                    {t('dashboard.view_all')}
                    <ChevronRightIcon className="h-4 w-4" />
                </Link>
            </div>

            {shown.length === 0 ? (
                <div className="dash-empty py-12">
                    <DocumentTextIcon className="mx-auto h-12 w-12 opacity-40" strokeWidth={1.5} />
                    <h3 className="mt-4 text-lg font-medium text-(--text)">{t('dashboard.no_cases_yet')}</h3>
                    <p className="mx-auto mt-2 max-w-sm text-(--text-subtle)">{t('dashboard.no_cases_description')}</p>
                    <Link
                        href={route('applicant.cases.create')}
                        className="mt-6 inline-flex items-center gap-2 rounded-lg px-5 py-2.5 font-medium text-white"
                        style={{ background: 'var(--warning)' }}
                    >
                        <PlusIcon className="h-5 w-5" />
                        {t('dashboard.submit_first_case')}
                    </Link>
                </div>
            ) : (
                <>
                    <div className="hidden overflow-hidden rounded-xl border border-(--border) lg:block">
                        <div className="overflow-x-auto">
                            <table className="min-w-full text-sm">
                                <thead className="bg-(--surface-soft) text-(--text-subtle)">
                                    <tr>
                                        {['case_number', 'type', 'status', 'created'].map((column) => (
                                            <th key={column} scope="col" className="px-4 py-3 text-left text-xs font-semibold tracking-wider uppercase">
                                                {t(`dashboard.${column}`)}
                                            </th>
                                        ))}
                                        <th scope="col" className="px-4 py-3 text-right text-xs font-semibold tracking-wider uppercase">
                                            {t('dashboard.actions')}
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {shown.map((item) => (
                                        <tr key={item.id} className="border-t border-(--border)">
                                            <td className="px-4 py-3 whitespace-nowrap">
                                                <span className="font-mono font-semibold text-[rgb(var(--ac))]">{item.caseNumber}</span>
                                            </td>
                                            <td className="px-4 py-3 whitespace-nowrap">
                                                <span className="inline-flex items-center rounded-full bg-[rgb(var(--ac)/0.1)] px-2.5 py-1 text-xs font-medium text-[rgb(var(--ac))]">{item.caseType}</span>
                                            </td>
                                            <td className="px-4 py-3 whitespace-nowrap">
                                                <CaseStatusPill status={item.status} />
                                            </td>
                                            <td className="px-4 py-3 whitespace-nowrap">
                                                <div className="text-(--text-muted)">{item.created}</div>
                                                <div className="text-xs text-(--text-subtle)">{item.createdRelative}</div>
                                            </td>
                                            <td className="px-4 py-3 text-right whitespace-nowrap">
                                                <Link
                                                    href={route('applicant.cases.show', item.id)}
                                                    className="inline-flex items-center gap-1.5 rounded-lg bg-[rgb(var(--ac)/0.1)] px-3 py-1.5 text-sm font-medium text-[rgb(var(--ac))]"
                                                >
                                                    {t('dashboard.view')}
                                                    <ArrowRightIcon className="h-4 w-4" />
                                                </Link>
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div className="space-y-3 lg:hidden">
                        {shown.map((item) => (
                            <div key={item.id} className="dash-list-item">
                                <div className="mb-2 flex items-start justify-between gap-2">
                                    <div className="min-w-0">
                                        <span className="font-mono font-semibold text-[rgb(var(--ac))]">{item.caseNumber}</span>
                                        <div className="mt-1">
                                            <span className="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium" style={tintStyle(caseStatusColor(item.status), false)}>
                                                {t(`cases.status.${item.status}`)}
                                            </span>
                                        </div>
                                    </div>
                                    <div className="shrink-0 text-xs text-(--text-subtle)">{item.createdRelative}</div>
                                </div>
                                <h3 className="mb-3 font-medium text-(--text)">{item.title}</h3>
                                <div className="flex items-center justify-between">
                                    <span className="inline-flex items-center rounded-full bg-[rgb(var(--ac)/0.1)] px-2.5 py-0.5 text-xs text-[rgb(var(--ac))]">{item.caseType}</span>
                                    <Link href={route('applicant.cases.show', item.id)} className="inline-flex items-center gap-1 text-sm font-medium text-[rgb(var(--ac))]">
                                        {t('dashboard.view')}
                                        <ArrowRightIcon className="h-4 w-4" />
                                    </Link>
                                </div>
                            </div>
                        ))}
                    </div>
                </>
            )}
        </div>
    );
}
