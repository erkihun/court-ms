<?php

namespace App\Services;

use App\Models\Applicant;
use App\Models\ApplicantResponseReply;
use App\Models\Respondent;
use App\Models\RespondentResponse;
use App\Models\SystemSetting;
use App\Models\User;
use App\Notifications\ApplicantResponseReplyReviewed;
use App\Notifications\ApplicantResponseReplySubmitted;
use App\Notifications\RespondentResponseReviewed;
use App\Notifications\RespondentResponseSubmitted;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class ResponseNotificationService
{
    public static function notifyApplicantCaseCreated(int $caseId): void
    {
        $case = self::caseSummary($caseId);

        if (! $case) {
            return;
        }

        app(TelegramGroupNotificationService::class)->send(implode("\n", array_filter([
            '<b>'.self::html(self::t('titles.new_case_submitted')).'</b>',
            self::line('case', '<b>'.self::html($case->case_number ?? $case->id).'</b>', true),
            filled($case->title) ? self::line('title', $case->title) : null,
            filled($case->case_type) ? self::line('type', $case->case_type) : null,
            filled($case->applicant_name) ? self::line('applicant', $case->applicant_name) : null,
            filled($case->respondent_name) ? self::line('respondent', $case->respondent_name) : null,
            filled($case->review_status) ? self::line('review_status', self::reviewStatusLabel($case->review_status)) : null,
            self::line('action', self::t('actions.review_new_case')),
            self::caseUrl((int) $case->id),
        ])));
    }

    public static function notifyApplicantCaseUpdated(int $caseId): void
    {
        self::sendCaseTelegram($caseId, 'applicant_case_updated', [
            self::line('action', self::t('actions.review_updated_case')),
        ]);
    }

    public static function notifyCaseReviewDecision(int $caseId, string $decision, ?string $note = null): void
    {
        self::sendCaseTelegram($caseId, 'case_review_decision', [
            self::line('decision', self::responseStatusLabel($decision)),
            filled($note) ? self::line('note', self::preview($note)) : null,
            self::line('action', self::t('actions.check_review_status')),
        ]);
    }

    public static function notifyCaseStatusChanged(int $caseId, ?string $oldStatus, string $newStatus, ?string $note = null): void
    {
        self::sendCaseTelegram($caseId, 'case_status_changed', [
            filled($oldStatus) ? self::line('from', self::caseStatusLabel($oldStatus)) : null,
            self::line('to', self::caseStatusLabel($newStatus)),
            filled($note) ? self::line('note', self::preview($note)) : null,
            self::line('action', self::t('actions.review_case_timeline')),
        ]);
    }

    public static function notifyCaseMessagePosted(int $caseId, string $senderLabel, string $body): void
    {
        self::sendCaseTelegram($caseId, 'case_message_posted', [
            self::line('sender', $senderLabel),
            self::line('message', self::preview($body)),
            self::line('action', self::t('actions.open_message_thread')),
        ]);
    }

    public static function notifyCaseHearingCreated(int $hearingId): void
    {
        $hearing = DB::table('case_hearings')->where('id', $hearingId)->first();
        if (! $hearing) {
            return;
        }

        self::sendHearingTelegram($hearing, 'case_hearing_scheduled');
    }

    public static function notifyCaseHearingUpdated(int $hearingId): void
    {
        $hearing = DB::table('case_hearings')->where('id', $hearingId)->first();
        if (! $hearing) {
            return;
        }

        self::sendHearingTelegram($hearing, 'case_hearing_updated');
    }

    public static function notifyCaseHearingDeleted(object $hearing): void
    {
        self::sendHearingTelegram($hearing, 'case_hearing_deleted');
    }

    public static function notifyRespondentCaseViewed(int $caseId, string $respondentName): void
    {
        self::sendCaseTelegram($caseId, 'respondent_viewed_case', [
            self::line('respondent', $respondentName),
            self::line('action', self::t('actions.check_respondent_activity')),
        ]);
    }

    public static function notifyRespondentResponseCreated(RespondentResponse $response): void
    {
        if (empty($response->case_number)) {
            return;
        }

        $case = DB::table('court_cases')
            ->select('id', 'applicant_id', 'assigned_user_id')
            ->where('case_number', $response->case_number)
            ->first();

        if (!$case) {
            return;
        }

        $admins = self::adminRecipients((int) $case->id, ['cases.review']);
        if ($admins->isNotEmpty()) {
            Notification::send($admins, new RespondentResponseSubmitted($response));
        }

        self::sendTelegramRespondentResponseSubmitted($response, $case);

        if (!empty($case->applicant_id)) {
            $applicant = Applicant::find($case->applicant_id);
            if ($applicant) {
                $applicant->notify(new RespondentResponseSubmitted($response));
            }
        }
    }

    public static function notifyApplicantResponseReplyCreated(ApplicantResponseReply $reply, RespondentResponse $respondentResponse): void
    {
        $case = DB::table('court_cases')
            ->select('id', 'assigned_user_id')
            ->where('id', $reply->case_id)
            ->first();

        if ($case) {
            $admins = self::adminRecipients((int) $case->id, ['cases.response-replies.manage', 'cases.review']);
            if ($admins->isNotEmpty()) {
                Notification::send($admins, new ApplicantResponseReplySubmitted($reply));
            }

            self::sendTelegramApplicantResponseReplySubmitted($reply, $respondentResponse);
        }

        $respondent = Respondent::find($respondentResponse->respondent_id);
        if ($respondent) {
            $respondent->notify(new ApplicantResponseReplySubmitted($reply));
        }
    }

    public static function notifyRespondentResponseReviewed(RespondentResponse $response, string $decision, ?string $note = null): void
    {
        $case = DB::table('court_cases')
            ->select('id')
            ->where('case_number', $response->case_number)
            ->first();

        if ($case) {
            self::sendCaseTelegram((int) $case->id, 'respondent_response_reviewed', [
                filled($response->response_number) ? self::line('response', $response->response_number) : null,
                self::line('decision', self::responseStatusLabel($decision)),
                filled($note) ? self::line('note', self::preview($note)) : null,
                self::line('action', self::t('actions.check_reviewed_respondent_response')),
            ]);
        }

        $respondent = Respondent::find($response->respondent_id);
        if (!$respondent) {
            return;
        }

        $respondent->notify(new RespondentResponseReviewed($response, $decision, $note));
    }

    public static function notifyResponseReplyReviewed(ApplicantResponseReply $reply, string $decision, ?string $note = null): void
    {
        self::sendCaseTelegram((int) $reply->case_id, 'applicant_reply_reviewed', [
            self::line('decision', self::responseStatusLabel($decision)),
            filled($note) ? self::line('note', self::preview($note)) : null,
            self::line('action', self::t('actions.check_reviewed_applicant_reply')),
        ]);

        $applicant = Applicant::find($reply->applicant_id);
        if (!$applicant) {
            return;
        }

        $applicant->notify(new ApplicantResponseReplyReviewed($reply, $decision, $note));
    }

    private static function adminRecipients(int $caseId, array $permissions): Collection
    {
        $case = DB::table('court_cases')
            ->select('assigned_user_id')
            ->where('id', $caseId)
            ->first();

        $ids = collect();
        if ($case && $case->assigned_user_id) {
            $ids->push((int) $case->assigned_user_id);
        }

        $permissionIds = DB::table('users as u')
            ->leftJoin('role_user as ru', 'ru.user_id', '=', 'u.id')
            ->leftJoin('roles as r', 'r.id', '=', 'ru.role_id')
            ->leftJoin('permission_role as pr', 'pr.role_id', '=', 'r.id')
            ->leftJoin('permissions as p', 'p.id', '=', 'pr.permission_id')
            ->where(function ($q) use ($permissions) {
                $q->whereRaw('LOWER(r.name) = ?', ['admin'])
                    ->orWhereIn('p.name', $permissions);
            })
            ->pluck('u.id');

        $ids = $ids->merge($permissionIds)->unique()->filter();

        return $ids->isEmpty()
            ? collect()
            : User::whereIn('id', $ids)->get();
    }

    private static function sendTelegramRespondentResponseSubmitted(RespondentResponse $response, object $case): void
    {
        app(TelegramGroupNotificationService::class)->send(implode("\n", array_filter([
            '<b>'.self::html(self::t('titles.respondent_response_submitted')).'</b>',
            self::line('case', '<b>'.self::html($response->case_number ?? $case->id).'</b>', true),
            filled($response->response_number) ? self::line('response', $response->response_number) : null,
            filled($response->title) ? self::line('title', $response->title) : null,
            self::line('action', self::t('actions.review_response')),
            self::caseUrl((int) $case->id),
        ])));
    }

    private static function sendTelegramApplicantResponseReplySubmitted(ApplicantResponseReply $reply, RespondentResponse $respondentResponse): void
    {
        $case = DB::table('court_cases')
            ->select('id', 'case_number', 'title')
            ->where('id', $reply->case_id)
            ->first();

        app(TelegramGroupNotificationService::class)->send(implode("\n", array_filter([
            '<b>'.self::html(self::t('titles.applicant_reply_submitted')).'</b>',
            self::line('case', '<b>'.self::html($case?->case_number ?? $reply->case_id).'</b>', true),
            filled($respondentResponse->response_number) ? self::line('response', $respondentResponse->response_number) : null,
            filled($case?->title) ? self::line('case_title', $case->title) : null,
            self::line('action', self::t('actions.review_reply')),
            $case ? self::caseUrl((int) $case->id) : null,
        ])));
    }

    private static function sendCaseTelegram(int $caseId, string $titleKey, array $lines = []): void
    {
        $case = self::caseSummary($caseId);
        if (! $case) {
            return;
        }

        app(TelegramGroupNotificationService::class)->send(implode("\n", array_filter([
            '<b>'.self::html(self::t("titles.{$titleKey}")).'</b>',
            self::line('case', '<b>'.self::html($case->case_number ?? $case->id).'</b>', true),
            filled($case->title) ? self::line('title', $case->title) : null,
            filled($case->case_type) ? self::line('type', $case->case_type) : null,
            filled($case->applicant_name) ? self::line('applicant', $case->applicant_name) : null,
            filled($case->respondent_name) ? self::line('respondent', $case->respondent_name) : null,
            ...$lines,
            self::caseUrl((int) $case->id),
        ])));
    }

    private static function sendHearingTelegram(object $hearing, string $titleKey): void
    {
        if (empty($hearing->case_id)) {
            return;
        }

        self::sendCaseTelegram((int) $hearing->case_id, $titleKey, [
            filled($hearing->hearing_at ?? null) ? self::line('hearing_at', $hearing->hearing_at) : null,
            filled($hearing->type ?? null) ? self::line('type', $hearing->type) : null,
            filled($hearing->location ?? null) ? self::line('location', $hearing->location) : null,
            filled($hearing->notes ?? null) ? self::line('notes', self::preview($hearing->notes)) : null,
            self::line('action', self::t('actions.review_hearing_schedule')),
        ]);
    }

    private static function caseSummary(int $caseId): ?object
    {
        return DB::table('court_cases as c')
            ->leftJoin('applicants as a', 'a.id', '=', 'c.applicant_id')
            ->leftJoin('case_types as ct', 'ct.id', '=', 'c.case_type_id')
            ->select(
                'c.id',
                'c.case_number',
                'c.title',
                'c.respondent_name',
                'c.review_status',
                'ct.name as case_type',
                DB::raw("TRIM(CONCAT_WS(' ', a.first_name, a.middle_name, a.last_name)) as applicant_name")
            )
            ->where('c.id', $caseId)
            ->first();
    }

    private static function caseUrl(int $caseId): string
    {
        return route('cases.show', $caseId);
    }

    private static function html(mixed $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    private static function line(string $labelKey, mixed $value, bool $trustedHtml = false): string
    {
        return self::html(self::t("labels.{$labelKey}")).': '.($trustedHtml ? (string) $value : self::html($value));
    }

    private static function t(string $key, array $replace = []): string
    {
        return (string) trans("notifications.telegram.{$key}", $replace, self::telegramLocale());
    }

    private static function caseStatusLabel(?string $status): string
    {
        if (! filled($status)) {
            return '';
        }

        $translated = trans("cases.status.{$status}", [], self::telegramLocale());

        return $translated === "cases.status.{$status}" ? (string) $status : (string) $translated;
    }

    private static function reviewStatusLabel(?string $status): string
    {
        if (! filled($status)) {
            return '';
        }

        $translated = trans("cases.review_status.{$status}", [], self::telegramLocale());

        return $translated === "cases.review_status.{$status}" ? (string) $status : (string) $translated;
    }

    private static function responseStatusLabel(string $status): string
    {
        $translated = trans("notifications.status.{$status}", [], self::telegramLocale());

        return $translated === "notifications.status.{$status}" ? $status : (string) $translated;
    }

    private static function telegramLocale(): string
    {
        static $locale = null;

        if ($locale !== null) {
            return $locale;
        }

        $configuredLocale = (string) (SystemSetting::current()->default_locale ?: config('app.locale', 'en'));
        $locale = in_array($configuredLocale, ['en', 'am'], true)
            ? $configuredLocale
            : (string) config('app.locale', 'en');

        return $locale;
    }

    private static function preview(mixed $value, int $width = 240): string
    {
        $plain = trim(preg_replace('/\s+/', ' ', strip_tags(html_entity_decode((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'))) ?? '');

        return mb_strimwidth($plain, 0, $width, '...');
    }
}
