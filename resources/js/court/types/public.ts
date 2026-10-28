/**
 * Props of the public site pages (Court/Public/*), the landing-page manager
 * (Court/Landing/Index) and the staff account pages (Court/Auth/*).
 * Dates are ISO 8601 strings.
 */

/** Footer and navigation data the public layout shows (App\Support\Ui\PublicSiteChrome). */
export interface PublicSiteChrome {
    /** The landing manager's footer settings; null when none were saved. */
    footer: LandingFooter | null;
    /** The user manual is switched on, has content and is linked from the navigation. */
    userManualEnabled: boolean;
    /** The manual's title saved for the request's language; null falls back to the translation. */
    userManualTitle?: string | null;
}

export interface PublicTermsProps {
    term: {
        id: number;
        title: string;
        /** Purified on the server (profile `cases`) and again by the viewer. */
        body: string;
        published_at: string | null;
    };
    publicSite: PublicSiteChrome;
}

export interface PublicUserManualProps {
    title: string;
    /** Manual HTML for the request's language, sanitized when it was saved. */
    content: string;
    publicSite: PublicSiteChrome;
}

/* ── Home and resources pages (PublicHomeController) ─────────────────── */

/**
 * One hero slide: the landing manager's slide, or one of the three built-in
 * slides (always in Amharic, whose saved slides are English-only text).
 */
export interface PublicHomeSlide {
    badge: string;
    title: string;
    description: string;
    primary_label: string;
    primary_href: string;
    /** '' hides the second button. */
    secondary_label: string;
    secondary_href: string;
    style: SlideStyle;
    image_url: string | null;
}

/** A section's heading, resolved on the server (saved text, else the translation). */
export interface PublicHomeSection {
    visible: boolean;
    title: string;
    subtitle: string;
}

export type PublicHomeSectionKey = 'metrics' | 'process' | 'services' | 'resources' | 'faq';

export interface PublicHomeMetric {
    key: MetricKey;
    label: string;
    description: string;
    value: number;
}

export interface PublicHomeStep {
    title: string;
    description: string;
    meta: string;
    duration: string;
    color: StepColor;
}

export interface PublicHomeService {
    title: string;
    description: string;
    meta: string;
    features: string[];
    icon: ServiceIcon;
    accent: ServiceAccent;
}

export interface PublicResource {
    id: number;
    title: string;
    type: ResourceType;
    description: string | null;
    is_featured: boolean;
    published_at: string | null;
    /** The uploaded file of a form or document. */
    download_url: string | null;
    external_url: string | null;
    cover_image_url: string | null;
}

export interface PublicHomeProps {
    slides: PublicHomeSlide[];
    sections: Record<PublicHomeSectionKey, PublicHomeSection>;
    /** Empty while the section is hidden, as are the lists below. */
    metrics: PublicHomeMetric[];
    steps: PublicHomeStep[];
    services: PublicHomeService[];
    /** The first six active resources. */
    resources: PublicResource[];
    hasMoreResources: boolean;
    faqs: Array<{ question: string; answer: string }>;
    publicSite: PublicSiteChrome;
}

export interface PublicResourcesProps {
    resources: PublicResource[];
    publicSite: PublicSiteChrome;
}

export interface SignageCase {
    id: number;
    case_number: string | null;
    applicant: string | null;
    case_type: string | null;
}

export interface SignageHearing {
    id: number;
    case_id: number;
    case_number: string | null;
    case_type: string | null;
    hearing_at: string | null;
    /** The stored time of day (HH:MM:SS), as the Blade board printed it. */
    time: string | null;
}

export interface PublicSignageProps {
    now: string;
    appName: string;
    logoUrl: string | null;
    totalCases: number;
    statusCounts: Array<{ label: string; total: number }>;
    categoryCounts: Array<{ name: string; total: number }>;
    todayCases: SignageCase[];
    todayHearings: SignageHearing[];
    activeAnnouncements: Array<{ id: number; title: string; content: string; created_at: string | null }>;
    activeStaff: Array<{ id: number; name: string; position: string | null; avatar_url: string | null }>;
    /** Seconds between automatic refreshes of the board. */
    refreshSeconds: number;
}

