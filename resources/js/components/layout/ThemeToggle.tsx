import { ComputerDesktopIcon, MoonIcon, SunIcon } from '@heroicons/react/24/outline';
import { Dropdown } from '@/components/ui/Dropdown';
import { type Accent, type ThemeMode, useTheme } from '@/hooks/useTheme';
import { cn } from '@/lib/cn';
import { t } from '@/lib/i18n';

const modeIcons = { light: SunIcon, dark: MoonIcon, system: ComputerDesktopIcon } as const;
const swatches: Record<Accent, string> = {
    blue: 'bg-blue-600 ring-blue-500',
    teal: 'bg-teal-600 ring-teal-500',
    violet: 'bg-violet-600 ring-violet-500',
    emerald: 'bg-emerald-600 ring-emerald-500',
    rose: 'bg-rose-600 ring-rose-500',
};

/** Light / dark / system mode and accent colour picker. */
export function ThemeToggle({ buttonClassName = 'topnav-icon-btn' }: { buttonClassName?: string }) {
    const { mode, setMode, accent, setAccent, accents } = useTheme();
    const Current = modeIcons[mode];

    return (
        <Dropdown
            panelClassName="w-52 p-3"
            trigger={({ open, toggle }) => (
                <button type="button" onClick={toggle} className={buttonClassName} aria-label={t('app.Theme')} aria-expanded={open}>
                    <Current className="h-[1.05rem] w-[1.05rem]" strokeWidth={1.8} />
                </button>
            )}
        >
            <p className="mb-2 text-[10px] font-semibold uppercase tracking-wider text-(--text-subtle)">{t('ui.theme.mode')}</p>
            <div className="mb-4 flex gap-1.5">
                {(Object.keys(modeIcons) as ThemeMode[]).map((option) => {
                    const Icon = modeIcons[option];
                    return (
                        <button
                            key={option}
                            type="button"
                            onClick={() => setMode(option)}
                            aria-pressed={mode === option}
                            className={cn(
                                'flex flex-1 flex-col items-center gap-1 rounded-lg py-2 text-[11px] font-medium transition-all duration-150',
                                mode === option ? 'bg-(--border-strong) text-(--text) ring-2 ring-[rgb(var(--ac))]' : 'text-(--text-muted) hover:bg-(--border)',
                            )}
                        >
                            <Icon className="h-4 w-4" strokeWidth={1.8} />
                            {t(`ui.theme.${option}`)}
                        </button>
                    );
                })}
            </div>
            <p className="mb-2 text-[10px] font-semibold uppercase tracking-wider text-(--text-subtle)">{t('ui.theme.accent')}</p>
            <div className="flex items-center gap-2">
                {accents.map((option) => (
                    <button
                        key={option}
                        type="button"
                        onClick={() => setAccent(option)}
                        aria-pressed={accent === option}
                        aria-label={option}
                        className={cn(
                            'h-6 w-6 rounded-full transition-all duration-150',
                            swatches[option],
                            accent === option ? 'ring-2 ring-offset-2' : 'opacity-70 ring-0 hover:opacity-100',
                        )}
                    />
                ))}
            </div>
        </Dropdown>
    );
}
