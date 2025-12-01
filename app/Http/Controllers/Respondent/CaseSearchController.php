<?php

namespace App\Http\Controllers\Respondent;

use App\Http\Controllers\Controller;
use App\Mail\RespondentViewedCaseMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class CaseSearchController extends Controller
{
    public function index(Request $request)
    {
        $caseNumber = trim((string) $request->get('case_number', ''));
        $case = null;

        if ($caseNumber !== '') {
            $case = DB::table('court_cases')->where('case_number', $caseNumber)->first();

            if ($case) {
                $history = collect(session('respondent_viewed_cases', []));
                $history = $history->prepend($caseNumber)->unique()->take(12);
                session(['respondent_viewed_cases' => $history->values()->all()]);
            }
        }

        return view('respondant.cases.search', [
            'caseNumber' => $caseNumber,
            'case' => $case,
        ]);
    }

    public function show(string $caseNumber)
    {
        $case = DB::table('court_cases as c')
            ->leftJoin('case_types as ct', 'ct.id', '=', 'c.case_type_id')
            ->leftJoin('applicants as a', 'a.id', '=', 'c.applicant_id')
            ->select(
                'c.*',
                'ct.name as case_type',
                'a.first_name as applicant_first_name',
                'a.middle_name as applicant_middle_name',
                'a.last_name as applicant_last_name',
                'a.email as applicant_record_email',
                'a.phone as applicant_record_phone'
            )
            ->where('c.case_number', trim($caseNumber))
            ->first();

        abort_if(!$case, 404);

        if (auth('respondent')->check()) {
            $this->handleRespondentCaseView($case);
        }

        $caseId = $case->id;

        $timeline = DB::table('case_status_logs')
            ->where('case_id', $caseId)
            ->orderBy('created_at')
            ->get();

        $files = DB::table('case_files')
            ->where('case_id', $caseId)
            ->orderByDesc('created_at')
            ->get();

        $msgs = DB::table('case_messages as m')
            ->leftJoin('users as u', 'u.id', '=', 'm.sender_user_id')
            ->select('m.*', 'u.name as admin_name')
            ->where('m.case_id', $caseId)
            ->orderBy('m.created_at')
            ->get();

        $hearings = DB::table('case_hearings')
            ->where('case_id', $caseId)
            ->orderBy('hearing_at')
            ->get();

        $docs = DB::table('case_evidences')
            ->where('case_id', $caseId)
            ->where('type', 'document')
            ->orderBy('id')
            ->get();

        $witnesses = DB::table('case_witnesses')
            ->where('case_id', $caseId)
            ->orderBy('id')
            ->get();

        $audits = DB::table('case_audits')
            ->where('case_id', $caseId)
            ->orderByDesc('id')
            ->limit(200)
            ->get();

        return view('respondant.cases.show', compact(
            'case',
            'timeline',
            'files',
            'msgs',
            'hearings',
            'docs',
            'witnesses',
            'audits'
        ));
    }

    private function handleRespondentCaseView(object $case): void
    {
        $respondent = auth('respondent')->user();
        if (!$respondent) {
            return;
        }

        $respondentName = $this->formatRespondentName($respondent);
        $caseUrl = route('respondent.cases.show', $case->case_number);

        $this->recordRespondentCaseView($case->id, $respondent->id, $case->case_number ?? null);
        $this->notifyRespondentViewParticipants($case, $respondentName, $caseUrl);
        $this->logRespondentCaseAudit($case->id, $respondent->id, $respondentName);
    }

    private function recordRespondentCaseView(int $caseId, ?int $respondentId, ?string $caseNumber): void
    {
        if (!$respondentId) {
            return;
        }

        try {
            $now = now();
            DB::table('respondent_case_views')->updateOrInsert(
                [
                    'respondent_id' => $respondentId,
                    'case_id' => $caseId,
                ],
                [
                    'case_number' => $caseNumber,
                    'viewed_at' => $now,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        } catch (\Throwable $e) {
            Log::warning('Failed to record respondent case view', [
                'case_id' => $caseId,
                'respondent_id' => $respondentId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function notifyRespondentViewParticipants(object $case, string $respondentName, string $caseUrl): void
    {
        $recipients = collect();

        $applicantEmail = data_get($case, 'applicant_record_email') ?? data_get($case, 'applicant_email');
        if ($applicantEmail) {
            $recipients->push($applicantEmail);
        }

        $adminEmail = null;
        if (!empty($case->assigned_user_id)) {
            $adminEmail = DB::table('users')->where('id', $case->assigned_user_id)->value('email');
        }
        $adminEmail = $adminEmail ?: config('mail.from.address');

        if ($adminEmail) {
            $recipients->push($adminEmail);
        }

        foreach ($recipients->filter()->unique() as $email) {
            try {
                Mail::to($email)->send(new RespondentViewedCaseMail($case, $respondentName, $caseUrl));
            } catch (\Throwable $e) {
                Log::error('Failed to send respondent viewed case notification', [
                    'case_id' => $case->id,
                    'case_number' => $case->case_number ?? null,
                    'email' => $email,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    private function logRespondentCaseAudit(int $caseId, ?int $respondentId, string $respondentName): void
    {
        try {
            DB::table('case_audits')->insert([
                'case_id' => $caseId,
                'action' => 'respondent_viewed',
                'actor_type' => 'respondent',
                'actor_id' => $respondentId,
                'meta' => json_encode([
                    'respondent' => $respondentName,
                ]),
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('Failed to log respondent view audit', [
                'case_id' => $caseId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function formatRespondentName(object $respondent): string
    {
        $name = trim($respondent->full_name ?? '');
        if ($name === '') {
            $name = trim(($respondent->first_name ?? '') . ' ' . ($respondent->last_name ?? ''));
        }
        return $name !== '' ? $name : ($respondent->email ?? 'Respondent');
    }

    public function myCases()
    {
        $respondentId = auth('respondent')->id();
        $cases = collect();

        if ($respondentId) {
            $cases = DB::table('respondent_case_views as r')
                ->join('court_cases as c', 'c.id', '=', 'r.case_id')
                ->leftJoin('case_types as ct', 'ct.id', '=', 'c.case_type_id')
                ->select('c.*', 'ct.name as case_type', 'r.viewed_at')
                ->where('r.respondent_id', $respondentId)
                ->orderByDesc('r.viewed_at')
                ->get();
        }

        return view('respondant.cases.my', [
            'cases' => $cases,
        ]);
    }
}
