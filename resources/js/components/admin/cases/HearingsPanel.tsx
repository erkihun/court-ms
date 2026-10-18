import { LockClosedIcon, PlusIcon } from '@heroicons/react/24/outline';
import { useForm } from '@inertiajs/react';
import { type FormEvent, useMemo, useState } from 'react';
import { Badge } from '@/components/ui/Badge';
import { Button } from '@/components/ui/Button';
import { ConfirmButton } from '@/components/ui/ConfirmButton';
import { EthCalendar, EthDateInput } from '@/components/ui/EthDateInput';
import { Field } from '@/components/ui/Field';
import { Input, Select, Textarea } from '@/components/ui/Input';
import { Modal } from '@/components/ui/Modal';
import { useShared } from '@/hooks/useShared';
import { t } from '@/lib/i18n';
import { route } from '@/lib/route';
import type { CaseHearing } from '@/types/admin/case';
import { CaseSection, EmptyBox, SectionTable, td, th } from './CaseSection';

/** Court hours, 30-minute steps, as Gregorian 24h "HH:MM" (what hearing_at stores). */
const SLOTS = Array.from({ length: 17 }, (_, index) => {
    const minutes = 8 * 60 + index * 30;
    return `${String(Math.floor(minutes / 60)).padStart(2, '0')}:${String(minutes % 60).padStart(2, '0')}`;
});

/** Same rule as EthiopianDate::formatEthiopianTime: Ethiopian hour = (hour + 6) % 12. */
function slotLabel(slot: string, amharic: boolean): string {
    const [hour = 0, minute = 0] = slot.split(':').map(Number);
    const mm = String(minute).padStart(2, '0');
    if (amharic) {
        const ethiopian = (hour + 6) % 12 || 12;
        return `${String(ethiopian).padStart(2, '0')}:${mm} ${hour < 12 ? 'ከሰአት በፊት' : 'ከሰአት በኋላ'}`;
    }
    return `${hour % 12 || 12}:${mm} ${hour < 12 ? 'AM' : 'PM'}`;
}

function TimeSelect({ value, onChange }: { value: string; onChange: (value: string) => void }) {
    const amharic = useShared().app.locale === 'am';
    // Keep an existing off-grid time selectable when editing.
    const slots = SLOTS.includes(value) || value === '' ? SLOTS : [...SLOTS, value].sort();

    return (
        <Select value={value} onChange={(event) => onChange(event.target.value)} required>
            {slots.map((slot) => (
                <option key={slot} value={slot}>
                    {slotLabel(slot, amharic)}
                </option>
            ))}
        </Select>
    );
}

interface HearingFields {
    date: string;
    time: string;
    type: string;
    location: string;
    notes: string;
}

const toPayload = (data: HearingFields) => ({ hearing_at: `${data.date}T${data.time}:00`, type: data.type, location: data.location, notes: data.notes });

interface HearingsSectionProps {
    caseId: number;
    hearings: CaseHearing[];
    locked: boolean;
    canCreate: boolean;
    canUpdate: boolean;
    canDelete: boolean;
}

export function HearingsSection({ caseId, hearings, locked, canCreate, canUpdate, canDelete }: HearingsSectionProps) {
    const [editing, setEditing] = useState<CaseHearing | null>(null);
    const hearingDates = useMemo(() => new Set(hearings.map((hearing) => hearing.date)), [hearings]);

    return (
        <CaseSection id="hearings" title={t('cases.hearings.title')} count={hearings.length} countLabel={t('cases.hearings.total')}>
            {!locked && canCreate && <NewHearingForm caseId={caseId} hearingDates={hearingDates} />}

            {hearings.length === 0 ? (
                <EmptyBox>{t('cases.hearings.no_hearings')}</EmptyBox>
            ) : (
                <SectionTable
                    head={
                        <tr>
                            <th className={`${th} w-12`}>#</th>
                            <th className={th}>{t('cases.show.when')}</th>
                            <th className={th}>{t('cases.hearings.type_placeholder')}</th>
                            <th className={th}>{t('cases.hearings.location_placeholder')}</th>
                            <th className={th}>{t('cases.hearings.notes_placeholder')}</th>
                            <th className={th}>{t('cases.table.actions')}</th>
                        </tr>
                    }
                >
                    {hearings.map((hearing, index) => (
                        <tr key={hearing.id}>
                            <td className={td}>{index + 1}</td>
                            <td className={`${td} font-medium text-(--text)`}>{hearing.display}</td>
                            <td className={td}>{hearing.type ?? '—'}</td>
                            <td className={td}>{hearing.location ?? '—'}</td>
                            <td className={`${td} whitespace-pre-line`}>{hearing.notes ?? '—'}</td>
                            <td className={td}>
                                <div className="flex flex-wrap gap-2">
                                    {!locked && !hearing.locked && canUpdate && (
                                        <Button size="sm" variant="secondary" onClick={() => setEditing(hearing)}>
                                            {t('cases.general.edit')}
                                        </Button>
                                    )}
                                    {!locked && !hearing.locked && canDelete && (
                                        <ConfirmButton
                                            url={route('cases.hearings.delete', hearing.id)}
                                            title={t('cases.hearings.remove_confirm')}
                                            message={hearing.display}
                                        >
                                            {t('cases.general.delete')}
                                        </ConfirmButton>
                                    )}
                                    {hearing.locked && (
                                        <Badge tone="warning" className="inline-flex items-center gap-1">
                                            <LockClosedIcon className="h-3 w-3" />
                                            {t('cases.show.hearing_locked')}
                                        </Badge>
                                    )}
                                </div>
                            </td>
                        </tr>
                    ))}
                </SectionTable>
            )}

            {editing && <EditHearingDialog hearing={editing} hearingDates={hearingDates} onClose={() => setEditing(null)} />}
        </CaseSection>
    );
}

