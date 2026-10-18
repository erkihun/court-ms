import { ClipboardDocumentListIcon } from '@heroicons/react/24/outline';
import { Head } from '@inertiajs/react';
import type { FormEvent } from 'react';
import { Button } from '@/components/ui/Button';
import { EmptyState } from '@/components/ui/EmptyState';
import { EthDateInput } from '@/components/ui/EthDateInput';
import { Field } from '@/components/ui/Field';
import { Input, Select } from '@/components/ui/Input';
import { PageHeader } from '@/components/ui/PageHeader';
import { Pagination } from '@/components/ui/Pagination';
import { Table } from '@/components/ui/Table';
import { useFilters } from '@/hooks/useFilters';
import { cn } from '@/lib/cn';
import { t } from '@/lib/i18n';
import { route } from '@/lib/route';
import type { Paginated } from '@/types';

interface AuditEvent {
    key: string;
    id: number;
    source: 'system' | 'case' | 'legacy_model';
    sourceLabel: string;
    occurredAt: string;
    action: string;
    actionLabel: string;
    description: string;
    method: string | null;
    outcome: string;
    responseStatus: number | null;
    actorName: string;
    actorType: string;
    targetLabel: string;
    route: string | null;
    requestId: string | null;
    ip: string | null;
    /** Pretty-printed JSON, or null when the event carries nothing extra. */
    details: string | null;
}

type Filters = {
    search: string;
    source: string;
    outcome: string;
    actor_type: string;
    method: string;
    date_from: string;
    date_to: string;
};

interface Props {
    events: Paginated<AuditEvent>;
    filters: Filters;
}

const SOURCES = ['system', 'case', 'legacy_model'] as const;
const OUTCOMES = ['success', 'failure', 'error'] as const;
const ACTORS = ['user', 'applicant', 'respondent', 'guest', 'system'] as const;
const METHODS = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS'] as const;

const sourceTone: Record<AuditEvent['source'], string> = {
    case: 'bg-violet-100 text-violet-800 dark:bg-violet-500/15 dark:text-violet-200',
    legacy_model: 'bg-slate-100 text-slate-700 dark:bg-slate-500/15 dark:text-slate-200',
    system: 'bg-blue-100 text-blue-800 dark:bg-blue-500/15 dark:text-blue-200',
};

function outcomeTone(outcome: string): string {
    if (outcome === 'success') return 'bg-emerald-100 text-emerald-800 dark:bg-emerald-500/15 dark:text-emerald-200';
    if (outcome === 'failure') return 'bg-amber-100 text-amber-800 dark:bg-amber-500/15 dark:text-amber-200';
    return 'bg-red-100 text-red-800 dark:bg-red-500/15 dark:text-red-200';
}

