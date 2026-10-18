import { ArrowTopRightOnSquareIcon } from '@heroicons/react/24/outline';
import { Head } from '@inertiajs/react';
import { useState } from 'react';
import { FaqsTab } from '@/components/admin/landing/FaqsTab';
import { ResourcesTab } from '@/components/admin/landing/ResourcesTab';
import { ServicesTab } from '@/components/admin/landing/ServicesTab';
import { FooterTab, ManualTab, MetricsTab, SectionsTab } from '@/components/admin/landing/SettingsTabs';
import { SlidesTab } from '@/components/admin/landing/SlidesTab';
import { StepsTab } from '@/components/admin/landing/StepsTab';
import type { Faq, FooterValues, LandingTab, ManualValues, MetricCard, Resource, Sections, Service, Slide, Step } from '@/components/admin/landing/types';
import { LinkButton } from '@/components/ui/Button';
import { PageHeader } from '@/components/ui/PageHeader';
import { Tabs } from '@/components/ui/Tabs';
import { t } from '@/lib/i18n';

interface Props {
    tab: string;
    previewUrl: string;
    slides: Slide[];
    steps: Step[];
    services: Service[];
    resources: Resource[];
    faqs: Faq[];
    manual: ManualValues;
    sections: Sections;
    metrics: MetricCard[];
    footer: FooterValues;
}

const TAB_KEYS: LandingTab[] = ['slides', 'metrics', 'timeline', 'services', 'resources', 'manual', 'faqs', 'footer', 'sections'];

export default function LandingIndex(props: Props) {
    const [tab, setTab] = useState<LandingTab>(TAB_KEYS.includes(props.tab as LandingTab) ? (props.tab as LandingTab) : 'slides');

    return (
        <>
            <Head title={t('admin_landing.page_title')} />
            <PageHeader
                accent
                title={t('admin_landing.heading')}
                description={t('admin_landing.intro')}
                className="mb-6"
                actions={
                    <LinkButton href={props.previewUrl} external target="_blank" variant="secondary">
                        {t('admin_landing.preview')}
                        <ArrowTopRightOnSquareIcon className="h-4 w-4" />
                    </LinkButton>
                }
            >
                <p className="mt-2 text-xs font-semibold tracking-wide text-(--text-subtle) uppercase">{t('admin_landing.eyebrow')}</p>
            </PageHeader>

            <Tabs
                label={t('admin_landing.page_title')}
                active={tab}
                onChange={setTab}
                tabs={TAB_KEYS.map((key) => ({ key, label: t(`admin_landing.tabs.${key}`) }))}
            >
                {tab === 'slides' && <SlidesTab slides={props.slides} />}
                {tab === 'metrics' && <MetricsTab metrics={props.metrics} />}
                {tab === 'timeline' && <StepsTab steps={props.steps} />}
                {tab === 'services' && <ServicesTab services={props.services} />}
                {tab === 'resources' && <ResourcesTab resources={props.resources} />}
                {tab === 'manual' && <ManualTab manual={props.manual} />}
                {tab === 'faqs' && <FaqsTab faqs={props.faqs} />}
                {tab === 'footer' && <FooterTab footer={props.footer} />}
                {tab === 'sections' && <SectionsTab sections={props.sections} />}
            </Tabs>
        </>
    );
}
