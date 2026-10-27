import type { LaravelPageMeta } from '@/Components/ui';

/** One team (Admin\TeamController::index). */
export interface TeamRow {
    id: number;
    name: string;
    parent_name: string | null;
    description: string | null;
    members_count: number;
    leader_name: string | null;
    leader_email: string | null;
}

export interface TeamsIndexProps {
    teams: TeamRow[];
}

/** One role (Admin\RolesController::index). */
export interface RoleRow {
    id: number;
    name: string;
    description: string | null;
    permissions: string[];
    users_count: number;
}

export interface RolesIndexProps {
    roles: LaravelPageMeta & { data: RoleRow[] };
}

/** One permission (Admin\PermissionController::index). */
export interface PermissionRow {
    id: number;
    name: string;
    label: string | null;
    description: string | null;
    roles: string[];
    roles_count: number;
    users_count: number;
}

export interface PermissionsIndexProps {
    permissions: LaravelPageMeta & { data: PermissionRow[] };
    filters: { q: string; perPage: number };
}
