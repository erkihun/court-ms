export type LandingTab = 'slides' | 'metrics' | 'timeline' | 'services' | 'resources' | 'manual' | 'faqs' | 'footer' | 'sections';

export interface Slide {
    id: number;
    badge: string;
    title: string;
    description: string;
    primaryLabel: string;
    primaryHref: string;
    secondaryLabel: string;
    secondaryHref: string;
    bgStyle: 'blue' | 'orange' | 'emerald';
    bgImageUrl: string | null;
    isActive: boolean;
}

export interface Step {
    id: number;
    title: string;
    description: string;
    meta: string;
    duration: string;
    color: string;
    isActive: boolean;
}

export interface Service {
    id: number;
    title: string;
    description: string;
    meta: string;
    features: string[];
    iconType: string;
    accent: string;
    isActive: boolean;
}

export interface Resource {
    id: number;
    title: string;
    type: 'post' | 'form' | 'document' | 'link';
    description: string;
    externalUrl: string;
    isFeatured: boolean;
    publishedAt: string | null;
    scheduledFor: string | null;
    fileUrl: string | null;
    fileName: string | null;
    coverUrl: string | null;
    isActive: boolean;
}

export interface Faq {
    id: number;
    question: string;
    answer: string;
    isActive: boolean;
}

export interface MetricCard {
    key: string;
    value: string;
    label: string;
    description: string;
    placeholderLabel: string;
    placeholderDescription: string;
}

export interface ManualValues {
    title_en: string;
    title_am: string;
    content_en: string;
    content_am: string;
    is_active: boolean;
}

export interface SectionValues {
    visible: boolean;
    title?: string;
    subtitle?: string;
    description?: string;
    primary_label?: string;
    primary_href?: string;
    secondary_label?: string;
    secondary_href?: string;
}

export type Sections = Record<'metrics' | 'process' | 'services' | 'cases' | 'resources' | 'faq' | 'cta', SectionValues>;

export type FooterValues = Record<
    | 'description'
    | 'contact_phone'
    | 'contact_email'
    | 'contact_address'
    | 'social_facebook'
    | 'social_twitter'
    | 'social_linkedin'
    | 'social_youtube'
    | 'social_telegram'
    | 'social_instagram',
    string
>;
