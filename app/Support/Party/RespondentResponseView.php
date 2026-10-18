<?php

declare(strict_types=1);

namespace App\Support\Party;

use App\Support\EthiopianDate;

/**
 * How an accepted respondent response (and the applicant's replies to it)
 * is shown to the applicant who owns the case.
 */
final class RespondentResponseView
{
    /**
     * @param  object  $case  court_cases row (id, case_number)
     * @param  object  $response  respondent_responses row joined with the respondent's name/contact
     * @return array<string, mixed>
     */
    public static function response(object $case, object $response): array
    {
        $respondentName = trim(implode(' ', array_filter([$response->first_name ?? null, $response->middle_name ?? null, $response->last_name ?? null])));

        return [
            'caseId' => (int) $case->id,
            'caseNumber' => (string) $case->case_number,
            'id' => (int) $response->id,
            'title' => $response->title ?? __('cases.respondent_response'),
            'responseNumber' => $response->response_number ?: null,
            'created' => EthiopianDate::format($response->created_at, withTime: true),
            'respondentName' => $respondentName !== '' ? $respondentName : null,
            'respondentEmail' => $response->respondent_email ?? null,
            'respondentPhone' => $response->respondent_phone ?? null,
            'respondentOrganization' => $response->respondent_org ?? null,
            'summary' => $response->summary ?? null,
            // Purified on save; purified again for rows stored before that.
            'descriptionHtml' => (string) clean($response->description ?? '', 'cases'),
            'pdfInlineUrl' => empty($response->pdf_path) ? null : route('applicant.respondent.responses.download', ['response' => $response->id, 'inline' => 1]),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function reply(object $reply, int $caseId, int $responseId): array
    {
        $status = in_array($reply->review_status, ['accepted', 'returned', 'rejected'], true) ? $reply->review_status : 'awaiting_review';
        $params = [$caseId, $responseId, $reply->id];

        return [
            'id' => (int) $reply->id,
            'description' => (string) $reply->description,
            'reviewStatus' => $status,
            'reviewNote' => $reply->review_note ?: null,
            'created' => EthiopianDate::format($reply->created_at, withTime: true),
            'locked' => $status === 'accepted',
            'showUrl' => route('applicant.cases.respondentResponses.replies.show', $params),
            'editUrl' => route('applicant.cases.respondentResponses.replies.edit', $params),
            'destroyUrl' => route('applicant.cases.respondentResponses.replies.destroy', $params),
            'downloadUrl' => route('applicant.cases.respondentResponses.replies.download', $params),
            'inlineUrl' => route('applicant.cases.respondentResponses.replies.download', [...$params, 'inline' => 1]),
        ];
    }
}
