import type { LaravelPageMeta } from '@/Components/ui';

/** One row of the decisions list (Admin\DecisionController::index). */
export interface DecisionRow {
    id: number;
    case_id: number | null;
    case_number: string | null;
    /** ISO date. */
    case_filed_date: string | null;
    applicant: string | null;
    respondent: string | null;
    judge: string | null;
    /** ISO date. */
    decision_date: string | null;
    status: string | null;
    approved: boolean;
    /** The middle judge may edit and delete a decision that is not published. */
    can_edit: boolean;
    can_delete: boolean;
}

export interface DecisionsIndexProps {
    decisions: LaravelPageMeta & { data: DecisionRow[] };
    filters: { q: string };
    canCreate: boolean;
}
