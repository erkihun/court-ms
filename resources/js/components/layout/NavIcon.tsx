import {
    AdjustmentsHorizontalIcon,
    BellIcon,
    BookmarkIcon,
    BriefcaseIcon,
    CalendarDaysIcon,
    ChartBarIcon,
    ChartBarSquareIcon,
    CheckBadgeIcon,
    ClipboardDocumentCheckIcon,
    ClipboardIcon,
    Cog6ToothIcon,
    DocumentDuplicateIcon,
    DocumentTextIcon,
    EnvelopeIcon,
    EnvelopeOpenIcon,
    EyeIcon,
    HomeIcon,
    InformationCircleIcon,
    KeyIcon,
    MegaphoneIcon,
    PencilSquareIcon,
    RectangleStackIcon,
    ShieldCheckIcon,
    Squares2X2Icon,
    TagIcon,
    UserGroupIcon,
    UserIcon,
    UsersIcon,
} from '@heroicons/react/24/outline';
import type { ComponentType, SVGProps } from 'react';

type Icon = ComponentType<SVGProps<SVGSVGElement>>;

/** Icon names used by App\Navigation\AdminNavigation. */
const icons: Record<string, Icon> = {
    'adjustments-horizontal': AdjustmentsHorizontalIcon,
    bell: BellIcon,
    bookmark: BookmarkIcon,
    briefcase: BriefcaseIcon,
    'calendar-days': CalendarDaysIcon,
    'chart-bar': ChartBarIcon,
    'chart-bar-square': ChartBarSquareIcon,
    'check-badge': CheckBadgeIcon,
    clipboard: ClipboardIcon,
    'clipboard-document-check': ClipboardDocumentCheckIcon,
    'cog-6-tooth': Cog6ToothIcon,
    'document-duplicate': DocumentDuplicateIcon,
    'document-text': DocumentTextIcon,
    envelope: EnvelopeIcon,
    'envelope-open': EnvelopeOpenIcon,
    eye: EyeIcon,
    home: HomeIcon,
    'information-circle': InformationCircleIcon,
    key: KeyIcon,
    megaphone: MegaphoneIcon,
    'pencil-square': PencilSquareIcon,
    'rectangle-stack': RectangleStackIcon,
    'shield-check': ShieldCheckIcon,
    'squares-2x2': Squares2X2Icon,
    tag: TagIcon,
    user: UserIcon,
    'user-group': UserGroupIcon,
    users: UsersIcon,
};

export function NavIcon({ name, className }: { name: string; className?: string }) {
    const Component = icons[name] ?? DocumentTextIcon;

    return <Component className={className} aria-hidden="true" />;
}
