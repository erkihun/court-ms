<?php

namespace App\Http\Controllers\Applicant;

use App\Http\Controllers\Controller;
use App\Models\Decision;
use App\Support\DecisionPdf;
use App\Support\EthiopianDate;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class ApplicantDashboardController extends Controller
{
    /**
     * Download the sealed decision PDF for a case the applicant owns.
     * Only available once the decision is approved AND published.
     */
    public function downloadDecision(Decision $decision)
    {
        $applicantId = auth('applicant')->id();

        $ownsCase = DB::table('court_cases')
            ->where('applicant_id', $applicantId)
            ->where('case_number', $decision->case_number)
            ->exists();

        abort_unless($ownsCase, 404);
        abort_unless($decision->isDownloadableByParties(), 403);

        return DecisionPdf::render($decision)->download(DecisionPdf::filename($decision));
    }

    public function index(): Response
    {
        $applicantId = auth('applicant')->id();

        $counts = DB::table('court_cases')
            ->where('applicant_id', $applicantId)
            ->selectRaw("
                COUNT(*) as total,
                SUM(status = 'pending') as pending,
                SUM(status = 'active') as active,
                SUM(status IN ('closed', 'dismissed')) as closed
            ")
            ->first();

        $total   = (int) ($counts->total ?? 0);
        $pending = (int) ($counts->pending ?? 0);
        $active  = (int) ($counts->active ?? 0);
        $closed  = (int) ($counts->closed ?? 0);

        $caseNumbers = DB::table('court_cases')
            ->where('applicant_id', $applicantId)
            ->pluck('case_number')
            ->filter()
            ->values();

        $lettersCount = 0;
        $responsesCount = 0;
        $decisionsCount = 0;
        $responseRepliesCount = 0;

        $caseLetters = collect();
        $caseResponses = collect();
        $caseDecisions = collect();
        $responseReplies = collect();

        if ($caseNumbers->isNotEmpty()) {
            $lettersCount = DB::table('letters')
                ->whereIn('case_number', $caseNumbers)
                ->where('approval_status', 'approved')
                ->count();

            $responsesCount = DB::table('respondent_responses')
                ->whereIn('case_number', $caseNumbers)
                ->where('review_status', 'accepted')
                ->count();

            $decisionsCount = DB::table('decisions')
                ->whereIn('case_number', $caseNumbers)
                ->count();

            $caseLetters = DB::table('letters as l')
                ->leftJoin('letter_templates as lt', 'lt.id', '=', 'l.letter_template_id')
                ->select(
                    'l.id',
                    'l.subject',
                    'l.reference_number',
                    'l.case_number',
                    'l.created_at',
                    'lt.title as template_title'
                )
                ->whereIn('l.case_number', $caseNumbers)
                ->where('l.approval_status', 'approved')
                ->orderByDesc('l.created_at')
                ->limit(5)
                ->get();

            $caseResponses = DB::table('respondent_responses as rr')
                ->leftJoin('court_cases as cc', function ($join) {
                    $join->on('cc.case_number', '=', 'rr.case_number');
                })
                ->select('rr.id', 'rr.title', 'rr.case_number', 'rr.created_at', 'cc.id as case_id')
                ->whereIn('rr.case_number', $caseNumbers)
                ->where('rr.review_status', 'accepted')
                ->orderByDesc('rr.created_at')
                ->limit(5)
                ->get();

            $caseDecisions = DB::table('decisions')
                ->select('id', 'name', 'case_number', 'decision_date', 'status', 'approved_at', 'created_at')
                ->whereIn('case_number', $caseNumbers)
                ->orderByDesc('decision_date')
                ->orderByDesc('created_at')
                ->limit(5)
                ->get();
        }

        $responseRepliesCount = DB::table('applicant_response_replies')
            ->where('applicant_id', $applicantId)
            ->count();

        $responseReplies = DB::table('applicant_response_replies as arr')
            ->join('court_cases as cc', 'cc.id', '=', 'arr.case_id')
            ->join('respondent_responses as rr', 'rr.id', '=', 'arr.respondent_response_id')
            ->select(
                'arr.id',
                'arr.case_id',
                'arr.respondent_response_id',
                'arr.review_status',
                'arr.review_note',
                'arr.created_at',
                'cc.case_number',
                'rr.response_number'
            )
            ->where('arr.applicant_id', $applicantId)
            ->orderByDesc('arr.created_at')
            ->limit(5)
            ->get();

        $recent = DB::table('court_cases')
            ->leftJoin('case_types', 'case_types.id', '=', 'court_cases.case_type_id')

            ->where('court_cases.applicant_id', $applicantId)
            ->select(
                'court_cases.id',
                'court_cases.case_number',
                'court_cases.title',
                'court_cases.status',
                'case_types.name as case_type',

                'court_cases.created_at'
            )
            ->orderByDesc('court_cases.created_at')
            ->limit(6)
            ->get();

        $applicant = auth('applicant')->user();
        $name = $applicant?->full_name ?: $applicant?->email ?: __('applicant.user');
        $date = fn ($value): string => $value ? EthiopianDate::format($value) : '';

        return Inertia::render('Applicant/Dashboard', [
            'greetingName' => $applicant?->is_lawyer ? __('dashboard.lawyer_title').' '.$name : $name,
            'today' => EthiopianDate::smartFormat(now(), false, '', 'h:i A', 'F j, Y'),
            'stats' => compact('total', 'pending', 'active', 'closed'),
            'counts' => [
                'letters' => $lettersCount,
                'responses' => $responsesCount,
                'decisions' => $decisionsCount,
                'responseReplies' => $responseRepliesCount,
            ],
            'recent' => $recent->map(fn (object $case): array => [
                'id' => (int) $case->id,
                'caseNumber' => (string) $case->case_number,
                'title' => (string) $case->title,
                'status' => (string) $case->status,
                'caseType' => $case->case_type,
                'created' => $date($case->created_at),
                'createdRelative' => EthiopianDate::smartRelative($case->created_at),
            ])->values(),
            'letters' => $caseLetters->map(fn (object $letter): array => [
                'id' => (int) $letter->id,
                'title' => $letter->subject ?? $letter->template_title ?? __('dashboard.letters'),
                'caseNumber' => $letter->case_number,
                'referenceNumber' => $letter->reference_number ?: null,
                'date' => $date($letter->created_at),
                'previewUrl' => route('letters.case-preview', $letter->id),
            ])->values(),
            'responses' => $caseResponses->map(fn (object $response): array => [
                'id' => (int) $response->id,
                'title' => (string) $response->title,
                'caseNumber' => $response->case_number,
                'when' => EthiopianDate::smartRelative($response->created_at, ''),
                'href' => $response->case_id ? route('applicant.cases.respondentResponses.show', [$response->case_id, $response->id]) : null,
            ])->values(),
            'responseReplies' => $responseReplies->map(fn (object $reply): array => [
                'id' => (int) $reply->id,
                'caseNumber' => (string) $reply->case_number,
                'responseNumber' => $reply->response_number ?: null,
                'status' => in_array($reply->review_status, ['accepted', 'returned', 'rejected'], true) ? $reply->review_status : 'awaiting_review',
                'reviewNote' => $reply->review_note ?: null,
                'date' => $reply->created_at ? EthiopianDate::format($reply->created_at, withTime: true) : '',
                'href' => route('applicant.cases.respondentResponses.replies.show', [$reply->case_id, $reply->respondent_response_id, $reply->id]),
            ])->values(),
            'decisions' => $caseDecisions->map(fn (object $decision): array => [
                'id' => (int) $decision->id,
                'name' => $decision->name ?? __('dashboard.decisions'),
                'caseNumber' => $decision->case_number,
                'date' => $date($decision->decision_date ?: $decision->created_at),
                'status' => $decision->status ?: null,
                // Same rule as Decision::isDownloadableByParties(), which downloadDecision() enforces.
                'downloadUrl' => $decision->status === 'published' && $decision->approved_at
                    ? route('applicant.decisions.download', $decision->id)
                    : null,
            ])->values(),
        ]);
    }
}
