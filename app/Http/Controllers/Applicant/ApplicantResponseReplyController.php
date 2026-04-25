<?php

namespace App\Http\Controllers\Applicant;

use App\Http\Controllers\Controller;
use App\Models\ApplicantResponseReply;
use App\Models\RespondentResponse;
use App\Services\ResponseNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ApplicantResponseReplyController extends Controller
{
    public function index()
    {
        $applicantId = auth('applicant')->id();
        abort_if(!$applicantId, 403);

        $replies = DB::table('applicant_response_replies as arr')
            ->join('court_cases as cc', 'cc.id', '=', 'arr.case_id')
            ->join('respondent_responses as rr', 'rr.id', '=', 'arr.respondent_response_id')
            ->leftJoin('respondents as r', 'r.id', '=', 'rr.respondent_id')
            ->select(
                'arr.id',
                'arr.case_id',
                'arr.respondent_response_id',
                'arr.description',
                'arr.review_status',
                'arr.review_note',
                'arr.created_at',
                'cc.case_number',
                'rr.response_number',
                'rr.title as respondent_response_title',
                'r.first_name',
                'r.middle_name',
                'r.last_name'
            )
            ->where('arr.applicant_id', $applicantId)
            ->orderByDesc('arr.created_at')
            ->paginate(15);

        return view('applicant.responses.index', compact('replies'));
    }

    public function create(int $caseId, int $responseId)
    {
        $applicantId = auth('applicant')->id();
        abort_if(!$applicantId, 403);

        [$case, $response] = $this->loadCaseAndResponse($caseId, $responseId, $applicantId);

        return view('applicant.responses.create', compact('case', 'response'));
    }

    public function store(Request $request, int $caseId, int $responseId)
    {
        $applicantId = auth('applicant')->id();
        abort_if(!$applicantId, 403);

        [$case, $response] = $this->loadCaseAndResponse($caseId, $responseId, $applicantId);

        $data = $request->validate([
            'description' => ['required', 'string'],
            'pdf' => ['required', 'file', 'mimes:pdf', 'max:2048'],
        ]);

        $path = $request->file('pdf')->store('applicant/response-replies', 'private');

        $reply = ApplicantResponseReply::create([
            'case_id' => $case->id,
            'applicant_id' => $applicantId,
            'respondent_response_id' => $response->id,
            'description' => $data['description'],
            'pdf_path' => $path,
            'review_status' => 'awaiting_review',
            'review_note' => null,
            'reviewed_by_user_id' => null,
            'reviewed_at' => null,
        ]);

        $respondentResponse = RespondentResponse::query()->findOrFail($response->id);
        ResponseNotificationService::notifyApplicantResponseReplyCreated($reply, $respondentResponse);

        return redirect()->route('applicant.cases.respondentResponses.replies.show', [$case->id, $response->id, $reply->id]);
    }

    public function show(int $caseId, int $responseId, int $replyId)
    {
        $applicantId = auth('applicant')->id();
        abort_if(!$applicantId, 403);

        [$case, $response] = $this->loadCaseAndResponse($caseId, $responseId, $applicantId);
        $reply = $this->loadReply($replyId, $case->id, $response->id, $applicantId);

        return view('applicant.responses.show', compact('case', 'response', 'reply'));
    }

    public function edit(int $caseId, int $responseId, int $replyId)
    {
        $applicantId = auth('applicant')->id();
        abort_if(!$applicantId, 403);

        [$case, $response] = $this->loadCaseAndResponse($caseId, $responseId, $applicantId);
        $reply = $this->loadReply($replyId, $case->id, $response->id, $applicantId);
        $this->ensureEditableReply($reply);

        return view('applicant.responses.edit', compact('case', 'response', 'reply'));
    }

    public function update(Request $request, int $caseId, int $responseId, int $replyId)
    {
        $applicantId = auth('applicant')->id();
        abort_if(!$applicantId, 403);

        [$case, $response] = $this->loadCaseAndResponse($caseId, $responseId, $applicantId);
        $reply = ApplicantResponseReply::query()
            ->whereKey($replyId)
            ->where('case_id', $case->id)
            ->where('respondent_response_id', $response->id)
            ->where('applicant_id', $applicantId)
            ->firstOrFail();
        $this->ensureEditableReply($reply);

        $data = $request->validate([
            'description' => ['required', 'string'],
            'pdf' => ['nullable', 'file', 'mimes:pdf', 'max:2048'],
        ]);

        if ($request->hasFile('pdf')) {
            Storage::disk('private')->delete($reply->pdf_path);
            Storage::disk('public')->delete($reply->pdf_path);
            $reply->pdf_path = $request->file('pdf')->store('applicant/response-replies', 'private');
        }

        $reply->description = $data['description'];
        $reply->review_status = 'awaiting_review';
        $reply->review_note = null;
        $reply->reviewed_by_user_id = null;
        $reply->reviewed_at = null;
        $reply->save();

        return redirect()->route('applicant.cases.respondentResponses.replies.show', [$case->id, $response->id, $reply->id]);
    }

    public function destroy(int $caseId, int $responseId, int $replyId)
    {
        $applicantId = auth('applicant')->id();
        abort_if(!$applicantId, 403);

        [$case, $response] = $this->loadCaseAndResponse($caseId, $responseId, $applicantId);

        $reply = ApplicantResponseReply::query()
            ->whereKey($replyId)
            ->where('case_id', $case->id)
            ->where('respondent_response_id', $response->id)
            ->where('applicant_id', $applicantId)
            ->firstOrFail();
        $this->ensureEditableReply($reply);

        Storage::disk('private')->delete($reply->pdf_path);
        Storage::disk('public')->delete($reply->pdf_path);
        $reply->delete();

        return redirect()->route('applicant.cases.respondentResponses.show', [$case->id, $response->id]);
    }

    private function loadCaseAndResponse(int $caseId, int $responseId, int $applicantId): array
    {
        $case = DB::table('court_cases as cc')
            ->leftJoin('case_types as ct', 'ct.id', '=', 'cc.case_type_id')
            ->select('cc.*', 'ct.name as case_type_name')
            ->where('cc.id', $caseId)
            ->where('cc.applicant_id', $applicantId)
            ->first();

        abort_if(!$case, 404);

        $response = DB::table('respondent_responses as rr')
            ->leftJoin('respondents as r', 'r.id', '=', 'rr.respondent_id')
            ->select(
                'rr.*',
                'r.first_name',
                'r.middle_name',
                'r.last_name',
                'r.email as respondent_email',
                'r.phone as respondent_phone',
                'r.organization_name as respondent_org'
            )
            ->where('rr.id', $responseId)
            ->where('rr.case_number', $case->case_number)
            ->where('rr.review_status', 'accepted')
            ->first();

        abort_if(!$response, 404);

        return [$case, $response];
    }

    private function loadReply(int $replyId, int $caseId, int $responseId, int $applicantId): object
    {
        $reply = DB::table('applicant_response_replies as arr')
            ->leftJoin('applicants as a', 'a.id', '=', 'arr.applicant_id')
            ->select(
                'arr.*',
                'a.first_name as applicant_first_name',
                'a.middle_name as applicant_middle_name',
                'a.last_name as applicant_last_name'
            )
            ->where('arr.id', $replyId)
            ->where('arr.case_id', $caseId)
            ->where('arr.respondent_response_id', $responseId)
            ->where('arr.applicant_id', $applicantId)
            ->first();

        abort_if(!$reply, 404);

        return $reply;
    }

    private function ensureEditableReply(object $reply): void
    {
        abort_if(($reply->review_status ?? null) === 'accepted', 403, __('respondent.response_reply_locked'));
    }
}
