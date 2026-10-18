import { useShared } from '@/hooks/useShared';
import { cn } from '@/lib/cn';
import { route } from '@/lib/route';
import { t } from '@/lib/i18n';
import type { Locale } from '@/types';

const LOCALES: Array<{ code: Locale; label: string }> = [
    { code: 'en', label: 'EN' },
    { code: 'am', label: 'AM' },
];

/**
 * EN / AM switch. Plain links on purpose: changing language reloads the page
 * so the new strings are fetched before anything renders.
 */
export function LanguageSwitch({ className }: { className?: string }) {
    const { app } = useShared();

    if (!app.showLanguageSwitcher) {
        return null;
    }

    return (
        <div
            className={cn('flex items-center gap-0.5 rounded-lg border border-slate-200 bg-slate-100 p-0.5 dark:border-slate-700 dark:bg-slate-800', className)}
            role="group"
            aria-label={t('app.Language')}
        >
            {LOCALES.map(({ code, label }) => (
                <a
                    key={code}
                    href={route('language.switch', { locale: code, return: window.location.href })}
                    aria-current={app.locale === code ? 'true' : undefined}
                    lang={code}
                    className={cn(
                        'rounded-md px-2.5 py-1 text-xs font-semibold transition-all duration-150',
                        app.locale === code
                            ? 'bg-white text-slate-900 shadow-xs dark:bg-slate-700 dark:text-white'
                            : 'text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200',
                    )}
                >
                    {label}
                </a>
            ))}
        </div>
    );
}
