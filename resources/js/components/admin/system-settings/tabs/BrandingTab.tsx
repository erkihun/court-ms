import { PhotoIcon } from '@heroicons/react/24/outline';
import { t } from '@/lib/i18n';
import { SettingsCard, UploadField } from '../controls';
import type { FileField, SettingsFormHandle } from '../types';

export type BrandingAssets = Record<FileField, string | null>;

export function BrandingTab({ form, assets }: { form: SettingsFormHandle; assets: BrandingAssets }) {
    const { data, setData, errors } = form;
    const small: Array<{ field: Exclude<FileField, 'banner'>; label: string; hint: string; accept: string; preview: string }> = [
        { field: 'logo', label: t('settings.logo'), hint: t('settings.logo_hint'), accept: '.png,.jpg,.jpeg,.svg,.webp', preview: 'h-12' },
        { field: 'favicon', label: t('settings.favicon'), hint: t('settings.favicon_hint'), accept: '.png,.ico', preview: 'h-8 w-8' },
        { field: 'seal', label: t('settings.official_seal'), hint: t('settings.seal_hint'), accept: '.png', preview: 'h-14 w-14' },
    ];

    return (
        <SettingsCard
            title={t('settings.visual_assets')}
            icon={<PhotoIcon />}
            badge={<span className="text-xs font-normal text-(--text-subtle)">{t('settings.keep_current_file')}</span>}
        >
            <UploadField
                label={t('settings.header_banner')}
                hint={t('settings.banner_hint')}
                accept=".png,.jpg,.jpeg,.webp"
                file={data.banner}
                currentUrl={assets.banner}
                onChange={(file) => setData('banner', file)}
                error={errors.banner}
                previewClassName="max-h-32 w-full"
            />
            <div className="mt-5 grid gap-4 md:grid-cols-3">
                {small.map((asset) => (
                    <UploadField
                        key={asset.field}
                        label={asset.label}
                        hint={asset.hint}
                        accept={asset.accept}
                        file={data[asset.field]}
                        currentUrl={assets[asset.field]}
                        onChange={(file) => setData(asset.field, file)}
                        error={errors[asset.field]}
                        previewClassName={asset.preview}
                    />
                ))}
            </div>
        </SettingsCard>
    );
}
