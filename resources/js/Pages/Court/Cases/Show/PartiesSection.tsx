import { Link, router } from '@inertiajs/react';
import { useState } from 'react';
import LocalizedDateDisplay from '@/Components/Calendar/LocalizedDateDisplay';
import { Alert, Button, FormField, Textarea, badgeClassName, buttonClassName, cx, type Tone } from '@/Components/ui';
import { useConfirm } from '@/hooks/useConfirm';
import type { CaseParties, CaseParty, CasePartyRepresentative } from '@/court/types/caseDetail';
import SectionCard, { EmptyBox } from './SectionCard';
import { UsersIcon } from './icons';
import { useCaseText } from './useCaseText';

const STATUS_TONES: Record<CaseParty['status'], Tone> = {
    active: 'success', admitted: 'success', invited: 'warning', requested: 'info', declined: 'neutral', refused: 'danger', removed: 'neutral',
};
const REPRESENTATION_TONES: Record<CasePartyRepresentative['status'], Tone> = { pending: 'warning', accepted: 'success', refused: 'danger', revoked: 'neutral' };

/** The notice on a case that was merged into another: the case it lives on in now. */
export function MergedBanner({ mergedInto }: { mergedInto: NonNullable<CaseParties['mergedInto']> }) {
    const { t } = useCaseText();

    return (
        <Alert tone="warning">
            <div className="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <p className="font-semibold">{t('cm.case_parties.panel.merge.merged_into').replace(':number', mergedInto.case_number)}</p>
                    {mergedInto.note && <p className="mt-1">{mergedInto.note}</p>}
                </div>
                <Link href={route('cases.show', mergedInto.id)} className={buttonClassName({ variant: 'outline', size: 'sm' })}>{t('cm.case_parties.panel.merge.open')}</Link>
            </div>
        </Alert>
    );
}

/**
 * The parties on the applicant's side (EUISIS ADR 0013 #13): the applicant,
 * co-applicants who confirmed in the employee portal, interveners and their
 * lawyers. The judge of the case admits or refuses an intervener; the registry
 * accepts or refuses a lawyer's power of attorney; a judge or registrar merges
 * open cases against the same respondent into this one.
 */
