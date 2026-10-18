import { DocumentTextIcon, PlusIcon } from '@heroicons/react/24/outline';
import { Head } from '@inertiajs/react';
import { type BenchNote, JudgeRow, NoteBody, SignatureBlock } from '@/components/admin/bench-notes/BenchNoteParts';
import { Badge } from '@/components/ui/Badge';
import { Button, LinkButton } from '@/components/ui/Button';
import { Card } from '@/components/ui/Card';
import { ConfirmButton } from '@/components/ui/ConfirmButton';
import { DescriptionList } from '@/components/ui/DescriptionList';
import { EmptyState } from '@/components/ui/EmptyState';
import { Select } from '@/components/ui/Input';
import { PageHeader } from '@/components/ui/PageHeader';
import { Pagination } from '@/components/ui/Pagination';
import { useCan } from '@/hooks/useCan';
import { useFilters } from '@/hooks/useFilters';
import { t } from '@/lib/i18n';
import { route } from '@/lib/route';
import type { Paginated } from '@/types';

interface Props {
    notes: Paginated<BenchNote>;
    cases: Array<{ id: number; label: string }>;
    filters: { case_id: string };
}

export default function BenchNotesIndex({ notes, cases, filters }: Props) {
    const can = useCan();
    const filter = useFilters(route('bench-notes.index'), filters);
    const createHref = route('bench-notes.create', filters.case_id ? { case_id: filters.case_id } : {});

    return (
        <>
            <Head title={t('bench.title')} />
            <PageHeader
                title={t('bench.page_header.index')}
                description={t('bench.descriptions.index')}
                className="mb-6"
                actions={
                    <>
                        <LinkButton href={route('cases.index')} variant="secondary">
                            {t('bench.labels.cases')}
                        </LinkButton>
                        {can('bench-notes.create') && (
                            <LinkButton href={createHref}>
                                <PlusIcon className="h-4 w-4" />
                                {t('bench.buttons.new_note')}
                            </LinkButton>
                        )}
                    </>
                }
            />

            <div className="ui-toolbar mb-6">
                <div className="flex flex-wrap items-end gap-3">
                    <div className="min-w-64 grow sm:grow-0">
                        <label htmlFor="bench-case" className="mb-1.5 block text-sm font-medium text-(--text)">
                            {t('bench.labels.filter_by_case')}
                        </label>
                        <Select id="bench-case" value={filter.values.case_id} onChange={(event) => filter.change('case_id', event.target.value)}>
                            <option value="">{t('bench.options.all_cases')}</option>
                            {cases.map((option) => (
                                <option key={option.id} value={option.id}>
                                    {option.label}
                                </option>
                            ))}
                        </Select>
                    </div>
                    {filter.active && (
                        <Button variant="ghost" onClick={filter.reset}>
                            {t('bench.buttons.clear')}
                        </Button>
                    )}
                </div>
            </div>

            {notes.data.length === 0 ? (
                <Card>
                    <EmptyState
                        icon={<DocumentTextIcon className="h-6 w-6" />}
                        title={t('bench.empty.title')}
                        description={filters.case_id ? t('bench.empty.description_case') : t('bench.empty.description_general')}
                    >
                        {can('bench-notes.create') && <LinkButton href={createHref}>{t('bench.buttons.create_new')}</LinkButton>}
                    </EmptyState>
                </Card>
            ) : (
                <div className="space-y-6">
                    {notes.data.map((note) => (
                        <NoteCard
                            key={note.id}
                            note={note}
                            canUpdate={note.mine && can('bench-notes.update')}
                            canDelete={note.mine && can('bench-notes.delete')}
                        />
                    ))}
                    <Pagination meta={notes} />
                </div>
            )}
        </>
    );
}

function NoteCard({ note, canUpdate, canDelete }: { note: BenchNote; canUpdate: boolean; canDelete: boolean }) {
    return (
        <Card padding="none">
            <div className="flex flex-wrap items-start justify-between gap-3 border-b border-(--border) px-5 py-4">
                <div className="min-w-0">
                    <Badge tone="info">{note.caseNumber ?? t('bench.meta.na')}</Badge>
                    {note.title && <h2 className="mt-2 text-lg font-semibold text-(--text)">{note.title}</h2>}
                    {note.caseTitle && (
                        <p className="mt-0.5 text-sm text-(--text-muted)">
                            {t('bench.labels.case_prefix')} {note.caseTitle}
                        </p>
                    )}
                </div>
                <span className="text-sm text-(--text-subtle)">{note.created}</span>
            </div>

            <div className="space-y-5 px-5 py-5">
                <JudgeRow judges={note.judges} />
                <DescriptionList
                    columns={3}
                    className="lg:grid-cols-4"
                    items={[
                        { label: t('bench.labels.author'), value: note.author ?? t('bench.meta.unknown') },
                        { label: t('bench.labels.created_date'), value: note.created },
                        { label: t('bench.labels.created_time'), value: note.createdTime },
                        { label: t('bench.labels.last_updated'), value: note.updated },
                    ]}
                />
                <div>
                    <h3 className="mb-2 text-sm font-semibold text-(--text)">{t('bench.sections.note_content')}</h3>
                    <NoteBody html={note.noteHtml} />
                </div>
            </div>

            <div className="flex flex-wrap items-end justify-between gap-4 border-t border-(--border) bg-(--surface-soft) px-5 py-4">
                <SignatureBlock name={note.judges[0] ?? null} signatureUrl={note.signatureUrl} />
                <div className="flex flex-wrap gap-2">
                    <LinkButton href={route('bench-notes.show', note.id)} variant="secondary" size="sm">
                        {t('bench.buttons.view')}
                    </LinkButton>
                    {canUpdate && (
                        <LinkButton href={route('bench-notes.edit', note.id)} variant="secondary" size="sm">
                            {t('bench.buttons.edit')}
                        </LinkButton>
                    )}
                    {canDelete && (
                        <ConfirmButton
                            url={route('bench-notes.destroy', note.id)}
                            title={t('bench.confirmations.delete_title')}
                            message={t('bench.confirmations.delete')}
                        >
                            {t('bench.buttons.delete')}
                        </ConfirmButton>
                    )}
                </div>
            </div>
        </Card>
    );
}
