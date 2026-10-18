import { t } from '@/lib/i18n';
import type { RespondentResponseData } from '@/types/party';

/** Case number / response number / applicant / respondent tiles at the top of reply pages. */
export function ResponseFacts({ response, applicantName }: { response: RespondentResponseData; applicantName: string | null }) {
    const notAvailable = t('cases.labels.not_available');
    const facts: Array<[string, string]> = [
        [t('cases.case_number'), response.caseNumber],
        [t('respondent.response_number_label'), response.responseNumber ?? notAvailable],
        [t('cases.applicant_full_name'), applicantName ?? notAvailable],
        [t('cases.respondent_defendant'), response.respondentName ?? notAvailable],
    ];

    return (
        <dl className="grid gap-4 sm:grid-cols-2">
            {facts.map(([label, value]) => (
                <div key={label} className="rounded-xl border border-(--border) bg-(--surface-soft) p-4">
                    <dt className="text-xs font-semibold tracking-wide text-(--text-subtle) uppercase">{label}</dt>
                    <dd className="mt-1 text-sm font-semibold text-(--text)">{value}</dd>
                </div>
            ))}
        </dl>
    );
}