export default function AuditIndex({ events, filters }: Props) {
    const { values, set, apply, reset } = useFilters(route('admin.audit'), filters);

    const submit = (event: FormEvent) => {
        event.preventDefault();
        apply();
    };

    return (
        <>
            <Head title={t('audit.title')} />
            <PageHeader
                title={t('audit.heading')}
                description={t('audit.subtitle')}
                actions={<span className="ui-badge ui-badge-blue">{t('audit.matching_events', { count: events.total.toLocaleString() })}</span>}
                accent
                className="mb-6"
            />

            <form onSubmit={submit} role="search" className="ui-toolbar mb-6 grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                <Field label={t('audit.filters.search')} className="xl:col-span-2">
                    <Input
                        value={values.search}
                        onChange={(event) => set('search', event.target.value)}
                        maxLength={150}
                        placeholder={t('audit.filters.search_placeholder')}
                    />
                </Field>
                <Field label={t('audit.filters.source')}>
                    <Select value={values.source} onChange={(event) => set('source', event.target.value)}>
                        <option value="">{t('audit.filters.all_sources')}</option>
                        {SOURCES.map((source) => (
                            <option key={source} value={source}>
                                {t(`audit.sources.${source}`)}
                            </option>
                        ))}
                    </Select>
                </Field>
                <Field label={t('audit.filters.outcome')}>
                    <Select value={values.outcome} onChange={(event) => set('outcome', event.target.value)}>
                        <option value="">{t('audit.filters.all_outcomes')}</option>
                        {OUTCOMES.map((outcome) => (
                            <option key={outcome} value={outcome}>
                                {t(`audit.outcomes.${outcome}`)}
                            </option>
                        ))}
                    </Select>
                </Field>
                <Field label={t('audit.filters.actor_type')}>
                    <Select value={values.actor_type} onChange={(event) => set('actor_type', event.target.value)}>
                        <option value="">{t('audit.filters.all_actors')}</option>
                        {ACTORS.map((actor) => (
                            <option key={actor} value={actor}>
                                {t(`audit.actors.${actor}`)}
                            </option>
                        ))}
                    </Select>
                </Field>
                <Field label={t('audit.filters.method')}>
                    <Select value={values.method} onChange={(event) => set('method', event.target.value)}>
                        <option value="">{t('audit.filters.all_methods')}</option>
                        {METHODS.map((method) => (
                            <option key={method} value={method}>
                                {method}
                            </option>
                        ))}
                    </Select>
                </Field>
                <Field label={t('audit.filters.from')}>
                    <EthDateInput value={values.date_from} onChange={(value) => set('date_from', value)} />
                </Field>
                <Field label={t('audit.filters.to')}>
                    <EthDateInput value={values.date_to} onChange={(value) => set('date_to', value)} align="end" />
                </Field>
                <div className="flex items-end gap-2 md:col-span-2 xl:col-span-4">
                    <Button type="submit">{t('audit.filters.apply')}</Button>
                    <Button variant="secondary" onClick={reset}>
                        {t('audit.filters.clear')}
                    </Button>
                </div>
            </form>

            <Table
                head={
                    <tr>
                        <th scope="col">{t('audit.columns.time_source')}</th>
                        <th scope="col">{t('audit.columns.action_outcome')}</th>
                        <th scope="col">{t('audit.columns.actor')}</th>
                        <th scope="col">{t('audit.columns.target_route')}</th>
                        <th scope="col">{t('audit.columns.evidence')}</th>
                        <th scope="col">{t('audit.columns.details')}</th>
                    </tr>
                }
                footer={events.last_page > 1 ? <Pagination meta={events} /> : undefined}
            >
                {events.data.length === 0 ? (
                    <tr>
                        <td colSpan={6} className="py-12">
                            <EmptyState icon={<ClipboardDocumentListIcon className="h-6 w-6" />} title={t('audit.empty')} />
                        </td>
                    </tr>
                ) : (
                    events.data.map((event) => (
                        <tr key={event.key} className="align-top">
                            <td className="text-sm">
                                <div className="font-medium whitespace-nowrap text-(--text)">{event.occurredAt}</div>
                                <span className={cn('mt-2 inline-flex rounded-full px-2.5 py-1 text-xs font-semibold', sourceTone[event.source])}>
                                    {event.sourceLabel}
                                </span>
                            </td>
                            <td className="text-sm">
                                <div className="font-semibold text-(--text)">{event.actionLabel}</div>
                                <div className="mt-1 max-w-sm text-xs leading-5 text-(--text-muted)">{event.description}</div>
                                <div className="mt-1 font-mono text-[11px] text-(--text-subtle)">{event.action}</div>
                                <div className="mt-2 flex flex-wrap gap-1.5">
                                    {event.method && (
                                        <span className="rounded-sm bg-(--surface-soft) px-2 py-1 text-xs font-semibold text-(--text-muted)">
                                            {event.method}
                                        </span>
                                    )}
                                    <span className={cn('rounded-full px-2.5 py-1 text-xs font-semibold', outcomeTone(event.outcome))}>
                                        {OUTCOMES.includes(event.outcome as (typeof OUTCOMES)[number]) ? t(`audit.outcomes.${event.outcome}`) : event.outcome}
                                    </span>
                                    {event.responseStatus !== null && (
                                        <span className="rounded-sm bg-(--surface-soft) px-2 py-1 text-xs text-(--text-muted)">
                                            HTTP {event.responseStatus}
                                        </span>
                                    )}
                                </div>
                            </td>
                            <td className="text-sm">
                                <div className="font-medium text-(--text)">{event.actorName}</div>
                                <div className="mt-1 text-xs tracking-wide text-(--text-subtle) uppercase">{t(`audit.actors.${event.actorType}`)}</div>
                            </td>
                            <td className="text-sm">
                                <div className="font-medium text-(--text)">{event.targetLabel}</div>
                                <div className="mt-1 max-w-xs text-xs break-all text-(--text-subtle)">{event.route || t('audit.not_route_bound')}</div>
                            </td>
                            <td className="text-xs text-(--text-muted)">
                                <dl className="space-y-1.5">
                                    <div>
                                        <dt className="inline font-semibold">{t('audit.request')}:</dt>{' '}
                                        <dd className="inline break-all">{event.requestId || t('audit.not_available')}</dd>
                                    </div>
                                    <div>
                                        <dt className="inline font-semibold">{t('audit.ip')}:</dt>{' '}
                                        <dd className="inline">{event.ip || t('audit.not_available')}</dd>
                                    </div>
                                    <div>
                                        <dt className="inline font-semibold">{t('audit.event')}:</dt>{' '}
                                        <dd className="inline">
                                            {event.source} #{event.id}
                                        </dd>
                                    </div>
                                </dl>
                            </td>
                            <td className="text-sm">
                                {event.details ? (
                                    <details className="max-w-md">
                                        <summary className="cursor-pointer font-semibold text-blue-700 hover:text-blue-900 dark:text-blue-300">
                                            {t('audit.view_details')}
                                        </summary>
                                        <pre className="mt-2 max-h-72 overflow-auto rounded-lg bg-gray-950 p-3 text-xs leading-5 break-words whitespace-pre-wrap text-gray-100">
                                            {event.details}
                                        </pre>
                                    </details>
                                ) : (
                                    <span className="text-(--text-subtle)">{t('audit.no_details')}</span>
                                )}
                            </td>
                        </tr>
                    ))
                )}
            </Table>
        </>
    );
}
