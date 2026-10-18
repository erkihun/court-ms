import { ArrowLeftIcon } from '@heroicons/react/24/outline';
import { Head } from '@inertiajs/react';
import { type BenchNote, JudgeRow, NoteBody, SignatureBlock } from '@/components/admin/bench-notes/BenchNoteParts';
import { LinkButton } from '@/components/ui/Button';
import { Card } from '@/components/ui/Card';
import { DescriptionList } from '@/components/ui/DescriptionList';
import { PageHeader } from '@/components/ui/PageHeader';
import { useCan } from '@/hooks/useCan';
import { t } from '@/lib/i18n';
import { route } from '@/lib/route';

export default function BenchNoteShow({ note }: { note: BenchNote }) {
    const can = useCan();
    const backHref = route('bench-notes.index', note.caseId ? { case_id: note.caseId } : {});

    return (
        <>
            <Head title={note.title || t('bench.title')} />
            <PageHeader
                title={note.title || t('bench.title')}
                description={note.caseNumber ? `${note.caseNumber}${note.caseTitle ? ` — ${note.caseTitle}` : ''}` : undefined}
                className="mb-6"
                actions={
                    <>
                        <LinkButton href={backHref} variant="secondary">
                            <ArrowLeftIcon className="h-4 w-4" />
                            {t('bench.buttons.back')}
                        </LinkButton>
                        {can('bench-notes.update') && <LinkButton href={route('bench-notes.edit', note.id)}>{t('bench.buttons.edit')}</LinkButton>}
                    </>
                }
            />

            <Card className="mx-auto max-w-4xl">
                <div className="space-y-6">
                    <JudgeRow judges={note.judges} />
                    <DescriptionList
                        columns={3}
                        items={[
                            { label: t('bench.labels.note_date'), value: note.noteDate },
                            { label: t('bench.labels.author'), value: note.author ?? t('bench.meta.unknown') },
                            { label: t('bench.labels.created'), value: `${note.created} ${note.createdTime}` },
                        ]}
                    />
                    <div>
                        <h2 className="mb-2 text-sm font-semibold text-(--text)">{t('bench.sections.note_content')}</h2>
                        <NoteBody html={note.noteHtml} />
                    </div>
                    <div className="border-t border-(--border) pt-4">
                        <SignatureBlock name={note.judges[0] ?? null} signatureUrl={note.signatureUrl} />
                    </div>
                </div>
            </Card>
        </>
    );
}
