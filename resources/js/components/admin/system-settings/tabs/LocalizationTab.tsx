import { GlobeAltIcon, LanguageIcon } from '@heroicons/react/24/outline';
import { Field } from '@/components/ui/Field';
import { Select } from '@/components/ui/Input';
import { cn } from '@/lib/cn';
import { t } from '@/lib/i18n';
import { SettingsCard, ToggleRow } from '../controls';
import type { SettingsFormHandle } from '../types';

const FLAGS: Record<string, string> = { en: '🇬🇧', am: '🇪🇹' };
const SAMPLES: Record<string, string> = { en: 'Hello, welcome to the portal.', am: 'ወደ ፖርታሉ እንኳን ደህና መጡ።' };
const DATE_FORMATS: Array<[string, string]> = [
    ['Y-m-d', '2026-06-11 (ISO)'],
    ['d/m/Y', '11/06/2026'],
    ['m/d/Y', '06/11/2026'],
    ['d-m-Y', '11-06-2026'],
    ['F j, Y', 'June 11, 2026'],
];

interface Props {
    form: SettingsFormHandle;
    locales: Array<{ code: string; name: string }>;
    timezones: string[];
    savedLocale: string;
}

export function LocalizationTab({ form, locales, timezones, savedLocale }: Props) {
    const { data, setData, errors } = form;

    return (
        <>
            <SettingsCard title={t('settings.default_language')} icon={<LanguageIcon />} className="mb-4">
                <fieldset className="grid gap-3 sm:grid-cols-2">
                    <legend className="sr-only">{t('settings.default_language')}</legend>
                    {locales.map((locale) => {
                        const selected = data.default_locale === locale.code;

                        return (
                            <label
                                key={locale.code}
                                className={cn(
                                    'cursor-pointer rounded-xl border-2 p-3.5 transition-colors',
                                    selected
                                        ? 'border-[rgb(var(--ac))] bg-[rgb(var(--ac)/0.05)]'
                                        : 'border-(--border) bg-(--surface-soft) hover:border-[rgb(var(--ac)/0.35)]',
                                )}
                            >
                                <input
                                    type="radio"
                                    name="default_locale"
                                    value={locale.code}
                                    checked={selected}
                                    onChange={() => setData('default_locale', locale.code)}
                                    className="sr-only"
                                />
                                <span className="flex items-center gap-2">
                                    <span aria-hidden="true">{FLAGS[locale.code] ?? '🌐'}</span>
                                    <span className="text-sm font-semibold text-(--text)">{locale.name}</span>
                                    {savedLocale === locale.code && (
                                        <span className="ms-auto text-[11px] font-semibold text-[rgb(var(--ac))]">{t('settings.active')}</span>
                                    )}
                                </span>
                                <span className="mt-1 block text-xs text-(--text-subtle)">{SAMPLES[locale.code] ?? ''}</span>
                            </label>
                        );
                    })}
                </fieldset>
                {errors.default_locale && <p className="mt-2 text-xs font-medium text-rose-600">{errors.default_locale}</p>}
                <div className="mt-4">
                    <ToggleRow
                        label={t('settings.show_lang_switcher')}
                        description={t('settings.show_lang_switcher_desc')}
                        checked={data.show_language_switcher}
                        onChange={(checked) => setData('show_language_switcher', checked)}
                    />
                </div>
            </SettingsCard>
            <SettingsCard title={t('settings.region_formats')} icon={<GlobeAltIcon />}>
                <div className="grid gap-4 sm:grid-cols-3">
                    <Field label={t('settings.timezone')} error={errors.timezone}>
                        <Select value={data.timezone} onChange={(event) => setData('timezone', event.target.value)}>
                            {timezones.map((zone) => (
                                <option key={zone} value={zone}>
                                    {zone}
                                </option>
                            ))}
                        </Select>
                    </Field>
                    <Field label={t('settings.date_format')} error={errors.date_format}>
                        <Select value={data.date_format} onChange={(event) => setData('date_format', event.target.value)}>
                            {DATE_FORMATS.map(([value, label]) => (
                                <option key={value} value={value}>
                                    {label}
                                </option>
                            ))}
                        </Select>
                    </Field>
                    <Field label={t('settings.time_format')} error={errors.time_format}>
                        <Select value={data.time_format} onChange={(event) => setData('time_format', event.target.value)}>
                            <option value="H:i">{t('settings.time_24')}</option>
                            <option value="h:i A">{t('settings.time_12')}</option>
                        </Select>
                    </Field>
                </div>
                <div className="mt-4">
                    <ToggleRow
                        label={t('settings.ethiopian_calendar')}
                        description={t('settings.ethiopian_calendar_desc')}
                        checked={data.use_ethiopian_calendar}
                        onChange={(checked) => setData('use_ethiopian_calendar', checked)}
                    />
                </div>
            </SettingsCard>
        </>
    );
}
