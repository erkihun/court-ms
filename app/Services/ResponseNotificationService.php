<?php

namespace App\Services;

use App\Models\Applicant;
use App\Models\ApplicantResponseReply;
use App\Models\Respondent;
use App\Models\RespondentResponse;
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
        }

        $respondent = Respondent::find($respondentResponse->respondent_id);
        if ($respondent) {
            $respondent->notify(new ApplicantResponseReplySubmitted($reply));
        }
    }

    public static function notifyRespondentResponseReviewed(RespondentResponse $response, string $decision, ?string $note = null): void
    {
        $respondent = Respondent::find($response->respondent_id);
        if (!$respondent) {
            return;
        }

        $respondent->notify(new RespondentResponseReviewed($response, $decision, $note));
    }

    public static function notifyResponseReplyReviewed(ApplicantResponseReply $reply, string $decision, ?string $note = null): void
    {
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
}
