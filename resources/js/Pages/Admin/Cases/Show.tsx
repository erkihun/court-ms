import { Head } from '@inertiajs/react';
import { useCallback, useEffect, useMemo, useState } from 'react';
import { CaseHeader, StatusChangeCard } from '@/components/admin/cases/CaseHeader';
import { FilesSection } from '@/components/admin/cases/FilesPanel';
import { HearingsSection } from '@/components/admin/cases/HearingsPanel';
import { MessagesSection } from '@/components/admin/cases/MessagesPanel';
import { AuditsSection, InspectionFindingsSection, InspectionRequestsSection, LettersSection } from '@/components/admin/cases/RecordPanels';
import { RespondentResponsesSection, ResponseRepliesSection } from '@/components/admin/cases/ResponsePanels';
import { DetailsSection, SummarySection } from '@/components/admin/cases/SummaryPanels';
import { cn } from '@/lib/cn';
import { t } from '@/lib/i18n';
import type { CaseShowProps } from '@/types/admin/case';

type SectionKey =
    | 'case-summary'
    | 'case-details'
    | 'letters'
    | 'audits'
    | 'inspection-requests'
    | 'inspection-findings'
    | 'uploaded-files'
    | 'hearings'
    | 'messages'
    | 'respondent-responses'
    | 'response-of-response';

interface NavItem {
    key: SectionKey;
    label: string;
    quick: boolean;
}

/** The visible panel follows the URL hash so links like #messages and the back button work. */
function useHashSection(keys: SectionKey[], fallback: SectionKey) {
    const read = useCallback((): SectionKey => {
        const hash = window.location.hash.slice(1) as SectionKey;
        return keys.includes(hash) ? hash : fallback;
    }, [keys, fallback]);
    const [active, setActive] = useState<SectionKey>(read);

    useEffect(() => {
        const sync = () => setActive(read());
        window.addEventListener('hashchange', sync);
        return () => window.removeEventListener('hashchange', sync);
    }, [read]);

    const open = (key: SectionKey) => {
        if (key !== active) window.history.pushState(window.history.state, '', `#${key}`);
        setActive(key);
        document.getElementById(key)?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    };

    return [active, open] as const;
}

