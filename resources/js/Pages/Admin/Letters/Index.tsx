import {
    ArrowPathIcon,
    CheckCircleIcon,
    ChevronDownIcon,
    DocumentTextIcon,
    EnvelopeIcon,
    FolderIcon,
    MagnifyingGlassIcon,
    TrophyIcon,
    XCircleIcon,
} from '@heroicons/react/24/outline';
import { Head, router } from '@inertiajs/react';
import type { FormEvent, ReactNode } from 'react';
import { Badge, type BadgeTone } from '@/components/ui/Badge';
import { Button, LinkButton } from '@/components/ui/Button';
import { ConfirmButton } from '@/components/ui/ConfirmButton';
import { Dropdown } from '@/components/ui/Dropdown';
import { EmptyState } from '@/components/ui/EmptyState';
import { Input } from '@/components/ui/Input';
import { PageHeader } from '@/components/ui/PageHeader';
import { Pagination } from '@/components/ui/Pagination';
import { Table } from '@/components/ui/Table';
import { useCan } from '@/hooks/useCan';
import { useFilters } from '@/hooks/useFilters';
import { cn } from '@/lib/cn';
import { t } from '@/lib/i18n';
import { route } from '@/lib/route';
import type { Paginated } from '@/types';
import type { LetterApprovalStatus, LetterRow, LetterStats } from '@/types/admin/letters';

interface Props {
    letters: Paginated<LetterRow>;
    stats: LetterStats;
    filters: { status: '' | 'pending' | 'approved' | 'rejected'; q: string };
}

const statusTones: Record<Exclude<LetterApprovalStatus, 'pending'>, BadgeTone> = {
    approved: 'success',
    returned: 'warning',
    rejected: 'danger',
};

const decisions = ['approved', 'returned', 'rejected'] as const;

