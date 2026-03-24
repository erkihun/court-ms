<?php

namespace App\Http\Controllers\Respondent;

use App\Http\Controllers\Controller;
use App\Models\Respondent;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

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

        return view('applicant.respondent.response-replies.index', compact('replies'));
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

        abort_if(!$reply, 404);

        return view('applicant.respondent.response-replies.show', compact('reply'));
    }

    private function currentRespondentId(): int
    {
        $applicant = Auth::guard('applicant')->user();
        abort_unless($applicant, 403);

        $respondent = Respondent::where('email', $applicant->email)->first();
        if (!$respondent) {
            $phone = $applicant->phone ?? 'resp_' . substr(md5((string) microtime(true)), 0, 12);
            if (Respondent::where('phone', $phone)->where('email', '!=', $applicant->email)->exists()) {
                $phone = 'resp_' . substr(md5(uniqid('', true)), 0, 12);
            }

            $respondent = Respondent::create([
                'first_name'        => $applicant->first_name ?? '',
                'middle_name'       => $applicant->middle_name ?? '',
                'last_name'         => $applicant->last_name ?? '',
                'gender'            => $applicant->gender ?? null,
                'position'          => $applicant->position ?? '',
                'organization_name' => $applicant->organization_name ?? '',
                'address'           => $applicant->address ?? '',
                'national_id'       => $this->applicantNationalId($applicant),
                'phone'             => $phone,
                'email'             => $applicant->email,
                'password'          => $applicant->password,
            ]);
        }

        return (int) $respondent->id;
    }

    private function applicantNationalId($applicant): ?string
    {
        $digits = preg_replace('/\D/', '', (string) ($applicant->getRawOriginal('national_id_number') ?? $applicant->national_id_number ?? ''));
        if ($digits === '') {
            return null;
        }

        return substr($digits, 0, 16);
    }
}
