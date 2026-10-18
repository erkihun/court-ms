/** Props for the admin letter, letter template and decision template pages. Keep in sync with their controllers. */

export interface LetterCategory {
    id: number;
    name: string;
    description: string | null;
}

/** An uploaded header/footer image of a template. */
export interface TemplateImage {
    url: string;
    name: string;
}

export interface LetterTemplateRow {
    id: number;
    title: string;
    category: string | null;
    placeholders: string[];
    updated: string;
}

export interface LetterTemplateData {
    id: number;
    title: string;
    category: string;
    /** Comma-separated, as edited in the form. */
    placeholders: string;
    body: string;
    headerImage: TemplateImage | null;
    footerImage: TemplateImage | null;
}

export interface DecisionTemplateRow extends LetterTemplateRow {
    isDefault: boolean;
}

export interface DecisionTemplateData extends LetterTemplateData {
    isDefault: boolean;
}

export type LetterApprovalStatus = 'pending' | 'approved' | 'returned' | 'rejected';

export interface LetterRow {
    id: number;
    referenceNumber: string | null;
    subject: string | null;
    caseNumber: string | null;
    sendToApplicant: boolean;
    sendToRespondent: boolean;
    applicantName: string | null;
    /** Set when a lawyer filed the case on the applicant's behalf. */
    lawyerName: string | null;
    respondentName: string | null;
    authorName: string | null;
    authorPosition: string | null;
    date: string;
    time: string;
    status: LetterApprovalStatus | null;
    approvedByName: string | null;
    previewUrl: string;
}

export interface LetterStats {
    total: number;
    approved: number;
    rejected: number;
    categories: number;
    categorisedLetters: number;
    topCategory: string | null;
    topCategoryCount: number;
}

/** A template offered on the compose page. */
export interface ComposeTemplate {
    id: number;
    title: string;
    category: string | null;
    placeholders: string[];
    body: string;
    excerpt: string;
}

/** Editable fields shared by composing and editing a letter. */
export interface LetterFormData {
    recipient_name: string;
    recipient_title: string;
    recipient_company: string;
    case_number: string;
    cc: string;
    send_to_applicant: boolean;
    send_to_respondent: boolean;
    subject: string;
    body: string;
}
