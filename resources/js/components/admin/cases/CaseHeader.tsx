import {
    ArrowLeftIcon,
    ArrowUturnLeftIcon,
    CheckIcon,
    ChevronDownIcon,
    ClipboardDocumentListIcon,
    DocumentTextIcon,
    ExclamationTriangleIcon,
    EnvelopeIcon,
    PencilSquareIcon,
    PlusIcon,
    UserGroupIcon,
    XMarkIcon,
} from '@heroicons/react/24/outline';
import { router, useForm } from '@inertiajs/react';
import { type FormEvent, type ReactNode, useState } from 'react';
import { Alert } from '@/components/ui/Alert';
import { Button, LinkButton } from '@/components/ui/Button';
import { CopyButton } from '@/components/ui/CopyButton';
import { Dropdown } from '@/components/ui/Dropdown';
import { Field } from '@/components/ui/Field';
import { Input, Select, Textarea } from '@/components/ui/Input';
import { Modal } from '@/components/ui/Modal';
import { t } from '@/lib/i18n';
import { route } from '@/lib/route';
import type { AdminCase, CaseAbilities, CaseLinks } from '@/types/admin/case';
import { CaseStatusBadge, ReviewStatusBadge } from './CaseBadges';

interface CaseHeaderProps {
    courtCase: AdminCase;
    abilities: CaseAbilities;
    links: CaseLinks;
}

export function CaseHeader({ courtCase, abilities, links }: CaseHeaderProps) {
    const [reviewing, setReviewing] = useState<'return' | 'reject' | null>(null);
    const canReview = abilities.review && (courtCase.reviewStatus === 'awaiting_review' || courtCase.reviewStatus === 'returned');
    const open = !courtCase.locked;

    const accept = () => router.patch(route('cases.review.update', courtCase.id), { decision: 'accept', note: '' }, { preserveScroll: true });

    const meta = [
        { label: t('cases.summary.filing_date'), value: courtCase.filingDate },
        { label: t('cases.summary.applicant'), value: courtCase.title },
        { label: t('cases.table.type'), value: courtCase.caseType },
        { label: t('cases.summary.assignee'), value: courtCase.assignee?.name },
    ].filter((item) => item.value);

    return (
        <div className="ui-card mb-5 overflow-hidden">
            <div className="h-1 w-full" style={{ background: 'linear-gradient(90deg, rgb(var(--ac)) 0%, rgb(var(--ac-light)) 100%)' }} />
            <div className="px-5 py-5">
                <div className="flex flex-col gap-5 xl:flex-row xl:items-center xl:justify-between">
                    <div className="min-w-0 flex-1">
                        <p className="mb-1.5 text-[10.5px] font-semibold tracking-[.16em] text-(--text-subtle) uppercase">{t('cases.case_number')}</p>
                        <div className="flex flex-wrap items-center gap-3">
                            <h1 className="font-mono text-2xl font-bold tracking-tight text-(--text)">{courtCase.caseNumber}</h1>
                            <CopyButton value={courtCase.caseNumber} />
                            <CaseStatusBadge status={courtCase.status} />
                            <ReviewStatusBadge status={courtCase.reviewStatus} />
                        </div>
                        {meta.length > 0 && (
                            <div className="mt-3 flex flex-wrap gap-x-6 gap-y-1 text-[12.5px] text-(--text-muted)">
                                {meta.map((item) => (
                                    <span key={item.label}>
                                        <span className="text-(--text-subtle)">{item.label}:</span> {item.value}
                                    </span>
                                ))}
                            </div>
                        )}
                    </div>

                    <div className="flex flex-wrap items-center gap-2 print:hidden">
                        {abilities.manageInspectionRequests && (
                            <LinkButton href={links.inspectionRequest} size="sm">
                                <ClipboardDocumentListIcon className="h-3.5 w-3.5" />
                                {t('cases.show.inspection_request')}
                            </LinkButton>
                        )}
                        {canReview && (
                            <Dropdown
                                trigger={({ open: menuOpen, toggle }) => (
                                    <Button size="sm" variant="secondary" onClick={toggle} aria-expanded={menuOpen} aria-haspopup="menu">
                                        {t('cases.show.review_actions')}
                                        <ChevronDownIcon className="h-3.5 w-3.5" />
                                    </Button>
                                )}
                                panelClassName="w-48 py-1.5"
                            >
                                {(close) => (
                                    <div role="menu">
                                        <MenuItem
                                            icon={<CheckIcon className="h-4 w-4" />}
                                            className="text-emerald-600"
                                            onClick={() => {
                                                close();
                                                accept();
                                            }}
                                        >
                                            {t('cases.show.accept')}
                                        </MenuItem>
                                        <MenuItem
                                            icon={<ArrowUturnLeftIcon className="h-4 w-4" />}
                                            className="text-amber-600"
                                            onClick={() => {
                                                close();
                                                setReviewing('return');
                                            }}
                                        >
                                            {t('cases.show.return')}
                                        </MenuItem>
                                        <MenuItem
                                            icon={<XMarkIcon className="h-4 w-4" />}
                                            className="text-red-600"
                                            onClick={() => {
                                                close();
                                                setReviewing('reject');
                                            }}
                                        >
                                            {t('cases.show.reject')}
                                        </MenuItem>
                                    </div>
                                )}
                            </Dropdown>
                        )}
                        {open && abilities.assign && (
                            <LinkButton href={links.assign} size="sm">
                                <UserGroupIcon className="h-3.5 w-3.5" />
                                {t('cases.assign_change')}
                            </LinkButton>
                        )}
                        {open && abilities.manageBenchNotes && (
                            <LinkButton href={links.benchNotes} size="sm" variant="secondary">
                                <PencilSquareIcon className="h-3.5 w-3.5" />
                                {t('cases.show.bench_note')}
                            </LinkButton>
                        )}
                        {open && abilities.writeLetter && (
                            <LinkButton href={links.composeLetter} size="sm" variant="success">
                                <EnvelopeIcon className="h-3.5 w-3.5" />
                                {t('cases.show.write_letter')}
                            </LinkButton>
                        )}
                        {open && courtCase.status === 'closed' && (
                            <LinkButton href={links.decision} size="sm">
                                <PlusIcon className="h-3.5 w-3.5" />
                                {t('cases.show.give_decision')}
                            </LinkButton>
                        )}
                        <LinkButton href={route('cases.index')} size="sm" variant="secondary">
                            <ArrowLeftIcon className="h-3.5 w-3.5" />
                            {t('cases.back')}
                        </LinkButton>
                    </div>
                </div>

                {courtCase.locked && (
                    <Alert tone="warning" className="mt-4 flex items-center gap-2.5">
                        <ExclamationTriangleIcon className="h-4 w-4 shrink-0" />
                        {t('cases.show.actions_locked')}
                    </Alert>
                )}
            </div>

            <ReviewDialog caseId={courtCase.id} decision={reviewing} initialNote={courtCase.reviewNote ?? ''} onClose={() => setReviewing(null)} />
        </div>
    );
}

