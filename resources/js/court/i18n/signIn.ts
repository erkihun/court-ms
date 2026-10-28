/**
 * The court staff sign-in (Pages/Court/Auth/Login) and the page that tells
 * applicants and respondents their cases are now in the employee portal
 * (Pages/Court/PortalMoved). Merged into court/i18n/{en,am}.ts as `court.signIn`.
 */
const signIn = {
    en: {
        eyebrow: 'Addis Ababa City Administration',
        title: 'Court staff sign-in',
        subtitle: 'For the registry, judges, inspectors and administrators of the tribunal.',
        panelHeading: 'One record for every case',
        panelText: 'From filing to the final decision, kept in the register of the tribunal.',
        steps: {
            filing: { title: 'Filing', text: 'Filed by the employee in the Agelgay employee portal' },
            review: { title: 'Review', text: 'The registry checks the file and assigns it' },
            hearing: { title: 'Hearing', text: 'Hearings scheduled and the parties notified' },
            decision: { title: 'Decision', text: 'Drafted, published and served on the parties' },
            appeal: { title: 'Appeal', text: 'Contested decisions followed to their end' },
        },
        securityNote: 'Authorised tribunal staff only. Every sign-in is recorded.',
        partiesTitle: 'Applicant or employee?',
        partiesText: 'Applicants file and follow their cases in the Agelgay employee portal, under My Portal › My court cases.',
        partiesLink: 'Go to My Portal',
        movedTitle: 'Court cases have moved to My Portal',
        movedText: 'Employees file, follow and answer their tribunal cases in the Agelgay employee portal. This court portal is no longer used.',
        movedAction: 'Open My Portal',
        movedNoLink: 'Sign in to Agelgay and open My Portal › My court cases.',
        staffLink: 'Court staff sign-in',
    },
    am: {
        eyebrow: 'የአዲስ አበባ ከተማ አስተዳደር',
        title: 'የፍርድ ቤት ሠራተኞች መግቢያ',
        subtitle: 'ለፍርድ ቤቱ መዝገብ ቤት ሠራተኞች፣ ዳኞች፣ ኢንስፔክተሮችና አስተዳዳሪዎች።',
        panelHeading: 'ለእያንዳንዱ ጉዳይ አንድ መዝገብ',
        panelText: 'ከክስ አቀራረብ እስከ የመጨረሻ ውሳኔ፣ በፍርድ ቤቱ መዝገብ ውስጥ ይያዛል።',
        steps: {
            filing: { title: 'ክስ ማቅረብ', text: 'ሠራተኛው ክሱን በአገልጋይ የሠራተኛ ፖርታል ያቀርባል' },
            review: { title: 'ግምገማ', text: 'መዝገብ ቤቱ መዝገቡን መርምሮ ይመድባል' },
            hearing: { title: 'ችሎት', text: 'ችሎት ይቀጠራል፤ ተከራካሪዎችም እንዲያውቁት ይደረጋል' },
            decision: { title: 'ውሳኔ', text: 'ውሳኔ ተጽፎ ይታተማል፤ ለተከራካሪዎች ይደርሳል' },
            appeal: { title: 'ይግባኝ', text: 'ይግባኝ የተባለባቸው ውሳኔዎች እስከ መጨረሻው ይከታተላሉ' },
        },
        securityNote: 'ለተፈቀደላቸው የፍርድ ቤት ሠራተኞች ብቻ። እያንዳንዱ መግቢያ ይመዘገባል።',
        partiesTitle: 'አመልካች ወይም ሠራተኛ ነዎት?',
        partiesText: 'አመልካቾች ጉዳያቸውን በአገልጋይ የሠራተኛ ፖርታል፣ በየእኔ ፖርታል › የፍርድ ቤት ጉዳዮቼ ያቀርባሉ፤ ይከታተላሉም።',
        partiesLink: 'ወደ የእኔ ፖርታል ይሂዱ',
        movedTitle: 'የፍርድ ቤት ጉዳዮች ወደ የእኔ ፖርታል ተዛውረዋል',
        movedText: 'ሠራተኞች የፍርድ ቤት ጉዳዮቻቸውን በአገልጋይ የሠራተኛ ፖርታል ያቀርባሉ፣ ይከታተላሉ፣ ምላሽም ይሰጣሉ። ይህ የፍርድ ቤት ፖርታል ከአሁን በኋላ አያገለግልም።',
        movedAction: 'የእኔ ፖርታልን ክፈት',
        movedNoLink: 'ወደ አገልጋይ ገብተው የእኔ ፖርታል › የፍርድ ቤት ጉዳዮቼን ይክፈቱ።',
        staffLink: 'የፍርድ ቤት ሠራተኞች መግቢያ',
    },
};

export default signIn;
