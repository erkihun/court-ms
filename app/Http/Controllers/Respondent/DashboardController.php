<?php

namespace App\Http\Controllers\Respondent;

use App\Http\Controllers\Controller;
use App\Models\Respondent;
use App\Services\Party\RespondentNotificationFeed;
use App\Support\EthiopianDate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        Session::put('acting_as_respondent', true);

        $applicant = Auth::guard('applicant')->user();
        abort_unless($applicant, 403);

        $respondent = $this->resolveActingRespondent($applicant);

        $stats = [
            'cases'        => DB::table('respondent_case_views')->where('respondent_id', $respondent->id)->count(),
            'responses'    => DB::table('respondent_responses')->where('respondent_id', $respondent->id)->count(),
            'response_replies' => DB::table('applicant_response_replies as arr')
                ->join('respondent_responses as rr', 'rr.id', '=', 'arr.respondent_response_id')
                ->join('respondent_case_views as rcv', function ($join) use ($respondent) {
                    $join->on('rcv.case_id', '=', 'arr.case_id')
                        ->where('rcv.respondent_id', '=', $respondent->id);
                })
                ->where('rr.respondent_id', $respondent->id)
                ->where('arr.review_status', 'accepted')
                ->count(),
            'notifications' => app(RespondentNotificationFeed::class)->count($respondent->id),
        ];

        $recentCases = DB::table('respondent_case_views as v')
            ->join('court_cases as c', 'c.id', '=', 'v.case_id')
            ->select('c.case_number', 'c.title', 'c.status', 'v.viewed_at')
            ->where('v.respondent_id', $respondent->id)
            ->orderByDesc('v.viewed_at')
            ->limit(5)
            ->get();

        $letters = DB::table('respondent_case_views as v')
            ->join('court_cases as c', 'c.id', '=', 'v.case_id')
            ->join('letters as l', 'l.case_number', '=', 'c.case_number')
            ->leftJoin('letter_templates as lt', 'lt.id', '=', 'l.letter_template_id')
            ->leftJoin('users as u', 'u.id', '=', 'l.user_id')
            ->where('v.respondent_id', $respondent->id)
            ->where('l.approval_status', 'approved')
            ->orderByDesc('l.created_at')
            ->limit(5)
            ->select(
                'l.id',
                'l.subject',
                'l.reference_number',
                'l.created_at',
                'c.case_number',
                'c.title as case_title',
                'lt.title as template_title',
                'u.name as author_name'
            )
            ->get();

        $responseReplies = DB::table('applicant_response_replies as arr')
            ->join('respondent_responses as rr', 'rr.id', '=', 'arr.respondent_response_id')
            ->join('court_cases as c', 'c.id', '=', 'arr.case_id')
            ->join('respondent_case_views as rcv', function ($join) use ($respondent) {
                $join->on('rcv.case_id', '=', 'arr.case_id')
                    ->where('rcv.respondent_id', '=', $respondent->id);
            })
            ->leftJoin('applicants as a', 'a.id', '=', 'arr.applicant_id')
            ->where('rr.respondent_id', $respondent->id)
            ->where('arr.review_status', 'accepted')
            ->orderByDesc('arr.created_at')
            ->limit(5)
            ->select(
                'arr.id',
                'arr.case_id',
                'arr.respondent_response_id',
                'arr.created_at',
                'arr.description',
                'c.case_number',
                'rr.response_number',
                'a.first_name as applicant_first_name',
                'a.middle_name as applicant_middle_name',
                'a.last_name as applicant_last_name'
            )
            ->get();

        return Inertia::render('Respondent/Dashboard', [
            'stats' => [
                'cases' => (int) $stats['cases'],
                'responses' => (int) $stats['responses'],
                'responseReplies' => (int) $stats['response_replies'],
                'notifications' => (int) $stats['notifications'],
            ],
            'recentCases' => $recentCases->map(fn (object $case): array => [
                'caseNumber' => (string) $case->case_number,
                'title' => $case->title,
                'status' => $case->status,
                'href' => route('respondent.cases.show', $case->case_number),
            ])->values(),
            'letters' => $letters->map(fn (object $letter): array => [
                'id' => (int) $letter->id,
                'title' => $letter->subject ?? $letter->template_title ?? __('respondent.letter'),
                'caseNumber' => $letter->case_number,
                'referenceNumber' => $letter->reference_number,
                'templateTitle' => $letter->template_title,
                'authorName' => $letter->author_name,
                'date' => EthiopianDate::format($letter->created_at, withTime: true),
                'previewUrl' => URL::signedRoute('letters.case-preview', ['letter' => $letter->id]),
            ])->values(),
            'responseReplies' => $responseReplies->map(fn (object $reply): array => [
                'id' => (int) $reply->id,
                'caseNumber' => $reply->case_number,
                'responseNumber' => $reply->response_number ?: null,
                'applicantName' => trim(implode(' ', array_filter([$reply->applicant_first_name, $reply->applicant_middle_name, $reply->applicant_last_name]))) ?: null,
                'href' => route('respondent.response-replies.show', $reply->id),
            ])->values(),
        ]);
    }

    private function resolveActingRespondent($applicant): Respondent
    {
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
        } else {
            $dirty = false;
            $maybeNationalId = $this->applicantNationalId($applicant);
            if (!$respondent->national_id && $maybeNationalId) {
                $respondent->national_id = $maybeNationalId;
                $dirty = true;
            }
            if ($dirty) {
                $respondent->save();
            }
        }

        return $respondent;
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
