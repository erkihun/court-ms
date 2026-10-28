import { Head, Link } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import PageHeader from '@/Components/PageHeader';
import { buttonClassName } from '@/Components/ui';
import { useLocale } from '@/hooks/useLocale';
import type { UsersCreateProps } from '@/court/types/people';
import UserForm from './Partials/UserForm';

/** A new staff account: identity, contact details, roles and profile images; the person gets a reset link. */
export default function UsersCreate({ roles }: UsersCreateProps) {
    const { t } = useLocale();

    return (
        <AuthenticatedLayout header={(
            <PageHeader title={t('cm.users.create_account_title')} description={t('cm.users.create_account_subtitle')}
                actions={<Link href={route('users.index')} className={buttonClassName({ variant: 'outline' })}>{t('cm.users.back_to_users')}</Link>} />
        )}>
            <Head title={t('cm.users.new_user_title')} />
            <UserForm mode="create" roles={roles} />
        </AuthenticatedLayout>
    );
}
