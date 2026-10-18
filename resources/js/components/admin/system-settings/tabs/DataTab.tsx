import { ArchiveBoxArrowDownIcon, CircleStackIcon, ClipboardDocumentListIcon } from '@heroicons/react/24/outline';
import { useState } from 'react';
import { Button } from '@/components/ui/Button';
import { ConfirmDialog } from '@/components/ui/ConfirmDialog';
import { Field } from '@/components/ui/Field';
import { t } from '@/lib/i18n';
import { route } from '@/lib/route';
import { InfoRows, SettingsCard, ToggleRow, UnitInput } from '../controls';
import type { SettingsFormHandle } from '../types';

export interface DatabaseMetrics {
    driver: string;
    database: string;
    connection: string;
    table_count: number;
    size: string | null;
    migration_batch: number | null;
    backup_supported: boolean;
}

/** The backup streams a file download, which an XHR visit cannot hand to the browser, so post a real form. */
function downloadBackup(csrfToken: string): void {
    const form = document.createElement('form');
    form.method = 'post';
    form.action = route('settings.system.databaseBackup');
    const token = document.createElement('input');
    token.type = 'hidden';
    token.name = '_token';
    token.value = csrfToken;
    form.append(token);
    document.body.append(form);
    form.submit();
    form.remove();
}

export function DataTab({ form, metrics, csrfToken }: { form: SettingsFormHandle; metrics: DatabaseMetrics; csrfToken: string }) {
    const { data, setData, errors } = form;
    const [confirming, setConfirming] = useState(false);

    return (
        <div className="grid gap-4 lg:grid-cols-2">
            <SettingsCard title={t('settings.data_overview')} icon={<CircleStackIcon />}>
                <p className="mb-3 text-xs text-(--text-subtle)">{t('settings.data_overview_hint')}</p>
                <InfoRows
                    rows={[
                        { label: t('settings.data_driver'), value: metrics.driver.toUpperCase() || 'unknown' },
                        { label: t('settings.data_connection'), value: metrics.connection || '-' },
                        { label: t('settings.data_database'), value: metrics.database || '-' },
                        { label: t('settings.data_tables'), value: metrics.table_count.toLocaleString() },
                        { label: t('settings.data_size'), value: metrics.size ?? '-' },
                        { label: t('settings.data_migration_batch'), value: metrics.migration_batch ?? '-' },
                    ]}
                />
            </SettingsCard>

            <SettingsCard
                title={t('settings.backup_database')}
                icon={<ArchiveBoxArrowDownIcon />}
                badge={
                    <span className="text-[11px] font-semibold text-(--text-subtle)">
                        {metrics.backup_supported ? t('settings.backup_supported') : t('settings.backup_not_supported')}
                    </span>
                }
            >
                <p className="mb-4 text-xs text-(--text-subtle)">{t('settings.backup_database_hint')}</p>
                <Button variant="secondary" disabled={!metrics.backup_supported} onClick={() => setConfirming(true)}>
                    <ArchiveBoxArrowDownIcon className="h-4 w-4" />
                    {t('settings.download_backup')}
                </Button>
                {!metrics.backup_supported && <p className="mt-2 text-xs text-(--text-subtle)">{t('settings.backup_unsupported')}</p>}
                <ConfirmDialog
                    open={confirming}
                    title={t('settings.download_backup')}
                    message={t('settings.download_backup_confirm')}
                    confirmLabel={t('settings.download_backup')}
                    variant="primary"
                    onCancel={() => setConfirming(false)}
                    onConfirm={() => {
                        setConfirming(false);
                        downloadBackup(csrfToken);
                    }}
                />
            </SettingsCard>

            <SettingsCard title={t('settings.audit_log_configuration')} icon={<ClipboardDocumentListIcon />} className="lg:col-span-2">
                <ToggleRow
                    label={t('settings.audit_logging_enabled')}
                    description={t('settings.audit_logging_enabled_desc')}
                    checked={data.audit_logging_enabled}
                    onChange={(checked) => setData('audit_logging_enabled', checked)}
                />
                <Field
                    label={t('settings.audit_retention_days')}
                    hint={t('settings.audit_retention_hint')}
                    error={errors.audit_retention_days}
                    className="mt-4"
                >
                    <UnitInput
                        value={data.audit_retention_days}
                        onChange={(value) => setData('audit_retention_days', value)}
                        unit={t('settings.unit_days')}
                        min={1}
                        max={3650}
                        className="max-w-56"
                    />
                </Field>
            </SettingsCard>
        </div>
    );
}