function NewHearingForm({ caseId, hearingDates }: { caseId: number; hearingDates: ReadonlySet<string> }) {
    const form = useForm<HearingFields>({ date: '', time: '08:00', type: '', location: '', notes: '' });

    const pickDate = (date: string) => {
        form.setData('date', date);
        form.clearErrors('hearing_at' as keyof HearingFields);
    };

    const submit = (event: FormEvent) => {
        event.preventDefault();
        if (!form.data.date) {
            form.setError('date', t('cases.show.select_hearing_date'));
            return;
        }
        form.transform(toPayload);
        form.post(route('cases.hearings.store', caseId), { preserveScroll: true, onSuccess: () => form.reset() });
    };

    const errors = form.errors as Partial<Record<keyof HearingFields | 'hearing_at', string>>;

    return (
        <form onSubmit={submit} className="grid gap-5 rounded-xl border border-(--border) bg-(--surface-soft) p-4 lg:grid-cols-[18rem_1fr]" noValidate>
            <div>
                <div className="mb-2 flex items-center justify-between">
                    <h3 className="text-sm font-semibold text-(--text)">{t('cases.show.hearing_calendar')}</h3>
                </div>
                <div className="rounded-lg border border-(--border) bg-(--surface-strong) p-3">
                    <EthCalendar value={form.data.date} onChange={pickDate} markedDates={hearingDates} disabledDates={hearingDates} bare />
                </div>
                <p className="mt-2 text-xs text-(--text-subtle)">{t('cases.show.hearing_calendar_hint')}</p>
            </div>

            <div className="space-y-4">
                <h3 className="text-sm font-semibold text-(--text)">{t('cases.hearings.add_new_hearing')}</h3>
                <div className="grid gap-4 sm:grid-cols-2">
                    <Field label={t('cases.show.hearing_date')} error={errors.date ?? errors.hearing_at} required>
                        <EthDateInput value={form.data.date} onChange={pickDate} disabledDates={hearingDates} markedDates={hearingDates} />
                    </Field>
                    <Field label={t('cases.show.hearing_time')} required>
                        <TimeSelect value={form.data.time} onChange={(value) => form.setData('time', value)} />
                    </Field>
                    <Field label={t('cases.hearings.type_placeholder')} error={errors.type} required>
                        <Input value={form.data.type} onChange={(event) => form.setData('type', event.target.value)} maxLength={255} required />
                    </Field>
                    <Field label={t('cases.hearings.location_placeholder')} error={errors.location}>
                        <Input value={form.data.location} onChange={(event) => form.setData('location', event.target.value)} maxLength={255} />
                    </Field>
                </div>
                <Field label={t('cases.hearings.notes_placeholder')} error={errors.notes} required>
                    <Textarea rows={2} value={form.data.notes} onChange={(event) => form.setData('notes', event.target.value)} required />
                </Field>
                <Button type="submit" variant="success" loading={form.processing}>
                    <PlusIcon className="h-4 w-4" />
                    {t('cases.hearings.add_hearing')}
                </Button>
            </div>
        </form>
    );
}

function EditHearingDialog({ hearing, hearingDates, onClose }: { hearing: CaseHearing; hearingDates: ReadonlySet<string>; onClose: () => void }) {
    const form = useForm<HearingFields>({
        date: hearing.date,
        time: hearing.time,
        type: hearing.type ?? '',
        location: hearing.location ?? '',
        notes: hearing.notes ?? '',
    });
    // Other hearings' days are taken; this hearing's own day stays available.
    const taken = useMemo(() => new Set([...hearingDates].filter((date) => date !== hearing.date)), [hearingDates, hearing.date]);
    const errors = form.errors as Partial<Record<keyof HearingFields | 'hearing_at', string>>;

    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.transform(toPayload);
        form.patch(route('cases.hearings.update', hearing.id), { preserveScroll: true, onSuccess: onClose });
    };

    return (
        <Modal
            open
            onClose={onClose}
            title={t('cases.general.edit')}
            footer={
                <>
                    <Button variant="secondary" onClick={onClose}>
                        {t('cases.general.cancel')}
                    </Button>
                    <Button type="submit" form="edit-hearing-form" loading={form.processing}>
                        {t('cases.general.save')}
                    </Button>
                </>
            }
        >
            <form id="edit-hearing-form" onSubmit={submit} className="space-y-4" noValidate>
                <div className="grid gap-4 sm:grid-cols-2">
                    <Field label={t('cases.show.hearing_date')} error={errors.date ?? errors.hearing_at} required>
                        <EthDateInput value={form.data.date} onChange={(value) => form.setData('date', value)} disabledDates={taken} markedDates={taken} />
                    </Field>
                    <Field label={t('cases.show.hearing_time')} required>
                        <TimeSelect value={form.data.time} onChange={(value) => form.setData('time', value)} />
                    </Field>
                </div>
                <Field label={t('cases.hearings.type_placeholder')} error={errors.type} required>
                    <Input value={form.data.type} onChange={(event) => form.setData('type', event.target.value)} maxLength={255} required />
                </Field>
                <Field label={t('cases.hearings.location_placeholder')} error={errors.location}>
                    <Input value={form.data.location} onChange={(event) => form.setData('location', event.target.value)} maxLength={255} />
                </Field>
                <Field label={t('cases.hearings.notes_placeholder')} error={errors.notes} required>
                    <Textarea rows={3} value={form.data.notes} onChange={(event) => form.setData('notes', event.target.value)} required />
                </Field>
            </form>
        </Modal>
    );
}