export default function LettersIndex({ letters, stats, filters }: Props) {
    const can = useCan();
    const canUpdate = can('letters.update');
    const canDelete = can('letters.delete');
    const canApprove = can('letters.approve');
    const { values, set, change, apply } = useFilters(route('letters.index'), filters);

    const search = (event: FormEvent) => {
        event.preventDefault();
        apply();
    };

    const decide = (letter: LetterRow, status: (typeof decisions)[number]) => {
        router.post(route('letters.approve', letter.id), { status }, { preserveScroll: true });
    };

    const statusTabs: Array<{ key: Props['filters']['status']; label: string }> = [
        { key: '', label: t('letters.table.all_letters') },
        { key: 'pending', label: t('letters.table.status_pending') },
        { key: 'approved', label: t('letters.table.status_approved') },
        { key: 'rejected', label: t('letters.table.status_rejected') },
    ];

    return (
        <>
            <Head title={t('letters.titles.index')} />
            <PageHeader
                title={t('letters.titles.index')}
                description={t('letters.description.index')}
                className="mb-6"
                actions={can('letters.create') && <LinkButton href={route('letters.compose')}>{t('letters.actions.new_letter')}</LinkButton>}
            />

            <div className="mb-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
                <StatCard icon={<EnvelopeIcon className="h-5 w-5" />} label={t('letters.cards.total_letters')} value={stats.total} tone="text-blue-600" />
                <StatCard
                    icon={<CheckCircleIcon className="h-5 w-5" />}
                    label={t('letters.cards.approved_letters')}
                    value={stats.approved}
                    hint={t('letters.cards.approved_letters_hint')}
                    tone="text-emerald-600"
                />
                <StatCard
                    icon={<XCircleIcon className="h-5 w-5" />}
                    label={t('letters.cards.rejected_letters')}
                    value={stats.rejected}
                    hint={t('letters.cards.rejected_letters_hint')}
                    tone="text-rose-600"
                />
                <StatCard
                    icon={<FolderIcon className="h-5 w-5" />}
                    label={t('letters.cards.letter_categories')}
                    value={stats.categories}
                    hint={
                        `${stats.categorisedLetters} ${t('letters.cards.letters_count_suffix')}` +
                        (stats.topCategory ? ` | ${t('letters.cards.top_category', { category: stats.topCategory })}` : '')
                    }
                    tone="text-violet-600"
                />
                <StatCard
                    icon={<TrophyIcon className="h-5 w-5" />}
                    label={t('letters.cards.max_letter_type_count')}
                    value={stats.topCategoryCount}
                    hint={stats.topCategory ? t('letters.cards.top_category', { category: stats.topCategory }) : t('letters.cards.empty_top_category')}
                    tone="text-amber-600"
                />
            </div>

            <div className="ui-toolbar mb-4">
                <div className="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                    <nav className="flex flex-wrap gap-2" aria-label={t('letters.table.filter')}>
                        {statusTabs.map((tab) => (
                            <button
                                key={tab.key || 'all'}
                                type="button"
                                onClick={() => change('status', tab.key)}
                                aria-pressed={values.status === tab.key}
                                className={cn(
                                    'rounded-full border px-3 py-1.5 text-xs font-medium transition-colors',
                                    values.status === tab.key
                                        ? 'border-blue-600 bg-blue-600 text-white'
                                        : 'border-(--border) bg-(--surface-strong) text-(--text-muted) hover:bg-(--surface-soft)',
                                )}
                            >
                                {tab.label}
                            </button>
                        ))}
                    </nav>
                    <form onSubmit={search} className="flex items-center gap-2" role="search">
                        <label htmlFor="letters-q" className="sr-only">
                            {t('letters.table.search_placeholder')}
                        </label>
                        <Input
                            id="letters-q"
                            value={values.q}
                            onChange={(event) => set('q', event.target.value)}
                            placeholder={t('letters.table.search_placeholder')}
                        />
                        <Button type="submit" variant="secondary" aria-label={t('letters.table.filter')}>
                            <MagnifyingGlassIcon className="h-4 w-4" />
                        </Button>
                    </form>
                </div>
            </div>

            <Table
                head={
                    <tr>
                        <th scope="col">{t('letters.table.reference')}</th>
                        <th scope="col">{t('letters.table.applicant')}</th>
                        <th scope="col">{t('letters.table.respondent')}</th>
                        <th scope="col">{t('letters.table.author')}</th>
                        <th scope="col">{t('letters.table.created')}</th>
                        <th scope="col">
                            <span className="sr-only">{t('letters.table.actions')}</span>
                        </th>
                    </tr>
                }
                footer={letters.last_page > 1 ? <Pagination meta={letters} /> : undefined}
            >
                {letters.data.length === 0 ? (
                    <tr>
                        <td colSpan={6} className="py-12">
                            <EmptyState
                                icon={<DocumentTextIcon className="h-6 w-6" />}
                                title={t('letters.cards.no_letters_yet')}
                                description={t('letters.cards.empty_description')}
                            />
                        </td>
                    </tr>
                ) : (
                    letters.data.map((letter) => {
                        const approved = letter.status === 'approved';

                        return (
                            <tr key={letter.id}>
                                <td>
                                    <div className="font-mono text-sm font-semibold text-(--text)">{letter.referenceNumber ?? t('letters.cards.missing')}</div>
                                    {letter.subject && <p className="mt-0.5 text-xs text-(--text-subtle)">{letter.subject}</p>}
                                </td>
                                <td>
                                    <Party
                                        name={letter.sendToApplicant ? (letter.applicantName ?? t('letters.cards.missing')) : t('letters.cards.missing')}
                                        role={
                                            letter.sendToApplicant && letter.lawyerName
                                                ? t('dashboard.submitted_by_lawyer', { name: letter.lawyerName })
                                                : t('letters.form.deliver_applicant')
                                        }
                                        recipient={letter.sendToApplicant}
                                    />
                                </td>
                                <td>
                                    <Party
                                        name={letter.sendToRespondent ? (letter.respondentName ?? t('letters.cards.missing')) : t('letters.cards.missing')}
                                        role={t('letters.form.deliver_respondent')}
                                        recipient={letter.sendToRespondent}
                                    />
                                </td>
                                <td>
                                    <p className="text-sm font-medium text-(--text)">{letter.authorName ?? t('letters.cards.missing')}</p>
                                    <p className="text-xs text-(--text-subtle)">{letter.authorPosition ?? t('letters.table.author')}</p>
                                </td>
                                <td className="whitespace-nowrap">
                                    <p className="text-sm text-(--text)">{letter.date}</p>
                                    <p className="text-xs text-(--text-subtle)">{letter.time}</p>
                                    {letter.status && letter.status !== 'pending' && (
                                        <Badge tone={statusTones[letter.status]} className="mt-1">
                                            {t(`letters.table.status_${letter.status}`)}
                                            {letter.approvedByName && <span className="font-normal"> | {letter.approvedByName}</span>}
                                        </Badge>
                                    )}
                                </td>
                                <td className="whitespace-nowrap">
                                    <div className="flex justify-end gap-2">
                                        <LinkButton href={letter.previewUrl} variant="secondary" size="sm" external target="_blank">
                                            {t('letters.actions.view')}
                                        </LinkButton>
                                        {!approved && canUpdate && (
                                            <LinkButton href={route('letters.edit', letter.id)} variant="secondary" size="sm">
                                                {t('letters.actions.edit')}
                                            </LinkButton>
                                        )}
                                        {!approved && canApprove && (
                                            <Dropdown
                                                trigger={({ open, toggle }) => (
                                                    <Button variant="success" size="sm" onClick={toggle} aria-expanded={open} aria-haspopup="menu">
                                                        {t('letters.actions.approve')}
                                                        <ChevronDownIcon className="h-3 w-3" />
                                                    </Button>
                                                )}
                                                panelClassName="w-44"
                                            >
                                                {(close) => (
                                                    <div role="menu" className="py-1">
                                                        {decisions.map((status) => (
                                                            <button
                                                                key={status}
                                                                type="button"
                                                                role="menuitem"
                                                                onClick={() => {
                                                                    close();
                                                                    decide(letter, status);
                                                                }}
                                                                className="flex w-full items-center gap-2 px-3 py-2 text-start text-sm text-(--text) hover:bg-(--surface-soft)"
                                                            >
                                                                {status === 'returned' ? (
                                                                    <ArrowPathIcon className="h-4 w-4 text-amber-600" />
                                                                ) : status === 'approved' ? (
                                                                    <CheckCircleIcon className="h-4 w-4 text-emerald-600" />
                                                                ) : (
                                                                    <XCircleIcon className="h-4 w-4 text-rose-600" />
                                                                )}
                                                                {t(`letters.table.status_${status}`)}
                                                            </button>
                                                        ))}
                                                    </div>
                                                )}
                                            </Dropdown>
                                        )}
                                        {!approved && canDelete && (
                                            <ConfirmButton
                                                url={route('letters.destroy', letter.id)}
                                                title={t('letters.confirm.delete_letter')}
                                                message={letter.referenceNumber ?? letter.subject ?? undefined}
                                            >
                                                {t('letters.actions.delete')}
                                            </ConfirmButton>
                                        )}
                                    </div>
                                </td>
                            </tr>
                        );
                    })
                )}
            </Table>
        </>
    );
}

function StatCard({ icon, label, value, hint, tone }: { icon: ReactNode; label: string; value: number; hint?: string; tone: string }) {
    return (
        <div className="ui-card flex items-start gap-3 p-4">
            <div className={cn('rounded-xl bg-(--surface-soft) p-2.5', tone)}>{icon}</div>
            <div className="min-w-0">
                <p className="text-xs font-medium text-(--text-subtle)">{label}</p>
                <p className="text-2xl font-semibold text-(--text)">{value}</p>
                {hint && (
                    <p className="truncate text-xs text-(--text-subtle)" title={hint}>
                        {hint}
                    </p>
                )}
            </div>
        </div>
    );
}

function Party({ name, role, recipient }: { name: string; role: string; recipient: boolean }) {
    return (
        <div className={cn(!recipient && 'opacity-60')}>
            <p className="text-sm font-medium text-(--text)">{name}</p>
            <p className="text-xs text-(--text-subtle)">{role}</p>
        </div>
    );
}
