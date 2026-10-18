import { CodeBracketIcon, ComputerDesktopIcon, PaintBrushIcon, SwatchIcon } from '@heroicons/react/24/outline';
import { Field } from '@/components/ui/Field';
import { Textarea } from '@/components/ui/Input';
import { cn } from '@/lib/cn';
import { t } from '@/lib/i18n';
import { SettingsCard, ToggleRow } from '../controls';
import type { SettingsFormHandle } from '../types';

const PALETTES: Array<{ key: string; hex: string }> = [
    { key: 'blue', hex: '#3b82f6' },
    { key: 'teal', hex: '#14b8a6' },
    { key: 'violet', hex: '#8b5cf6' },
    { key: 'emerald', hex: '#10b981' },
    { key: 'rose', hex: '#f43f5e' },
];

const THEMES: Array<{ key: string; preview: string }> = [
    { key: 'light', preview: 'linear-gradient(135deg,#f8fafc 50%,#e2e8f0 100%)' },
    { key: 'dark', preview: 'linear-gradient(135deg,#1e293b 50%,#0f172a 100%)' },
    { key: 'system', preview: 'linear-gradient(135deg,#f8fafc 0%,#f8fafc 50%,#1e293b 50%,#0f172a 100%)' },
];

export function AppearanceTab({ form }: { form: SettingsFormHandle }) {
    const { data, setData, errors } = form;

    return (
        <div className="grid gap-4 lg:grid-cols-2">
            <SettingsCard title={t('settings.accent_palette')} icon={<SwatchIcon />}>
                <p className="mb-3 text-xs text-(--text-subtle)">{t('settings.accent_palette_hint')}</p>
                <fieldset className="flex flex-wrap gap-2">
                    <legend className="sr-only">{t('settings.accent_palette')}</legend>
                    {PALETTES.map((palette) => {
                        const selected = data.accent_palette === palette.key;

                        return (
                            <label
                                key={palette.key}
                                className={cn(
                                    'inline-flex cursor-pointer items-center gap-1.5 rounded-md border-2 px-3 py-1.5 text-xs font-semibold has-focus-visible:ring-2',
                                    selected ? 'border-current' : 'border-transparent',
                                )}
                                style={{ color: palette.hex, background: `${palette.hex}18` }}
                            >
                                <input
                                    type="radio"
                                    name="accent_palette"
                                    value={palette.key}
                                    checked={selected}
                                    onChange={() => setData('accent_palette', palette.key)}
                                    className="sr-only"
                                />
                                <span className="h-2.5 w-2.5 rounded-full" style={{ background: palette.hex }} aria-hidden="true" />
                                {t(`settings.palette_${palette.key}`)}
                            </label>
                        );
                    })}
                </fieldset>
                {errors.accent_palette && <p className="mt-2 text-xs font-medium text-rose-600">{errors.accent_palette}</p>}
            </SettingsCard>

            <SettingsCard title={t('settings.default_theme')} icon={<ComputerDesktopIcon />}>
                <fieldset className="grid grid-cols-3 gap-2.5">
                    <legend className="sr-only">{t('settings.default_theme')}</legend>
                    {THEMES.map((theme) => {
                        const selected = data.default_theme === theme.key;

                        return (
                            <label
                                key={theme.key}
                                className={cn(
                                    'cursor-pointer rounded-xl border-2 p-3 text-center has-focus-visible:ring-2',
                                    selected ? 'border-[rgb(var(--ac))] shadow-[0_0_0_3px_rgb(var(--ac)/0.12)]' : 'border-(--border)',
                                )}
                            >
                                <input
                                    type="radio"
                                    name="default_theme"
                                    value={theme.key}
                                    checked={selected}
                                    onChange={() => setData('default_theme', theme.key)}
                                    className="sr-only"
                                />
                                <span
                                    className="mb-2 block h-10 rounded-md border border-(--border)"
                                    style={{ background: theme.preview }}
                                    aria-hidden="true"
                                />
                                <span className="text-xs font-semibold text-(--text-muted)">{t(`settings.theme_${theme.key}`)}</span>
                            </label>
                        );
                    })}
                </fieldset>
                {errors.default_theme && <p className="mt-2 text-xs font-medium text-rose-600">{errors.default_theme}</p>}
            </SettingsCard>

            <SettingsCard title={t('settings.display_options')} icon={<PaintBrushIcon />}>
                <ToggleRow
                    label={t('settings.show_banner_login')}
                    description={t('settings.show_banner_login_desc')}
                    checked={data.show_banner_on_login}
                    onChange={(checked) => setData('show_banner_on_login', checked)}
                />
            </SettingsCard>

            <SettingsCard
                title={t('settings.custom_css')}
                icon={<CodeBracketIcon />}
                badge={
                    <span className="rounded-full bg-amber-50 px-2 py-0.5 text-[11px] font-semibold text-amber-700 dark:bg-amber-500/10 dark:text-amber-300">
                        {t('settings.custom_css_badge')}
                    </span>
                }
            >
                <Field label={t('settings.custom_css')} hint={t('settings.custom_css_hint')} error={errors.custom_css}>
                    <Textarea
                        value={data.custom_css}
                        onChange={(event) => setData('custom_css', event.target.value)}
                        rows={7}
                        maxLength={10000}
                        spellCheck={false}
                        className="font-mono text-xs"
                        placeholder=":root { --ac: 37 99 235; }"
                    />
                </Field>
            </SettingsCard>
        </div>
    );
}
