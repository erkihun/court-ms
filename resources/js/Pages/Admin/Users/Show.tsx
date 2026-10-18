import { Head } from '@inertiajs/react';
import { Badge } from '@/components/ui/Badge';
import { LinkButton } from '@/components/ui/Button';
import { Card, CardTitle } from '@/components/ui/Card';
import { DescriptionList } from '@/components/ui/DescriptionList';
import { t } from '@/lib/i18n';
import { route } from '@/lib/route';
import { initials } from '@/lib/str';

interface Props {
    user: {
        id: number;
        name: string;
        firstName: string;
        middleName: string;
        lastName: string;
        email: string;
        active: boolean;
        avatarUrl: string | null;
        signatureUrl: string | null;
        stampUrl: string | null;
        roles: string[];
        created: string;
        updated: string;
        canEdit: boolean;
    };
}

export default function UserShow({ user }: Props) {
    return (
        <>
            <Head title={t('users.profile_title')} />

            <div className="grid gap-6 lg:grid-cols-3">
                <Card className="text-center">
                    <div className="mx-auto h-28 w-28 overflow-hidden rounded-full border-4 border-(--surface-strong) bg-(--surface-soft) shadow-sm">
                        {user.avatarUrl ? (
                            <img src={user.avatarUrl} alt={t('users.avatar')} className="h-full w-full object-cover" />
                        ) : (
                            <div className="flex h-full w-full items-center justify-center bg-blue-50 text-4xl font-bold text-blue-600" aria-hidden="true">
                                {initials(user.name)}
                            </div>
                        )}
                    </div>
                    <h1 className="mt-5 text-xl font-semibold text-(--text)">{user.name}</h1>
                    <p className="mt-1 text-sm text-(--text-muted)">{user.email}</p>
                    <Badge tone={user.active ? 'success' : 'neutral'} className="mt-4">
                        {user.active ? t('users.active') : t('users.inactive')}
                    </Badge>
                    <div className="mt-6 flex justify-center gap-2">
                        {user.canEdit && <LinkButton href={route('users.edit', user.id)}>{t('users.edit_profile')}</LinkButton>}
                        <LinkButton href={route('users.index')} variant="secondary">
                            {t('users.back')}
                        </LinkButton>
                    </div>
                </Card>

                <Card className="lg:col-span-2" header={<CardTitle title={t('users.details')} />}>
                    <DescriptionList
                        items={[
                            { label: t('users.first_name'), value: user.firstName },
                            { label: t('users.created_at'), value: user.created },
                            { label: t('users.middle_name'), value: user.middleName },
                            { label: t('users.updated_at'), value: user.updated },
                            { label: t('users.last_name'), value: user.lastName },
                            { label: t('users.email_address'), value: user.email },
                            { label: t('users.full_name'), value: user.name, wide: true },
                        ]}
                    />

                    <div className="mt-6 grid gap-4 md:grid-cols-2">
                        {[
                            { label: t('users.signature'), url: user.signatureUrl, empty: t('users.no_signature') },
                            { label: t('users.stamp'), url: user.stampUrl, empty: t('users.no_stamp') },
                        ].map((asset) => (
                            <div key={asset.label} className="rounded-xl border border-(--border) bg-(--surface-soft) p-4">
                                <p className="mb-2 text-xs font-medium tracking-wide text-(--text-subtle) uppercase">{asset.label}</p>
                                {asset.url ? (
                                    <img src={asset.url} alt={asset.label} className="mx-auto max-h-20 object-contain" />
                                ) : (
                                    <p className="text-sm text-(--text-subtle) italic">{asset.empty}</p>
                                )}
                            </div>
                        ))}
                    </div>

                    <div className="mt-6 border-t border-(--border) pt-4">
                        <p className="mb-2 text-xs font-medium tracking-wide text-(--text-subtle) uppercase">{t('users.assigned_roles')}</p>
                        {user.roles.length > 0 ? (
                            <div className="flex flex-wrap gap-2">
                                {user.roles.map((role) => (
                                    <Badge key={role} tone="info">
                                        {role}
                                    </Badge>
                                ))}
                            </div>
                        ) : (
                            <p className="text-sm text-(--text-subtle)">{t('users.no_roles_assigned')}</p>
                        )}
                    </div>
                </Card>
            </div>
        </>
    );
}
