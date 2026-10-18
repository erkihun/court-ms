/** Props of Admin/Cases/Show; see App\Support\Admin\CaseShowPage. */

export type ReviewStatus = 'awaiting_review' | 'accepted' | 'returned' | 'rejected';

export interface AdminCase {
    id: number;
    caseNumber: string;
    /** Shown as the applicant's name on this page. */
    title: string;
    status: string;
    reviewStatus: ReviewStatus;
    reviewNote: string | null;
    caseType: string | null;
    filingDate: string | null;
    applicant: { address: string | null; email: string | null };
    lawyer: { name: string; documentUrl: string | null } | null;
    respondent: { name: string | null; address: string | null };
    assignee: { name: string; teamName: string | null; assignedAt: string | null } | null;
    created: string | null;
    updated: string | null;
    descriptionHtml: string;
    reliefHtml: string;
    /** Closed with a published decision: no further changes. */
    locked: boolean;
}

export interface CaseAbilities {
    editStatus: boolean;
    assign: boolean;
    review: boolean;
    manageResponseReplies: boolean;
    writeLetter: boolean;
    viewLetters: boolean;
    manageBenchNotes: boolean;
    viewHearings: boolean;
    createHearings: boolean;
    updateHearings: boolean;
    deleteHearings: boolean;
    viewFiles: boolean;
    filesSection: boolean;
    createFiles: boolean;
    deleteFiles: boolean;
    createMessages: boolean;
    manageInspectionRequests: boolean;
    manageInspectionFindings: boolean;
    acceptInspectionFindings: boolean;
}

export interface CaseLinks {
    assign: string;
    benchNotes: string;
    composeLetter: string;
    decision: string;
    inspectionRequest: string;
}

export interface CaseHearing {
    id: number;
    /** Gregorian Y-m-d and H:i, as stored. */
    date: string;
    time: string;
    display: string;
    summaryDisplay: string;
    type: string | null;
    location: string | null;
    notes: string | null;
    notesExcerpt: string | null;
    locked: boolean;
}

export interface CaseAudit {
    id: number;
    when: string;
    action: string;
    actor: string;
    meta: string | null;
}

export interface InspectionRequestRow {
    id: number;
    date: string;
    subject: string;
    status: string;
    inspector: string | null;
    createdBy: string | null;
}

export interface InspectionFindingRow {
    id: number;
    date: string;
    title: string;
    accepted: boolean;
    severity: string;
    requestSubject: string | null;
    inspector: string | null;
    recordedBy: string | null;
    details: string;
    attachment: { url: string; name: string | null } | null;
}

export interface CaseDocumentRow {
    id: number;
    title: string;
    descriptionHtml: string | null;
    mime: string | null;
    uploaded: string;
    sizeKb: number | null;
    url: string | null;
}

export interface CaseWitness {
    id: number;
    name: string;
    phone: string | null;
    email: string | null;
    address: string | null;
}

export interface CaseLetterRow {
    id: number;
    reference: string | null;
    subject: string | null;
    template: string | null;
    status: string;
    date: string;
    author: string | null;
    url: string | null;
}

export interface RespondentResponseRow {
    id: number;
    title: string | null;
    status: ReviewStatus;
    created: string;
    caseNumber: string;
    bodyHtml: string;
    reviewNote: string | null;
    pdfUrl: string | null;
}

export interface ResponseReplyRow {
    id: number;
    status: ReviewStatus;
    created: string;
    responseNumber: string | null;
    applicantName: string | null;
    description: string;
    reviewNote: string | null;
    pdfUrl: string | null;
}

export interface CaseMessage {
    id: number;
    fromStaff: boolean;
    who: string;
    when: string;
    body: string;
}

export interface CaseFileRow {
    id: number;
    label: string;
    mime: string | null;
    sizeKb: number;
    uploaded: string;
    by: string | null;
    url: string | null;
}

export interface CaseShowProps {
    case: AdminCase;
    abilities: CaseAbilities;
    links: CaseLinks;
    termsItems: string[];
    printedAt: string;
    hearings: CaseHearing[];
    audits: CaseAudit[];
    inspectionRequests: InspectionRequestRow[];
    inspectionFindings: InspectionFindingRow[];
    documents: CaseDocumentRow[];
    witnesses: CaseWitness[];
    letters: CaseLetterRow[];
    respondentResponses: RespondentResponseRow[];
    responseReplies: ResponseReplyRow[];
    messages: CaseMessage[];
    files: CaseFileRow[];
    [key: string]: unknown;
}
