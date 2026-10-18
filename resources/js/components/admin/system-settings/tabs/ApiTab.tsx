import { ArrowPathIcon, CommandLineIcon, ServerStackIcon } from '@heroicons/react/24/outline';
import { ConfirmButton } from '@/components/ui/ConfirmButton';
import { Field } from '@/components/ui/Field';
import { t } from '@/lib/i18n';
import { route } from '@/lib/route';
import { InfoRows, SettingsCard, ToggleRow, UnitInput } from '../controls';
import type { SettingsFormHandle } from '../types';

export function ApiTab({ form, systemInfo }: { form: SettingsFormHandle; systemInfo: Array<{ label: string; value: string }> }) {
    const { data, setData, errors } = form;

    return (
        <div className="grid gap-4 lg:grid-cols-2">
            <SettingsCard title={t('settings.rest_api')} icon={<CommandLineIcon />} className="lg:col-span-2">
                <ToggleRow
                    label={t('settings.enable_api')}
                    description={t('settings.enable_api_desc')}
                    checked={data.api_enabled}
                    onChange={(checked) => setData('api_enabled', checked)}
                />
                <Field label={t('settings.rate_limit')} hint={t('settings.rate_limit_hint')} error={errors.api_rate_limit} className="mt-4">
                    <UnitInput
                        value={data.api_rate_limit}
                        onChange={(value) => setData('api_rate_limit', value)}
                        unit={t('settings.unit_req_min')}
                        min={1}
                        max={10000}
                        className="max-w-56"
                    />
                </Field>
            </SettingsCard>

            <SettingsCard title={t('settings.system_information')} icon={<ServerStackIcon />}>
                <InfoRows rows={systemInfo.map((row) => ({ label: t(row.label), value: row.value }))} />
            </SettingsCard>

            <SettingsCard title={t('settings.cache_management')} icon={<ArrowPathIcon />}>
                <p className="mb-4 text-xs text-(--text-subtle)">{t('settings.cache_hint')}</p>
                <ConfirmButton
                    url={route('settings.system.clearCache')}
                    method="post"
                    data={{ tab: 'api' }}
                    title={t('settings.clear_caches')}
                    message={t('settings.clear_caches_confirm')}
                    variant="secondary"
                    size="md"
                >
                    {t('settings.clear_caches')}
                </ConfirmButton>
            </SettingsCard>
        </div>
    );
}
