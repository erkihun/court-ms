// court-ms's dictionary, merged over the EUISIS one (scripts/sync-from-euisis.mjs patches i18n/registry.ts).
// `cm.*` is court-ms's own lang/en/*.php, exported by `php artisan court:client-lang`, so a React
// screen says what the Blade screen it replaces said; `court.*` is text only the React screens need.
import lang from './lang/en';

export default {
    cm: lang,
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
