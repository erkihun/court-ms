import type { LaravelPageMeta } from '@/Components/ui';

/** One row of the letters list (Admin\LetterController::index). */
export interface LetterRow {
    id: number;
    reference_number: string | null;
    subject: string | null;
    case_number: string | null;
    send_to_applicant: boolean;
    send_to_respondent: boolean;
    applicant_name: string | null;
    /** The lawyer who filed for the applicant, when one did. */
    applicant_lawyer_name: string | null;
    respondent_name: string | null;
    author_name: string | null;
    author_position: string | null;
    category: string | null;
    approval_status: 'pending' | 'approved' | 'returned' | 'rejected' | null;
    approved_by_name: string | null;
    /** ISO 8601. */
    created_at: string | null;
}

export interface LettersIndexProps {
    letters: LaravelPageMeta & { data: LetterRow[] };
    stats: { total: number; approved: number; rejected: number; categories: number; top_category: string | null; top_category_count: number };
    filters: { status: '' | 'pending' | 'approved' | 'rejected' };
    can: { create: boolean; update: boolean; delete: boolean; approve: boolean };
}
