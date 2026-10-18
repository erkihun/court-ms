import { Head, Link } from '@inertiajs/react';
import { CaseStatusPill } from '@/components/party/StatusPill';
import { t } from '@/lib/i18n';
import { route } from '@/lib/route';

interface Props {
    cases: Array<{ caseNumber: string; status: string; caseType: string | null; href: string }>;
}

/** Cases the respondent has opened with a valid access code. */
export default function MyCases({ cases }: Props) {
    return (
        <div className="mx-auto w-full max-w-[1800px] space-y-6">
            <Head title={t('respondent.my_cases')} />
            <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p className="text-sm tracking-wide text-(--text-subtle) uppercase">{t('cases.navigation.title')}</p>
                    <h1 className="text-2xl font-semibold text-(--text)">{t('respondent.my_cases')}</h1>
                </div>
                <Link href={route('respondent.case.search')} className="inline-flex items-center rounded-full bg-blue-700 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-800">
                    {t('respondent.find_case')}
                </Link>
            </div>

            {cases.length === 0 ? (
                <div className="rounded-xl border border-dashed border-(--border) bg-(--surface-strong) p-6 text-center text-(--text-muted)">{t('cases.table.no_cases_viewed_yet')}</div>
            ) : (
                <div className="overflow-hidden rounded-2xl border border-(--border) bg-(--surface-strong) shadow-xl">
                    <div className="overflow-x-auto">
                        <table className="min-w-full divide-y divide-(--border) text-sm">
                            <thead className="bg-(--surface-soft) text-xs tracking-wider text-(--text-muted) uppercase">
                                <tr>
                                    <th scope="col" className="w-1/4 px-6 py-3 text-left font-medium">{t('cases.case_number')}</th>
                                    <th scope="col" className="w-1/3 px-6 py-3 text-left font-medium">{t('cases.case_type')}</th>
                                    <th scope="col" className="px-6 py-3 text-right font-medium">{t('app.Actions')}</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-(--border) text-(--text)">
                                {cases.map((item) => (
                                    <tr key={item.caseNumber} className="hover:bg-blue-50/50">
                                        <td className="px-6 py-4">
                                            <div className="flex items-center gap-2">
                                                <span className="text-sm font-semibold">{item.caseNumber}</span>
                                                <CaseStatusPill status={item.status} withIcon={false} />
                                            </div>
                                        </td>
                                        <td className="px-6 py-4 text-(--text-muted)">{item.caseType ?? '—'}</td>
                                        <td className="px-6 py-4">
                                            <div className="flex justify-end">
                                                <Link href={item.href} className="inline-flex items-center rounded-full border border-(--border) px-3 py-1.5 text-xs font-semibold text-blue-700 hover:bg-blue-50">
                                                    {t('respondent.view_case_details')}
                                                </Link>
                                            </div>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                </div>
            )}
        </div>
    );
}