export default function CaseShow(props: CaseShowProps) {
    const { case: courtCase, abilities, links } = props;

    const nav = useMemo<NavItem[]>(() => {
        const items: Array<NavItem | false> = [
            { key: 'case-summary', label: t('cases.navigation.case_summary'), quick: false },
            { key: 'case-details', label: t('cases.navigation.case_details'), quick: false },
            { key: 'letters', label: t('cases.navigation.letters'), quick: false },
            { key: 'audits', label: t('cases.navigation.case_audits'), quick: false },
            abilities.manageInspectionRequests && { key: 'inspection-requests', label: t('case_inspections.requests.index_title'), quick: true },
            abilities.manageInspectionFindings && { key: 'inspection-findings', label: t('case_inspections.findings.index_title'), quick: true },
            abilities.filesSection && { key: 'uploaded-files', label: t('cases.navigation.uploaded_files'), quick: true },
            abilities.viewHearings && { key: 'hearings', label: t('cases.navigation.hearings'), quick: true },
            { key: 'messages', label: t('cases.navigation.messages'), quick: true },
            { key: 'respondent-responses', label: t('cases.navigation.respondent_responses'), quick: true },
            abilities.manageResponseReplies && { key: 'response-of-response', label: t('cases.navigation.response_of_response'), quick: true },
        ];
        return items.filter((item): item is NavItem => item !== false);
    }, [abilities]);

    const keys = useMemo(() => nav.map((item) => item.key), [nav]);
    const [active, open] = useHashSection(keys, 'case-summary');

    const navButton = (item: NavItem) => (
        <li key={item.key}>
            <button
                type="button"
                onClick={() => open(item.key)}
                aria-current={active === item.key ? 'true' : undefined}
                className={cn(
                    'w-full rounded-lg px-3 py-2.5 text-start text-sm leading-5 transition-colors',
                    active === item.key ? 'bg-[rgb(var(--ac)/0.18)] font-semibold text-white' : 'text-white/60 hover:bg-white/5 hover:text-white',
                )}
            >
                {item.label}
            </button>
        </li>
    );

    return (
        <>
            <Head title={`${t('cases.case_details')} · ${courtCase.caseNumber}`} />

            <CaseHeader courtCase={courtCase} abilities={abilities} links={links} />
            {abilities.editStatus && <StatusChangeCard courtCase={courtCase} />}

            <div className="grid grid-cols-1 gap-6 lg:grid-cols-12">
                <nav aria-label={t('cases.navigation.title')} className="min-w-0 lg:col-span-3 print:hidden">
                    {/* Phones and tablets: one scrollable strip, so the panel stays near the top. */}
                    <ul className="-mx-1 flex gap-1.5 overflow-x-auto rounded-2xl bg-[#0c1527] p-2 lg:hidden">
                        {nav.map((item) => (
                            <li key={item.key} className="shrink-0">
                                <button
                                    type="button"
                                    onClick={() => open(item.key)}
                                    aria-current={active === item.key ? 'true' : undefined}
                                    className={cn(
                                        'rounded-lg px-3 py-2 text-sm whitespace-nowrap',
                                        active === item.key ? 'bg-[rgb(var(--ac)/0.25)] font-semibold text-white' : 'text-white/65',
                                    )}
                                >
                                    {item.label}
                                </button>
                            </li>
                        ))}
                    </ul>
                    <div className="sticky top-6 hidden overflow-hidden rounded-2xl border border-white/5 bg-[#0c1527] shadow-xl lg:block">
                        <div className="border-b border-white/5 bg-white/[0.03] px-4 py-3 text-[10.5px] font-semibold tracking-[0.14em] text-white/40 uppercase">
                            {t('cases.navigation.title')}
                        </div>
                        <div className="p-2">
                            <ul className="space-y-0.5">{nav.filter((item) => !item.quick).map(navButton)}</ul>
                            <div className="mt-2 border-t border-white/5 pt-3">
                                <p className="mb-1.5 px-3 text-[10px] font-semibold tracking-widest text-white/30 uppercase">
                                    {t('cases.show.quick_sections')}
                                </p>
                                <ul className="space-y-0.5">{nav.filter((item) => item.quick).map(navButton)}</ul>
                            </div>
                        </div>
                    </div>
                </nav>

                <div className="space-y-6 lg:col-span-9">
                    {active === 'case-summary' && <SummarySection courtCase={courtCase} hearings={props.hearings} />}
                    {active === 'case-details' && (
                        <DetailsSection
                            courtCase={courtCase}
                            documents={props.documents}
                            witnesses={props.witnesses}
                            termsItems={props.termsItems}
                            printedAt={props.printedAt}
                        />
                    )}
                    {active === 'letters' && (
                        <LettersSection letters={props.letters} composeUrl={!courtCase.locked && abilities.writeLetter ? links.composeLetter : null} />
                    )}
                    {active === 'audits' && <AuditsSection audits={props.audits} />}
                    {active === 'inspection-requests' && <InspectionRequestsSection requests={props.inspectionRequests} />}
                    {active === 'inspection-findings' && (
                        <InspectionFindingsSection findings={props.inspectionFindings} canAccept={abilities.acceptInspectionFindings} />
                    )}
                    {active === 'uploaded-files' && (
                        <FilesSection
                            caseId={courtCase.id}
                            files={props.files}
                            canUpload={abilities.createFiles && !courtCase.locked}
                            canDelete={abilities.deleteFiles && !courtCase.locked}
                        />
                    )}
                    {active === 'hearings' && (
                        <HearingsSection
                            caseId={courtCase.id}
                            hearings={props.hearings}
                            locked={courtCase.locked}
                            canCreate={abilities.createHearings}
                            canUpdate={abilities.updateHearings}
                            canDelete={abilities.deleteHearings}
                        />
                    )}
                    {active === 'messages' && (
                        <MessagesSection
                            caseId={courtCase.id}
                            caseNumber={courtCase.caseNumber}
                            messages={props.messages}
                            canPost={abilities.createMessages}
                            locked={courtCase.locked}
                        />
                    )}
                    {active === 'respondent-responses' && <RespondentResponsesSection responses={props.respondentResponses} canReview={abilities.review} />}
                    {active === 'response-of-response' && <ResponseRepliesSection caseNumber={courtCase.caseNumber} replies={props.responseReplies} />}
                </div>
            </div>
        </>
    );
}
