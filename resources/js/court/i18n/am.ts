// court-ms's dictionary, merged over the EUISIS one (scripts/sync-from-euisis.mjs patches i18n/registry.ts).
// `cm.*` is court-ms's own lang/am/*.php, exported by `php artisan court:client-lang`, so a React
// screen says what the Blade screen it replaces said; `court.*` is text only the React screens need.
import lang from './lang/am';

export default {
    cm: lang,
    court: {
        workspaceName: 'የፍርድ ቤት ጉዳዮች አስተዳደር',
        nav: {
            landing: 'የመግቢያ ገጽ',
        },
        dashboard: {
            period: 'ጊዜ',
            ranges: {
                all: 'ሁሉም ጊዜ',
                '7d': 'ያለፉት 7 ቀናት',
                '30d': 'ያለፉት 30 ቀናት',
                '90d': 'ያለፉት 90 ቀናት',
                '6m': 'ያለፉት 6 ወራት',
                '12m': 'ያለፉት 12 ወራት',
            },
            channels: {
                mail: 'ኢሜይል',
                sms: 'ኤስኤምኤስ',
                telegram: 'ቴሌግራም',
                in_app: 'በሲስተሙ ውስጥ',
            },
        },
    },
};
