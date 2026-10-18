import { CheckCircleIcon, ClockIcon, FolderIcon, ScaleIcon } from '@heroicons/react/24/outline';
import { Head, Link } from '@inertiajs/react';
import { type ReactNode, useState } from 'react';
import { CaseStatusBadge } from '@/components/admin/cases/CaseBadges';
import { CaseTypeBarChart, CasesLineChart, GenderPieChart } from '@/components/admin/dashboard/DashboardCharts';
import { Alert } from '@/components/ui/Alert';
import { EthCalendar } from '@/components/ui/EthDateInput';
import { useShared } from '@/hooks/useShared';
import { cn } from '@/lib/cn';
import { t } from '@/lib/i18n';
import { route } from '@/lib/route';

interface Member {
    id: number;
    name: string;
    teamName: string | null;
    active: boolean;
    avatarUrl: string | null;
    cases: number;
    pending: number;
    activeCases: number;
    resolved: number;
    types: Array<{ label: string | null; count: number }>;
}

interface CaseStatistics {
    kpis: { total: number; pending: number; active: number; resolved: number };
    members: Member[];
    teams: Array<{ id: number; name: string; cases: number }>;
    recent: Array<{ id: number; caseNumber: string; title: string; status: string }>;
    monthly: { labels: string[]; values: number[] };
    gender: Array<{ gender: string; total: number }>;
    types: Array<{ label: string | null; total: number }>;
}

interface SystemPanels {
    health: {
        database: boolean;
        queue_waiting: number | null;
        queue_ready: boolean;
        mail: boolean;
        sms: boolean;
        sms_ready: boolean;
        telegram: boolean;
        in_app: boolean;
    };
    storage: {
        tables: number;
        database_size: string | null;
        storage_free: number | false | null;
        audit_events: number;
    };
}

interface Props {
    cases: CaseStatistics | null;
    system: SystemPanels | null;
}

const number = (value: number) => value.toLocaleString();

export default function AdminDashboard({ cases, system }: Props) {
    return (
        <>
            <Head title={t('dashboard.title')} />

            <div className="space-y-6">
                <div className="grid grid-cols-1 gap-5 xl:grid-cols-[minmax(0,1fr)_17.5rem]">
                    <div className="xl:order-2">
                        <MiniCalendar />
                    </div>
                    <div className="xl:order-1">{cases ? <KpiCards kpis={cases.kpis} /> : <Alert tone="info">{t('dashboard.no_case_access')}</Alert>}</div>
                </div>

                {cases && <CasePanels cases={cases} />}
                {system && <SystemSection system={system} />}
            </div>
        </>
    );
}

function MiniCalendar() {
    const { today } = useShared().app;
    const [picked, setPicked] = useState(() => new Date().toLocaleDateString('en-CA'));

    return (
        <div className="ui-card p-4">
            <EthCalendar value={picked} onChange={(value) => setPicked(value || picked)} bare />
            <p className="mt-3 border-t border-(--border) pt-2 text-xs text-(--text-subtle)">{today.long}</p>
        </div>
    );
}

function KpiCards({ kpis }: { kpis: CaseStatistics['kpis'] }) {
    const cards: Array<{ label: string; badge: string; value: number; icon: ReactNode; color: string }> = [
        { label: t('dashboard.total_cases'), badge: t('dashboard.all_time'), value: kpis.total, icon: <FolderIcon className="h-5 w-5" />, color: '59 130 246' },
        { label: t('dashboard.pending'), badge: t('dashboard.pending'), value: kpis.pending, icon: <ClockIcon className="h-5 w-5" />, color: '234 88 12' },
        {
            label: t('dashboard.active_cases_label'),
            badge: t('dashboard.active'),
            value: kpis.active,
            icon: <ScaleIcon className="h-5 w-5" />,
            color: '8 145 178',
        },
        {
            label: t('dashboard.resolved_cases_label'),
            badge: t('dashboard.resolved'),
            value: kpis.resolved,
            icon: <CheckCircleIcon className="h-5 w-5" />,
            color: '5 150 105',
        },
    ];

    return (
        <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
            {cards.map((card) => (
                <div key={card.label} className="ui-card p-5" style={{ borderTop: `3px solid rgb(${card.color})` }}>
                    <div className="flex items-start justify-between gap-2">
                        <span
                            className="grid h-10 w-10 place-items-center rounded-xl"
                            style={{ background: `rgb(${card.color} / 0.12)`, color: `rgb(${card.color})` }}
                        >
                            {card.icon}
                        </span>
                        <span
                            className="rounded-full px-2 py-0.5 text-[10px] font-semibold"
                            style={{ background: `rgb(${card.color} / 0.12)`, color: `rgb(${card.color})` }}
                        >
                            {card.badge}
                        </span>
                    </div>
                    <p className="mt-3 text-3xl font-extrabold text-(--text) tabular-nums">{number(card.value)}</p>
                    <p className="text-sm text-(--text-muted)">{card.label}</p>
                </div>
            ))}
        </div>
    );
}

