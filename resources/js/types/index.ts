/**
 * Props shared with every page by App\Http\Middleware\HandleInertiaRequests.
 * Keep in sync with that class.
 */

export type Locale = 'en' | 'am';

export interface AppInfo {
    name: string;
    shortName: string;
    logoUrl: string | null;
    footerText: string;
    locale: Locale;
    translationsUrl: string;
    showLanguageSwitcher: boolean;
    session: {
        lifetimeMinutes: number;
        warningMinutes: number;
        extendOnActivity: boolean;
    };
    today: {
        long: string;
        short: string;
    };
}

export interface RoleOption {
    id: number;
    name: string;
    label: string;
}

export interface StaffUser {
    id: number;
    name: string;
    email: string;
    avatarUrl: string | null;
    activeRole: RoleOption | null;
    roles: RoleOption[];
    /** Effective permission names; ['*'] for administrators. UI hint only — the server authorises. */
    permissions: string[];
}

export interface PartyUser {
    id: number;
    fullName: string;
    firstName: string;
    email: string;
    isLawyer: boolean;
    actingAsRespondent: boolean;
}

export interface Auth {
    user: StaffUser | null;
    applicant: PartyUser | null;
}

export type ToastType = 'success' | 'error' | 'warning' | 'info';

export interface FlashToast {
    type: ToastType;
    message: string;
    details: string[];
}

export interface NavLink {
    type: 'link';
    key: string;
    label: string;
    href: string;
    icon: string;
    active: boolean;
}

export interface NavGroup {
    type: 'group';
    key: string;
    label: string;
    icon: string;
    active: boolean;
    children: NavLink[];
}

export interface NavSection {
    key: string;
    label: string | null;
    items: Array<NavLink | NavGroup>;
}

export interface SharedProps {
    app: AppInfo;
    auth: Auth;
    navigation: NavSection[] | null;
    toasts: FlashToast[];
    errors: Record<string, string>;
    [key: string]: unknown;
}

/** Page props = the page's own props plus the shared ones. */
export type PageProps<T extends Record<string, unknown> = Record<string, unknown>> = T & SharedProps;

/** Laravel's LengthAwarePaginator, as serialised for the frontend. */
export interface Paginated<T> {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number | null;
    to: number | null;
    links: Array<{ url: string | null; label: string; active: boolean }>;
}

/** A value shown to the user, already formatted by the server for the current locale/calendar. */
export interface DisplayDate {
    iso: string | null;
    display: string;
}

export interface AdminNotificationItem {
    type: 'message' | 'case' | 'hearing' | 'respondent_view';
    sourceId: number;
    caseId: number;
    caseNumber: string;
    href: string;
    text: string;
    when: string | null;
}

export interface AdminNotificationFeed {
    count: number;
    groups: Array<{ type: AdminNotificationItem['type']; items: AdminNotificationItem[] }>;
}
