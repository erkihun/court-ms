import { Head, Link, useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';
import { Button } from '@/components/ui/Button';
import { Card } from '@/components/ui/Card';
import { t } from '@/lib/i18n';
import { route } from '@/lib/route';

type Pref = 'email_status' | 'email_hearing' | 'email_message' | 'email_weekly_digest';

const prefs: Array<{ name: Pref; key: string }> = [
    { name: 'email_status', key: 'status' },
    { name: 'email_hearing', key: 'hearing' },
    { name: 'email_message', key: 'message' },
    { name: 'email_weekly_digest', key: 'weekly_digest' },
];

/** Which events the applicant is emailed about. */
export default function NotificationSettings({ prefs: initial }: { prefs: Record<Pref, boolean> }) {
    const form = useForm(initial);

    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.post(route('applicant.notifications.settings.update'), { preserveScroll: true });
    };

    return (
        <div className="max-w-xl space-y-4">
            <Head title={t('app.notification_preferences.title')} />
            <h1 className="text-lg font-semibold text-(--text)">{t('app.notification_preferences.email_title')}</h1>

            <Card>
                <form onSubmit={submit}>
                    <div className="space-y-4">
                        {prefs.map(({ name, key }) => (
                            <label key={name} className="flex items-start gap-3">
                                <input
                                    type="checkbox"
                                    className="mt-1 h-4 w-4 rounded-sm border-slate-300"
                                    checked={form.data[name]}
                                    onChange={(event) => form.setData(name, event.target.checked)}
                                />
                                <span>
                                    <span className="block font-medium text-(--text)">{t(`app.notification_preferences.${key}_label`)}</span>
                                    <span className="block text-sm text-(--text-muted)">{t(`app.notification_preferences.${key}_desc`)}</span>
                                </span>
                            </label>
                        ))}
                    </div>

                    <div className="mt-5 flex items-center gap-3">
                        <Button type="submit" loading={form.processing}>
                            {t('app.notification_preferences.save')}
                        </Button>
                        <Link href={route('applicant.notifications.index')} className="text-sm text-(--text-muted) hover:text-(--text)">
                            &larr; {t('app.notification_preferences.back')}
                        </Link>
                    </div>
                </form>
            </Card>
        </div>
    );
}