function Panel({
    title,
    subtitle,
    aside,
    children,
    className,
}: {
    title: string;
    subtitle?: string;
    aside?: ReactNode;
    children: ReactNode;
    className?: string;
}) {
    return (
        <section className={cn('ui-card', className)}>
            <div className="flex items-start justify-between gap-3 border-b border-(--border) px-5 py-4">
                <div>
                    <h2 className="text-sm font-bold text-(--text)">{title}</h2>
                    {subtitle && <p className="mt-0.5 text-xs text-(--text-subtle)">{subtitle}</p>}
                </div>
                {aside}
            </div>
            <div className="p-5">{children}</div>
        </section>
    );
}

function CountPill({ value }: { value: number }) {
    return (
        <span className="rounded-full border border-(--border) bg-(--surface-soft) px-2.5 py-1 text-[11px] font-semibold text-(--text-muted)">
            {number(value)}
        </span>
    );
}

function Bar({ percent, color = 'rgb(var(--ac))' }: { percent: number; color?: string }) {
    return (
        <div className="mt-2 h-1.5 overflow-hidden rounded-full bg-(--surface-soft)" aria-hidden="true">
            <div className="h-full rounded-full" style={{ width: `${percent}%`, background: color }} />
        </div>
    );
}

function CasePanels({ cases }: { cases: CaseStatistics }) {
    const logoUrl = useShared().app.logoUrl;
    const maxMember = Math.max(1, ...cases.members.map((member) => member.cases));
    const maxTeam = Math.max(1, ...cases.teams.map((team) => team.cases));
    const sum = (values: number[]) => values.reduce((total, value) => total + value, 0);
    const typeLabel = (label: string | null) => label ?? t('dashboard.unknown_case_type');
    const genderLabel = (gender: string) => (gender === 'male' || gender === 'female' ? t(`auth.${gender}`) : gender || t('dashboard.unknown_case_type'));

    return (
        <>
            <Panel
                title={t('dashboard.cases_by_member')}
                subtitle={t('dashboard.assigned_cases_by_member')}
                aside={<CountPill value={sum(cases.members.map((member) => member.cases))} />}
            >
                {cases.members.length === 0 ? (
                    <p className="py-6 text-center text-sm text-(--text-subtle)">{t('dashboard.no_member_case_counts')}</p>
                ) : (
                    <div className="grid grid-cols-1 gap-4 md:grid-cols-2 2xl:grid-cols-3">
                        {cases.members.map((member) => (
                            <article key={member.id} className="rounded-xl border border-(--border) p-4">
                                <div className="flex items-start justify-between gap-3">
                                    <div className="flex min-w-0 items-center gap-2.5">
                                        {member.avatarUrl || logoUrl ? (
                                            <img
                                                src={member.avatarUrl ?? logoUrl ?? ''}
                                                alt=""
                                                className="h-10 w-10 shrink-0 rounded-full bg-white object-cover shadow-xs"
                                            />
                                        ) : (
                                            <span className="grid h-10 w-10 shrink-0 place-items-center rounded-full bg-[rgb(var(--ac)/0.15)] text-sm font-bold text-[rgb(var(--ac))]">
                                                {member.name.charAt(0).toUpperCase()}
                                            </span>
                                        )}
                                        <div className="min-w-0">
                                            <p className="truncate font-semibold text-(--text)">{member.name}</p>
                                            <div className="flex items-center gap-1.5 text-xs text-(--text-subtle)">
                                                <span className="truncate">{member.teamName ?? t('dashboard.no_team')}</span>
                                                <span
                                                    className={cn(
                                                        'rounded-full border px-1.5 text-[10px]',
                                                        member.active
                                                            ? 'border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300'
                                                            : 'border-(--border)',
                                                    )}
                                                >
                                                    {member.active ? t('users.active') : t('users.inactive')}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div className="text-end">
                                        <p className="text-2xl font-extrabold text-(--text) tabular-nums">{number(member.cases)}</p>
                                        <p className="text-[11px] text-(--text-subtle)">{t('app.Cases')}</p>
                                    </div>
                                </div>
                                <dl className="mt-3 grid grid-cols-3 gap-2 text-center text-xs" aria-label={t('dashboard.status')}>
                                    <Stat label={t('dashboard.pending')} value={member.pending} tone="text-orange-600" />
                                    <Stat label={t('dashboard.active')} value={member.activeCases} tone="text-blue-600" />
                                    <Stat label={t('dashboard.resolved')} value={member.resolved} tone="text-emerald-600" />
                                </dl>
                                {member.types.length > 0 && (
                                    <div className="mt-3 flex flex-wrap items-center gap-1.5">
                                        <span className="text-[10px] font-semibold tracking-wide text-(--text-subtle) uppercase">{t('dashboard.type')}</span>
                                        {member.types.map((type) => (
                                            <span
                                                key={type.label ?? 'unknown'}
                                                className="inline-flex items-center gap-1 rounded-full bg-(--surface-soft) px-2 py-0.5 text-[11px] text-(--text-muted)"
                                            >
                                                <span className="max-w-32 truncate">{typeLabel(type.label)}</span>
                                                <span className="font-semibold text-(--text)">{number(type.count)}</span>
                                            </span>
                                        ))}
                                    </div>
                                )}
                                <Bar percent={(member.cases / maxMember) * 100} />
                            </article>
                        ))}
                    </div>
                )}
            </Panel>

            <div className="grid grid-cols-1 gap-6 xl:grid-cols-2">
                <Panel
                    title={t('dashboard.cases_by_team')}
                    subtitle={t('dashboard.assigned_cases_by_team')}
                    aside={<CountPill value={sum(cases.teams.map((team) => team.cases))} />}
                >
                    {cases.teams.length === 0 ? (
                        <p className="py-6 text-center text-sm text-(--text-subtle)">{t('dashboard.no_team_case_counts')}</p>
                    ) : (
                        <ul className="divide-y divide-(--border)">
                            {cases.teams.map((team) => (
                                <li key={team.id} className="flex items-center justify-between gap-4 py-3">
                                    <div className="min-w-0 flex-1">
                                        <p className="truncate font-medium text-(--text)">{team.name}</p>
                                        <Bar percent={(team.cases / maxTeam) * 100} color="rgb(16 185 129)" />
                                    </div>
                                    <span className="rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300">
                                        {number(team.cases)}
                                    </span>
                                </li>
                            ))}
                        </ul>
                    )}
                </Panel>

                <Panel
                    title={t('dashboard.recent_cases')}
                    subtitle={t('dashboard.recent_cases_description')}
                    aside={
                        <Link href={route('cases.index')} className="text-xs font-medium text-[rgb(var(--ac))] hover:underline">
                            {t('dashboard.view_all')}
                        </Link>
                    }
                >
                    {cases.recent.length === 0 ? (
                        <p className="py-6 text-center text-sm text-(--text-subtle)">{t('dashboard.no_recent_cases')}</p>
                    ) : (
                        <ul className="divide-y divide-(--border)">
                            {cases.recent.map((item) => (
                                <li key={item.id} className="flex items-center justify-between gap-3 py-3">
                                    <div className="min-w-0">
                                        <Link href={route('cases.show', item.id)} className="font-mono text-sm font-semibold text-(--text) hover:underline">
                                            {item.caseNumber}
                                        </Link>
                                        <p className="truncate text-xs text-(--text-subtle)">{item.title}</p>
                                    </div>
                                    <CaseStatusBadge status={item.status} />
                                </li>
                            ))}
                        </ul>
                    )}
                </Panel>
            </div>

            <div className="grid grid-cols-1 gap-6 xl:grid-cols-5">
                <Panel
                    title={t('dashboard.new_cases_per_month')}
                    subtitle={t('dashboard.case_activity')}
                    aside={<span className="text-xs text-(--text-subtle)">{t('dashboard.last_6_months')}</span>}
                    className="xl:col-span-3"
                >
                    <div className="h-64">
                        <CasesLineChart data={cases.monthly} label={t('dashboard.new_cases')} />
                    </div>
                </Panel>
                <Panel
                    title={t('dashboard.applicants_by_gender')}
                    aside={<span className="text-xs text-(--text-subtle)">{t('dashboard.all_time')}</span>}
                    className="xl:col-span-2"
                >
                    <div className="mx-auto h-64 max-w-[360px]">
                        <GenderPieChart
                            data={{ labels: cases.gender.map((row) => genderLabel(row.gender)), values: cases.gender.map((row) => row.total) }}
                            label={t('dashboard.applicants_by_gender')}
                        />
                    </div>
                </Panel>
            </div>

            <Panel title={t('dashboard.cases_by_type')} subtitle={t('dashboard.case_type_distribution')}>
                <div className="h-64">
                    <CaseTypeBarChart
                        data={{ labels: cases.types.map((row) => typeLabel(row.label)), values: cases.types.map((row) => row.total) }}
                        label={t('dashboard.cases_by_type')}
                    />
                </div>
            </Panel>
        </>
    );
}

