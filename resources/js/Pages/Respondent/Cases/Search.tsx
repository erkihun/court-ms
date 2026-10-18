import { Head, Link, router } from '@inertiajs/react';
import { type FormEvent, useState } from 'react';
import { inputClass } from '@/components/party/case/CaseFormParts';
import { cn } from '@/lib/cn';
import { t } from '@/lib/i18n';
import { route } from '@/lib/route';

interface Props {
    caseNumber: string;
    searched: boolean;
    codeError: boolean;
    case: { caseNumber: string; title: string; status: string; filed: string; href: string } | null;
}

/** Find a case by number and the access code given to the respondent by the court. */
export default function CaseSearch({ caseNumber, searched, codeError, case: found }: Props) {
    const [number, setNumber] = useState(caseNumber);
    const [code, setCode] = useState('');
    const [busy, setBusy] = useState(false);

    const submit = (event: FormEvent) => {
        event.preventDefault();
        // POST, so the access code stays out of URLs, history and logs.
        router.post(route('respondent.case.search.submit'), { case_number: number, case_code: code }, { onStart: () => setBusy(true), onFinish: () => setBusy(false) });
    };

    return (
        <div className="mx-auto w-full max-w-[1800px] rounded-2xl border border-(--border) bg-(--surface-strong) p-6 shadow-sm">
            <Head title={t('respondent.find_case_title')} />
            <h1 className="mb-2 text-xl font-semibold text-(--text)">{t('respondent.find_case_title')}</h1>
            <p className="mb-6 text-sm text-(--text-muted)">{t('respondent.find_case_description')}</p>

            <form onSubmit={submit} className="max-w-xl space-y-4">
                <div>
                    <label htmlFor="case_number" className="block text-sm font-medium text-(--text-muted)">
                        {t('respondent.case_number_label')}
                    </label>
                    <input id="case_number" className={cn(inputClass, 'mt-1')} value={number} onChange={(event) => setNumber(event.target.value)} placeholder={t('respondent.case_number_placeholder')} autoComplete="off" required />
                </div>
                <div>
                    <label htmlFor="case_code" className="block text-sm font-medium text-(--text-muted)">
                        {t('respondent.case_access_code_label')}
                    </label>
                    <input
                        id="case_code"
                        className={cn(inputClass, 'mt-1')}
                        value={code}
                        onChange={(event) => setCode(event.target.value)}
                        placeholder={t('respondent.case_access_code_placeholder')}
                        autoComplete="off"
                        inputMode="numeric"
                        required
                    />
                </div>
                <button type="submit" disabled={busy} className="inline-flex items-center rounded-lg bg-blue-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-800 disabled:opacity-60">
                    {t('respondent.search')}
                </button>
            </form>

            {searched && (
                <div className="mt-6" aria-live="polite">
                    {found ? (
                        <div className="rounded-xl border border-blue-100 bg-blue-50 p-4">
                            <div className="flex items-center justify-between">
                                <div>
                                    <div className="text-xs font-semibold tracking-wide text-blue-700 uppercase">{t('respondent.case_found')}</div>
                                    <div className="mt-1 text-lg font-semibold text-slate-900">{found.caseNumber}</div>
                                </div>
                                <Link href={found.href} className="text-sm text-blue-700 hover:underline">
                                    {t('respondent.view_case_details')}
                                </Link>
                            </div>
                            <p className="mt-2 text-sm text-slate-700">{found.title}</p>
                            <div className="mt-3 grid grid-cols-2 gap-3 text-sm text-slate-600">
                                <div>
                                    <span className="text-xs tracking-wide text-slate-500 uppercase">{t('respondent.status_label')}</span>
                                    <div className="text-sm font-medium text-slate-900">{t(`cases.status.${found.status}`)}</div>
                                </div>
                                <div>
                                    <span className="text-xs tracking-wide text-slate-500 uppercase">{t('respondent.filed')}</span>
                                    <div className="text-sm font-medium text-slate-900">{found.filed}</div>
                                </div>
                            </div>
                        </div>
                    ) : (
                        <div role="alert" className="rounded-xl border border-red-100 bg-red-50 p-4 text-sm font-semibold text-red-700">
                            {t(codeError ? 'respondent.case_invalid_access' : 'respondent.no_matching_case')}
                        </div>
                    )}
                </div>
            )}
        </div>
    );
}