function MenuItem({ icon, className, onClick, children }: { icon: ReactNode; className: string; onClick: () => void; children: ReactNode }) {
    return (
        <button
            type="button"
            role="menuitem"
            onClick={onClick}
            className={`flex w-full items-center gap-2.5 px-3.5 py-2 text-sm hover:bg-(--surface-soft) ${className}`}
        >
            {icon}
            {children}
        </button>
    );
}

/** Return-for-correction or reject: both need a note for the applicant. */
function ReviewDialog({
    caseId,
    decision,
    initialNote,
    onClose,
}: {
    caseId: number;
    decision: 'return' | 'reject' | null;
    initialNote: string;
    onClose: () => void;
}) {
    const form = useForm({ decision: '', note: initialNote });

    const submit = (event: FormEvent) => {
        event.preventDefault();
        if (!decision) return;
        form.transform((data) => ({ ...data, decision }));
        form.patch(route('cases.review.update', caseId), { preserveScroll: true, onSuccess: onClose });
    };

    return (
        <Modal
            open={decision !== null}
            onClose={onClose}
            title={decision === 'return' ? t('cases.show.return_for_correction') : t('cases.show.reject_case')}
            footer={
                <>
                    <Button variant="secondary" onClick={onClose}>
                        {t('cases.general.cancel')}
                    </Button>
                    <Button type="submit" form="case-review-form" variant={decision === 'reject' ? 'danger' : 'primary'} loading={form.processing}>
                        {t('cases.show.submit')}
                    </Button>
                </>
            }
        >
            <form id="case-review-form" onSubmit={submit}>
                <Field label={t('cases.show.reason_note')} error={form.errors.note} required>
                    <Textarea
                        rows={4}
                        value={form.data.note}
                        onChange={(event) => form.setData('note', event.target.value)}
                        required
                        maxLength={2000}
                        autoFocus
                    />
                </Field>
            </form>
        </Modal>
    );
}

const STATUS_CHOICES = ['pending', 'active', 'dismissed', 'closed'] as const;

/** Status change for staff with cases.edit; a closed case is final. */
export function StatusChangeCard({ courtCase }: { courtCase: AdminCase }) {
    const form = useForm({
        status: courtCase.reviewStatus === 'awaiting_review' ? 'pending' : courtCase.status,
        note: '',
    });

    if (courtCase.status === 'closed') {
        return (
            <Alert tone="success" className="mb-6">
                <p className="font-semibold">{t('cases.status.closed_locked_title')}</p>
                <p className="mt-1 text-sm">{t('cases.status.closed_locked')}</p>
            </Alert>
        );
    }

    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.patch(route('cases.status.update', courtCase.id), { preserveScroll: true, onSuccess: () => form.reset('note') });
    };

    return (
        <div className="ui-card mb-6 p-5 print:hidden">
            <h2 className="mb-4 flex items-center gap-2 text-base font-semibold text-(--text)">
                <DocumentTextIcon className="h-5 w-5 text-blue-600" />
                {t('cases.status.change_case_status')}
            </h2>
            <form onSubmit={submit} className="grid items-end gap-4 md:grid-cols-3">
                <Field label={t('cases.status.new_status')} error={form.errors.status}>
                    <Select value={form.data.status} onChange={(event) => form.setData('status', event.target.value)}>
                        {STATUS_CHOICES.map((status) => (
                            <option key={status} value={status}>
                                {t(`cases.status.${status}`)}
                            </option>
                        ))}
                    </Select>
                </Field>
                <Field label={t('cases.status.note_to_timeline')} error={form.errors.note}>
                    <Input
                        value={form.data.note}
                        onChange={(event) => form.setData('note', event.target.value)}
                        placeholder={t('cases.status.add_note_placeholder')}
                        maxLength={2000}
                    />
                </Field>
                <div>
                    <Button type="submit" loading={form.processing}>
                        <CheckIcon className="h-4 w-4" />
                        {t('cases.status.update_status')}
                    </Button>
                </div>
            </form>
        </div>
    );
}