export default function PartiesSection({ caseId, caseNumber, parties }: { caseId: number; caseNumber: string; parties: CaseParties }) {
    const { t } = useCaseText();
    const p = (key: string) => t(`cm.case_parties.panel.${key}`);
    const { confirm } = useConfirm();
    const [busy, setBusy] = useState(false);
    const [picked, setPicked] = useState<number[]>([]);
    const [note, setNote] = useState('');
    const [mergeError, setMergeError] = useState<string | null>(null);
    const options = { preserveScroll: true, onStart: () => setBusy(true), onFinish: () => setBusy(false) };

    const decide = async (party: CaseParty, admit: boolean) => {
        const result = await confirm({
            title: `${admit ? p('admit') : p('refuse')}: ${party.name}`,
            variant: admit ? 'success' : 'danger',
            confirmLabel: admit ? p('admit') : p('refuse'),
            cancelLabel: p('cancel'),
            requireReason: !admit,
            reasonLabel: p('reason'),
        });
        if (!result.confirmed) return;
        router.post(route('cases.parties.decide', [caseId, party.id]), { decision: admit ? 'admit' : 'refuse', note: result.reason ?? '' }, options);
    };

    const verify = async (lawyer: CasePartyRepresentative, decision: 'accept' | 'refuse' | 'revoke') => {
        const result = await confirm({
            title: `${p(decision)}: ${lawyer.name}`,
            variant: decision === 'accept' ? 'success' : 'danger',
            confirmLabel: p(decision),
            cancelLabel: p('cancel'),
            requireReason: decision !== 'accept',
            reasonLabel: p('reason'),
        });
        if (!result.confirmed) return;
        router.post(route('cases.representatives.verify', [caseId, lawyer.id]), { decision, note: result.reason ?? '' }, options);
    };

    const merge = async () => {
        setMergeError(null);
        const result = await confirm({
            title: p('merge.confirm_title'),
            description: p('merge.confirm').replace(':count', String(picked.length)).replace(':number', caseNumber),
            variant: 'warning',
            confirmLabel: p('merge.submit'),
            cancelLabel: p('cancel'),
        });
        if (!result.confirmed) return;
        router.post(route('cases.merge', caseId), { cases: picked, note }, {
            ...options,
            onSuccess: () => { setPicked([]); setNote(''); },
            onError: (errors) => setMergeError(Object.values(errors)[0] ?? null),
        });
    };

    return (
        <SectionCard id="parties" title={p('title')} icon={<UsersIcon />} count={parties.rows.length}>
            {parties.rows.length === 0 ? <EmptyBox>{p('none')}</EmptyBox> : (
                <ul className={cx('space-y-3', busy && 'opacity-60')}>
                    {parties.rows.map((party) => (
                        <li key={party.id} className="rounded-control border border-(--app-border) p-4">
                            <div className="flex flex-wrap items-start justify-between gap-3">
                                <div className="min-w-0">
                                    <p className="font-semibold text-(--app-foreground)">{party.name}</p>
                                    <p className="mt-0.5 text-xs text-(--app-muted-foreground)">
                                        {party.source === 'merge' && party.merged_from
                                            ? p('sources.merge').replace(':number', party.merged_from)
                                            : t(`cm.case_parties.panel.sources.${party.source}`)}
                                        {party.created_at && <> · <LocalizedDateDisplay value={party.created_at} /></>}
                                    </p>
                                    {!party.linked && party.status === 'invited' && <p className="mt-1 text-xs text-(--app-muted-foreground)">{p('no_account')}</p>}
                                </div>
                                <div className="flex flex-wrap items-center gap-2">
                                    <span className={badgeClassName('info')}>{p(`roles.${party.role}`)}</span>
                                    <span className={badgeClassName(STATUS_TONES[party.status] ?? 'neutral')}>{p(`statuses.${party.status}`)}</span>
                                </div>
                            </div>

                            {party.role === 'intervener' && (
                                <div className="mt-3 space-y-2 border-t border-(--app-border) pt-3 text-sm">
                                    {party.statement && (
                                        <div>
                                            <p className="text-[11px] font-semibold uppercase tracking-wider text-(--app-muted-foreground)">{p('grounds')}</p>
                                            <p className="mt-1 whitespace-pre-line text-(--app-foreground)">{party.statement}</p>
                                        </div>
                                    )}
                                    {party.attachment_url && <a href={party.attachment_url} className="inline-block text-sm font-medium text-primary hover:underline">{p('attachment')}</a>}
                                    {party.decided_at && (
                                        <p className="text-xs text-(--app-muted-foreground)">
                                            {p('decided_by')}: {party.decided_by ?? '—'} · <LocalizedDateDisplay value={party.decided_at} />
                                            {party.decision_note && <> · {p('decision_note')}: {party.decision_note}</>}
                                        </p>
                                    )}
                                    {party.status === 'requested' && (parties.can.decide ? (
                                        <div className="flex flex-wrap gap-2 pt-1">
                                            <Button type="button" size="sm" disabled={busy} onClick={() => { void decide(party, true); }}>{p('admit')}</Button>
                                            <Button type="button" size="sm" variant="destructiveOutline" disabled={busy} onClick={() => { void decide(party, false); }}>{p('refuse')}</Button>
                                        </div>
                                    ) : <p className="text-xs text-(--app-muted-foreground)">{p('judge_only')}</p>)}
                                </div>
                            )}

                            {party.representatives.length > 0 && (
                                <div className="mt-3 border-t border-(--app-border) pt-3">
                                    <p className="text-[11px] font-semibold uppercase tracking-wider text-(--app-muted-foreground)">{p('lawyers')}</p>
                                    <ul className="mt-2 space-y-2">
                                        {party.representatives.map((lawyer) => (
                                            <li key={lawyer.id} className="flex flex-wrap items-center justify-between gap-3 rounded-control bg-(--app-surface-muted) px-3 py-2.5 text-sm">
                                                <div className="min-w-0">
                                                    <p className="font-medium text-(--app-foreground)">{lawyer.name}</p>
                                                    <p className="text-xs text-(--app-muted-foreground)">
                                                        {p('license')}: {lawyer.license_number ?? '—'}
                                                        {lawyer.phone && <> · {lawyer.phone}</>}
                                                        {lawyer.email && <> · {lawyer.email}</>}
                                                    </p>
                                                    {!lawyer.confirmed_by_party && <p className="text-xs text-amber-700 dark:text-amber-400">{p('not_confirmed')}</p>}
                                                    {lawyer.note && <p className="text-xs text-(--app-muted-foreground)">{lawyer.note}</p>}
                                                </div>
                                                <div className="flex flex-wrap items-center gap-2">
                                                    <span className={badgeClassName(REPRESENTATION_TONES[lawyer.status])}>{p(`representation.${lawyer.status}`)}</span>
                                                    {lawyer.power_of_attorney_url && <a href={lawyer.power_of_attorney_url} className={buttonClassName({ variant: 'outline', size: 'sm' })}>{p('power_of_attorney')}</a>}
                                                    {parties.can.verify && lawyer.status === 'pending' && (
                                                        <>
                                                            <Button type="button" size="sm" disabled={busy} onClick={() => { void verify(lawyer, 'accept'); }}>{p('accept')}</Button>
                                                            <Button type="button" size="sm" variant="destructiveOutline" disabled={busy} onClick={() => { void verify(lawyer, 'refuse'); }}>{p('refuse')}</Button>
                                                        </>
                                                    )}
                                                    {parties.can.verify && lawyer.status === 'accepted' && (
                                                        <Button type="button" size="sm" variant="ghost" disabled={busy} onClick={() => { void verify(lawyer, 'revoke'); }}>{p('revoke')}</Button>
                                                    )}
                                                </div>
                                            </li>
                                        ))}
                                    </ul>
                                </div>
                            )}
                        </li>
                    ))}
                </ul>
            )}

            {parties.mergedFrom.length > 0 && (
                <div className="rounded-control border border-(--app-border) p-4">
                    <p className="text-sm font-semibold text-(--app-foreground)">{p('merge.merged_from')}</p>
                    <ul className="mt-2 flex flex-wrap gap-2">
                        {parties.mergedFrom.map((origin) => (
                            <li key={origin.id}>
                                <Link href={route('cases.show', origin.id)} className={buttonClassName({ variant: 'outline', size: 'sm' })}>{origin.case_number}</Link>
                            </li>
                        ))}
                    </ul>
                </div>
            )}

            {parties.can.merge && (
                <div className="space-y-3 rounded-control border border-dashed border-(--app-border) p-4">
                    <div>
                        <p className="text-sm font-semibold text-(--app-foreground)">{p('merge.title')}</p>
                        <p className="mt-1 text-xs text-(--app-muted-foreground)">{p('merge.help')}</p>
                    </div>
                    {parties.candidates.length === 0 ? <p className="text-sm text-(--app-muted-foreground)">{p('merge.none')}</p> : (
                        <>
                            <fieldset>
                                <legend className="mb-2 text-sm font-medium text-(--app-foreground)">{p('merge.pick')}</legend>
                                <ul className="max-h-64 space-y-1 overflow-y-auto">
                                    {parties.candidates.map((candidate) => (
                                        <li key={candidate.id}>
                                            <label className="flex cursor-pointer items-center gap-3 rounded-control px-2 py-1.5 text-sm hover:bg-(--app-surface-muted)">
                                                <input type="checkbox" checked={picked.includes(candidate.id)} className="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary"
                                                    onChange={(event) => setPicked((current) => event.target.checked ? [...current, candidate.id] : current.filter((id) => id !== candidate.id))} />
                                                <span className="font-mono text-xs font-semibold">{candidate.case_number}</span>
                                                <span className="min-w-0 truncate text-(--app-foreground)">{candidate.title}</span>
                                                {candidate.filed_at && <span className="ml-auto shrink-0 text-xs text-(--app-muted-foreground)"><LocalizedDateDisplay value={candidate.filed_at} /></span>}
                                            </label>
                                        </li>
                                    ))}
                                </ul>
                            </fieldset>
                            <FormField label={p('merge.note')} required error={mergeError ?? undefined}>
                                {({ id, describedBy }) => <Textarea id={id} rows={3} value={note} onChange={(event) => setNote(event.target.value)} aria-describedby={describedBy} />}
                            </FormField>
                            <Button type="button" variant="secondary" disabled={busy || picked.length === 0 || note.trim() === ''} onClick={() => { void merge(); }}>{p('merge.submit')}</Button>
                        </>
                    )}
                </div>
            )}
        </SectionCard>
    );
}