function Stat({ label, value, tone }: { label: string; value: number; tone: string }) {
    return (
        <div className="rounded-lg bg-(--surface-soft) px-2 py-1.5">
            <dt className="text-[10px] text-(--text-subtle)">{label}</dt>
            <dd className={cn('text-sm font-bold tabular-nums', tone)}>{number(value)}</dd>
        </div>
    );
}

function SystemSection({ system }: { system: SystemPanels }) {
    const { health, storage } = system;
    const healthy = health.database && health.queue_ready;
    const channels: Array<{ key: 'mail' | 'sms' | 'telegram' | 'in_app'; label: string; ready: boolean }> = [
        { key: 'mail', label: 'Email', ready: health.mail },
        { key: 'sms', label: 'SMS', ready: health.sms_ready },
        { key: 'telegram', label: 'Telegram', ready: health.telegram },
        { key: 'in_app', label: 'In-app', ready: health.in_app },
    ];
    const freeGb = typeof storage.storage_free === 'number' ? `${(storage.storage_free / 1073741824).toFixed(1)} GB` : '—';

    return (
        <div className="grid grid-cols-1 gap-6 xl:grid-cols-3">
            <Panel
                title={t('dashboard.service_health')}
                subtitle={healthy ? t('dashboard.operational') : t('dashboard.action_required')}
                className="xl:col-span-2"
                aside={
                    <span
                        className={cn(
                            'inline-flex items-center gap-1.5 text-xs font-semibold',
                            healthy ? 'text-emerald-700 dark:text-emerald-300' : 'text-amber-700 dark:text-amber-300',
                        )}
                    >
                        <span className={cn('h-2 w-2 rounded-full', healthy ? 'bg-emerald-500' : 'bg-amber-500')} aria-hidden="true" />
                        {healthy ? t('dashboard.operational') : t('dashboard.action_required')}
                    </span>
                }
            >
                <div className="grid grid-cols-1 gap-4 md:grid-cols-3">
                    <HealthCard title={t('dashboard.system_uptime')} subtitle={t('dashboard.database_connection')}>
                        <p className={cn('text-lg font-bold', health.database ? 'text-emerald-600' : 'text-red-600')}>
                            {health.database ? t('dashboard.operational') : t('dashboard.unavailable')}
                        </p>
                    </HealthCard>
                    <HealthCard title={t('dashboard.queue_status')} subtitle={t('dashboard.jobs_waiting')}>
                        <p className="text-2xl font-extrabold text-blue-600">{health.queue_waiting ?? '—'}</p>
                    </HealthCard>
                    <HealthCard title={t('dashboard.notifications_service')} subtitle={t('dashboard.email_sms_gateways')}>
                        <div className="flex flex-wrap gap-1.5 text-[11px]">
                            {channels.map((channel) => (
                                <span
                                    key={channel.key}
                                    className={cn(
                                        'rounded-full border px-2 py-0.5',
                                        channel.ready
                                            ? 'border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300'
                                            : health[channel.key]
                                              ? 'border-amber-200 bg-amber-50 text-amber-700 dark:border-amber-800 dark:bg-amber-900/30 dark:text-amber-300'
                                              : 'border-(--border) text-(--text-subtle)',
                                    )}
                                >
                                    {channel.label}:{' '}
                                    {channel.ready ? t('dashboard.operational') : health[channel.key] ? t('dashboard.enabled') : t('dashboard.info_disabled')}
                                </span>
                            ))}
                        </div>
                    </HealthCard>
                </div>
            </Panel>

            <Panel title={t('dashboard.data_storage')}>
                <dl className="grid grid-cols-2 gap-3 text-sm">
                    <StorageStat label={t('dashboard.database_tables')} value={number(storage.tables)} />
                    <StorageStat label={t('dashboard.audit_events')} value={number(storage.audit_events)} />
                    <StorageStat label={t('dashboard.database_size')} value={storage.database_size ?? '—'} />
                    <StorageStat label={t('dashboard.storage_free')} value={freeGb} />
                </dl>
            </Panel>
        </div>
    );
}

function HealthCard({ title, subtitle, children }: { title: string; subtitle: string; children: ReactNode }) {
    return (
        <div className="rounded-xl border border-(--border) p-4">
            <h3 className="text-sm font-semibold text-(--text)">{title}</h3>
            <p className="mb-2 text-xs text-(--text-subtle)">{subtitle}</p>
            {children}
        </div>
    );
}

function StorageStat({ label, value }: { label: string; value: string }) {
    return (
        <div className="rounded-lg border border-(--border) bg-(--surface-soft) p-3">
            <dt className="text-[11px] text-(--text-subtle)">{label}</dt>
            <dd className="mt-1 font-bold text-(--text)">{value}</dd>
        </div>
    );
}
