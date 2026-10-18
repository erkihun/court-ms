<?php

namespace App\Http\Controllers\Respondent;

use App\Http\Controllers\Controller;
use App\Services\Party\ActingRespondent;
use App\Support\EthiopianDate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Inertia\Inertia;

class ResponseReplyController extends Controller
{
    public function index()
    {
        Session::put('acting_as_respondent', true);
        $respondentId = $this->currentRespondentId();

        $replies = DB::table('applicant_response_replies as arr')
            ->join('respondent_responses as rr', 'rr.id', '=', 'arr.respondent_response_id')
            ->join('court_cases as cc', 'cc.id', '=', 'arr.case_id')
            ->join('respondent_case_views as rcv', function ($join) use ($respondentId) {
                $join->on('rcv.case_id', '=', 'arr.case_id')
                    ->where('rcv.respondent_id', '=', $respondentId);
            })
            ->leftJoin('applicants as a', 'a.id', '=', 'arr.applicant_id')
            ->select(
                'arr.id',
                'arr.case_id',
                'arr.respondent_response_id',
                'arr.description',
                'arr.pdf_path',
                'arr.review_status',
                'arr.review_note',
                'arr.created_at',
                'cc.case_number',
                'rr.response_number',
                'rr.title as respondent_response_title',
                'a.first_name as applicant_first_name',
                'a.middle_name as applicant_middle_name',
                'a.last_name as applicant_last_name'
            )
            ->where('rr.respondent_id', $respondentId)
            ->where('arr.review_status', 'accepted')
            ->orderByDesc('arr.created_at')
            ->paginate(15);

        return Inertia::render('Respondent/ResponseReplies/Index', [
            'replies' => $replies->through(fn (object $reply): array => [
                'id' => (int) $reply->id,
                'caseNumber' => (string) $reply->case_number,
                'responseNumber' => $reply->response_number ?: null,
                'applicantName' => $this->applicantName($reply),
                'uploaded' => EthiopianDate::format($reply->created_at, withTime: true),
            ]),
        ]);
    }

    public function show(int $replyId)
    {
        Session::put('acting_as_respondent', true);
        $respondentId = $this->currentRespondentId();

        $reply = DB::table('applicant_response_replies as arr')
            ->join('respondent_responses as rr', 'rr.id', '=', 'arr.respondent_response_id')
            ->join('court_cases as cc', 'cc.id', '=', 'arr.case_id')
            ->join('respondent_case_views as rcv', function ($join) use ($respondentId) {
                $join->on('rcv.case_id', '=', 'arr.case_id')
                    ->where('rcv.respondent_id', '=', $respondentId);
            })
            ->leftJoin('applicants as a', 'a.id', '=', 'arr.applicant_id')
            ->select(
                'arr.*',
                'cc.case_number',
                'rr.response_number',
                'rr.title as respondent_response_title',
                'a.first_name as applicant_first_name',
                'a.middle_name as applicant_middle_name',
                'a.last_name as applicant_last_name'
            )
            ->where('rr.respondent_id', $respondentId)
            ->where('arr.id', $replyId)
            ->where('arr.review_status', 'accepted')
            ->first();

        abort_if(! $reply, 404);

        return Inertia::render('Respondent/ResponseReplies/Show', [
            'reply' => [
                'id' => (int) $reply->id,
                'caseNumber' => (string) $reply->case_number,
                'responseNumber' => $reply->response_number ?: null,
                'applicantName' => $this->applicantName($reply),
                'description' => (string) $reply->description,
                'downloadUrl' => route('respondent.response-replies.download', $reply->id),
                'inlineUrl' => route('respondent.response-replies.download', [$reply->id, 'inline' => 1]),
            ],
        ]);
    }

    private function applicantName(object $reply): ?string
    {
        $name = trim(implode(' ', array_filter([$reply->applicant_first_name, $reply->applicant_middle_name, $reply->applicant_last_name])));

        return $name !== '' ? $name : null;
    }

    private function currentRespondentId(): int
    {
        $applicant = Auth::guard('applicant')->user();
        abort_unless($applicant, 403);

        return (int) app(ActingRespondent::class)->resolve($applicant)->id;
    }
}
