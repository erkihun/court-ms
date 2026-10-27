// court-ms's dictionary, merged over the EUISIS one (scripts/sync-from-euisis.mjs patches i18n/registry.ts).
// `cm.*` is court-ms's own lang/en/*.php, exported by `php artisan court:client-lang`, so a React
// screen says what the Blade screen it replaces said; `court.*` is text only the React screens need.
import lang from './lang/en';

export default {
    cm: lang,
    // The sign-in page's story panel (EUISIS Pages/Auth/Login reads these fixed keys, so they keep EUISIS's
    // key names while telling the court's own story: a case from filing to appeal, and what the system holds).
    auth: {
        stage: {
            headline: 'One register. Every case.',
            headlineAccent: 'From filing to decision.',
            steps: {
                city: { title: 'Filing', text: 'An applicant files a case, online or at the counter' },
                institutions: { title: 'Review', text: 'The registry checks the file and assigns it' },
                employees: { title: 'Hearing', text: 'Hearings scheduled, parties notified, notes kept' },
                card: { title: 'Decision', text: 'Drafted from a template, published, served' },
                verified: { title: 'Appeal', text: 'Contested decisions followed to their end' },
            },
            cardTitle: 'Case file',
            cardVerified: 'Decided',
            services: {
                cafeteria: 'Cases',
                transport: 'Hearings',
                performance: 'Decisions',
                grievance: 'Letters',
                activity: 'Inspections',
                reports: 'Reports',
            },
            capsLock: 'Caps Lock is on',
        },
    },
    court: {
        workspaceName: 'Court Case Management',
        nav: {
            landing: 'Landing Page',
        },
        dashboard: {
            period: 'Period',
            ranges: {
                all: 'All time',
                '7d': 'Last 7 days',
                '30d': 'Last 30 days',
                '90d': 'Last 90 days',
                '6m': 'Last 6 months',
                '12m': 'Last 12 months',
            },
            channels: {
                mail: 'Email',
                sms: 'SMS',
                telegram: 'Telegram',
                in_app: 'In-app',
            },
        },
    },
};
