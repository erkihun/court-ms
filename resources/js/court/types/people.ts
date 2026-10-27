import type { LaravelPageMeta } from '@/Components/ui';

/** One row of the applicants list (Admin\ApplicantController::index). */
export interface ApplicantRow {
    id: number;
    full_name: string;
    national_id_number: string | null;
    email: string | null;
    phone: string | null;
    position: string | null;
    organization_name: string | null;
    is_active: boolean;
    verified: boolean;
    /** ISO 8601. */
    created_at: string | null;
}

export interface ApplicantsIndexProps {
    applicants: LaravelPageMeta & { data: ApplicantRow[] };
    filters: { q: string; status: '' | 'active' | 'inactive' };
    canManage: boolean;
}

/** One row of the users list (Admin\UsersController::index). */
export interface UserRow {
    id: number;
    name: string;
    email: string;
    avatar_url: string | null;
    has_signature: boolean;
    roles: string[];
    status: string;
    verified: boolean;
    /** ISO 8601. */
    created_at: string | null;
    /** What this viewer may do with the row (the user policy). */
    can: { view: boolean; update: boolean; delete: boolean };
}

export interface UsersIndexProps {
    users: LaravelPageMeta & { data: UserRow[] };
    filters: { q: string; status: '' | 'active' | 'inactive' };
    canCreate: boolean;
}
