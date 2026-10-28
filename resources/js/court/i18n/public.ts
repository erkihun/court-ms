/**
 * Text the React public pages, the landing-page manager and the staff account
 * pages need that no lang/{en,am}/*.php key provides (the Blade pages
 * hard-coded it in English). To be merged into court/i18n/{en,am}.ts under
 * `court.publicPages`; until then court/i18n/usePublicText.ts reads it
 * directly as the fallback.
 */
const publicPages = {
    en: {
        // Landing-page manager
        tabs_label: 'Landing page sections',
        hidden: 'Hidden',
        hide: 'Hide',
        show: 'Show',
        scheduled: 'Scheduled',
        current_image: 'Current image',
        current_file: 'Current file',
        services_default_cards: 'No service cards saved yet — the page shows the 6 built-in default cards.',
        features_placeholder: 'Feature one\nFeature two\nFeature three',
        footer_description_placeholder: 'A brief description of the organization shown in the footer…',
        address_placeholder: 'Addis Ababa, Ethiopia',
        cta_title_placeholder: 'Ready to engage with the tribunal?',
        cta_description_placeholder: 'File a case, track an existing matter…',
        section_visible: 'Show this section on the home page',
        // Public layout
        staff_sign_in: 'Staff sign-in',
        follow_us: 'Follow us',
        // Staff account pages
        secure_access: 'Secure staff access',
    },
    am: {
        tabs_label: 'የመነሻ ገጽ ክፍሎች',
        hidden: 'የተደበቀ',
        hide: 'ደብቅ',
        show: 'አሳይ',
        scheduled: 'የታቀደ፦',
        current_image: 'የአሁኑ ምስል',
        current_file: 'የአሁኑ ፋይል',
        services_default_cards: 'እስካሁን የተቀመጠ የአገልግሎት ካርድ የለም፤ ገጹ አብረው የተዘጋጁትን 6 ነባሪ ካርዶች ያሳያል።',
        features_placeholder: 'የመጀመሪያ ባህሪ\nሁለተኛ ባህሪ\nሦስተኛ ባህሪ',
        footer_description_placeholder: 'በገጹ ግርጌ የሚታይ የተቋሙ አጭር መግለጫ…',
        address_placeholder: 'አዲስ አበባ፣ ኢትዮጵያ',
        cta_title_placeholder: 'ከፍርድ ቤቱ ጋር ለመገናኘት ዝግጁ ነዎት?',
        cta_description_placeholder: 'ጉዳይ ያቅርቡ፣ ያለ ጉዳይን ይከታተሉ…',
        section_visible: 'ይህን ክፍል በመነሻ ገጹ ላይ አሳይ',
        staff_sign_in: 'የሠራተኞች መግቢያ',
        follow_us: 'ይከተሉን',
        secure_access: 'ደህንነቱ የተጠበቀ የሠራተኞች መግቢያ',
    },
} as const;

export type PublicPagesTextKey = keyof typeof publicPages.en;

export default publicPages as { en: Record<PublicPagesTextKey, string>; am: Record<PublicPagesTextKey, string> };