/* ── Landing-page manager ─────────────────────────────────────────────── */

export type LandingTab = 'slides' | 'metrics' | 'timeline' | 'services' | 'resources' | 'manual' | 'faqs' | 'footer' | 'sections';

export type SlideStyle = 'blue' | 'orange' | 'emerald';
export type StepColor = 'blue' | 'orange' | 'emerald' | 'violet' | 'amber';
export type ServiceAccent = StepColor | 'pink';
export type ServiceIcon = 'document' | 'calendar' | 'lock' | 'chart' | 'database' | 'chat';
export type ResourceType = 'post' | 'form' | 'document' | 'link';

export interface LandingSlide {
    id: number;
    badge: string | null;
    title: string;
    description: string | null;
    primary_label: string;
    primary_href: string;
    secondary_label: string | null;
    secondary_href: string | null;
    bg_style: SlideStyle;
    bg_image: string | null;
    bg_image_url: string | null;
    is_active: boolean;
}

export interface LandingStep {
    id: number;
    title: string;
    description: string | null;
    meta: string | null;
    duration: string | null;
    color: StepColor;
    is_active: boolean;
}

export interface LandingService {
    id: number;
    title: string;
    description: string | null;
    meta: string | null;
    features: string[];
    icon_type: ServiceIcon;
    accent: ServiceAccent;
    is_active: boolean;
}

export interface LandingResource {
    id: number;
    title: string;
    type: ResourceType;
    description: string | null;
    external_url: string | null;
    is_featured: boolean;
    is_active: boolean;
    published_at: string | null;
    /** `published_at` as a datetime-local value (Y-m-d\TH:i). */
    published_at_local: string | null;
    is_scheduled: boolean;
    file_path: string | null;
    cover_image: string | null;
    cover_image_url: string | null;
}

export interface LandingFaq {
    id: number;
    question: string;
    answer: string;
    is_active: boolean;
}

export type MetricKey = 'total_cases' | 'resolved_cases' | 'pending_cases' | 'active_caseload' | 'hearings_this_week' | 'avg_resolution_time';

export interface MetricText {
    label: string;
    description: string;
}

export interface SectionSetting {
    visible: boolean;
    title?: string | null;
    subtitle?: string | null;
    description?: string | null;
    primary_label?: string | null;
    primary_href?: string | null;
    secondary_label?: string | null;
    secondary_href?: string | null;
}

export type SectionKey = 'metrics' | 'process' | 'services' | 'cases' | 'resources' | 'faq' | 'cta';

export interface LandingFooter {
    description: string | null;
    contact_phone: string | null;
    contact_email: string | null;
    contact_address: string | null;
    social_facebook: string | null;
    social_twitter: string | null;
    social_linkedin: string | null;
    social_youtube: string | null;
    social_telegram: string | null;
    social_instagram: string | null;
}

export interface LandingUserManual {
    title_en: string;
    title_am: string;
    content_en: string;
    content_am: string;
    is_active: boolean;
}

export interface LandingIndexProps {
    initialTab: LandingTab;
    homeUrl: string;
    slides: LandingSlide[];
    steps: LandingStep[];
    services: LandingService[];
    resources: LandingResource[];
    faqs: LandingFaq[];
    /** Live figures the statistics cards show. */
    stats: Record<MetricKey, number>;
    /** Saved card labels and descriptions ('' when not overridden). */
    metrics: Record<MetricKey, MetricText>;
    /** The English defaults, shown as placeholders. */
    metricDefaults: Record<MetricKey, MetricText>;
    sections: Record<SectionKey, SectionSetting>;
    footer: Record<keyof LandingFooter, string>;
    userManual: LandingUserManual;
}

/* ── Staff account pages ──────────────────────────────────────────────── */

export interface ForgotPasswordProps {
    status: string | null;
    email: string;
}
