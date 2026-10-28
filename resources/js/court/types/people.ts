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

/** A role to tick on the account forms (Admin\UsersController::create, edit). */
export interface RoleOption {
    id: number;
    name: string;
    /** The admin role, for anyone who is not an administrator: the server refuses to grant it. */
    disabled?: boolean;
}

/** Who the account is, as the forms and profile read it (UsersController::userIdentity). */
export interface UserIdentity {
    id: number;
    name: string;
    first_name: string;
    middle_name: string;
    last_name: string;
    email: string;
    status: string;
}

/** The account the edit form changes (Admin\UsersController::edit). */
export interface UserFormUser extends UserIdentity {
    gender: string | null;
    /** Gregorian Y-m-d. */
    date_of_birth: string | null;
    national_id: string | null;
    position: string | null;
    phone: string | null;
    address: string | null;
    avatar_url: string | null;
    signature_url: string | null;
    stamp_url: string | null;
    role_ids: number[];
}

export interface UsersCreateProps {
    roles: RoleOption[];
}

export interface UsersEditProps {
    user: UserFormUser;
    roles: RoleOption[];
    /** False for someone editing only their own record: roles, status, password, signature and stamp are not theirs to change. */
    canManage: boolean;
}

/** The profile page (Admin\UsersController::show). */
export interface UserProfile extends UserIdentity {
    avatar_url: string | null;
    signature_url: string | null;
    stamp_url: string | null;
    roles: string[];
    /** ISO 8601. */
    created_at: string | null;
    /** ISO 8601. */
    updated_at: string | null;
}

export interface UsersShowProps {
    user: UserProfile;
    can: { update: boolean };
}
