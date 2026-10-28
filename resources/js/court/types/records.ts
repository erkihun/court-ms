import type { LaravelPageMeta } from '@/Components/ui';

/** One row of the case records register (Admin\RecordController::index). */
export interface RecordListRow {
    id: number;
    case_number: string;
    title: string | null;
    applicant_name: string | null;
    case_type: string | null;
    team_name: string | null;
    assignee_name: string | null;
    status: string;
    /** ISO date. */
    filing_date: string | null;
    /** What this viewer may see: a non-reviewer always sees `accepted`. */
    review_status: string;
    reviewer_name: string | null;
    decisions_count: number;
    /** The appeal record exists once the case has a decision or is closed/dismissed. */
    has_appeal_record: boolean;
}

export interface RecordListFilters {
    q: string;
    status: string;
    case_type_id: string;
    assignee_id: string;
    /** ISO dates. */
    from: string;
    to: string;
}

export interface RecordsIndexProps {
    cases: LaravelPageMeta & { data: RecordListRow[] };
    filters: RecordListFilters;
    statuses: string[];
    types: { id: number; name: string }[];
    users: { id: number; name: string }[];
    isReviewer: boolean;
}

export interface RecordDocument {
    id: number | null;
    label: string | null;
    path: string | null;
    /** ISO 8601. */
    created_at: string | null;
}

/** The case record page (Admin\RecordController::show): the case and every section of its file. */
export interface RecordShowProps {
    case: {
        id: number;
        case_number: string | null;
        title: string | null;
        status: string | null;
        case_type: string | null;
        /** ISO dates. */
        filing_date: string | null;
        first_hearing_date: string | null;
        applicant_name: string | null;
        applicant_email: string | null;
        applicant_phone: string | null;
        respondent_name: string | null;
        respondent_address: string | null;
        /** Sanitized HTML. */
        description: string | null;
        relief_requested: string | null;
    };
    /** ISO 8601, when the page was generated. */
    generatedAt: string;
    benchNotes: { id: number; title: string | null; note: string | null; author: string | null; created_at: string | null }[];
    submittedDocuments: RecordDocument[];
    otherDocuments: RecordDocument[];
    witnesses: { id: number; full_name: string | null; phone: string | null; email: string | null; address: string | null }[];
    respondentResponses: {
        id: number;
        title: string | null;
        case_number: string | null;
        /** Plain text, shortened. */
        description: string | null;
        pdf_path: string | null;
        download_url: string | null;
        created_at: string | null;
    }[];
    letters: {
        id: number;
        subject: string | null;
        reference_number: string | null;
        approval_status: string | null;
        author_name: string | null;
        /** Sanitized HTML. */
        body: string | null;
        created_at: string | null;
    }[];
    hearings: { id: number; hearing_at: string | null; type: string | null; location: string | null; judge_notes: string | null; notes: string | null }[];
    decision: { id: number; title: string | null; content: string | null; created_at: string | null } | null;
    evidences: { id: number; title: string | null; description: string | null; created_at: string | null }[];
    /** Case files plus evidence items, the count the "Other documents" header shows. */
    filesCount: number;
    hasAppealRecord: boolean;
}

/** The sections of the record page; one is open at a time, as on the Blade page. */
export type RecordSection =
    | 'case-summary'
    | 'case-details'
    | 'respondent-responses'
    | 'letters'
    | 'hearings'
    | 'bench-notes'
    | 'final-decision'
    | 'other-documents';
