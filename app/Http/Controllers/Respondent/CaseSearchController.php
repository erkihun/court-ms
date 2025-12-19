<?php

namespace App\Http\Controllers\Respondent;

use App\Http\Controllers\Controller;
use App\Mail\RespondentViewedCaseMail;
use App\Models\Respondent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;

class CaseSearchController extends Controller
{
    public function index(Request $request)
    {
        Session::put('acting_as_respondent', true);
        $caseNumber = trim((string) $request->get('case_number', ''));
        $case = null;

        if ($caseNumber !== '') {
            $case = DB::table('court_cases')
                ->select('id', 'case_number', 'title', 'status', 'created_at', 'applicant_id')
                ->where('case_number', $caseNumber)
                ->first();

            // Prevent applicants from viewing/searching their own cases while in respondent mode.
            $applicantId = Auth::guard('applicant')->id();
            if ($case && $applicantId && (int) $case->applicant_id === (int) $applicantId) {
                $case = null;
            }

            if ($case) {
                $history = collect(session('respondent_viewed_cases', []));
                $history = $history->prepend($caseNumber)->unique()->take(12);
                session(['respondent_viewed_cases' => $history->values()->all()]);

                // If the respondent is signed in, immediately record the view so it appears in "My Cases".
                if (auth('applicant')->check()) {
                    $respondent = $this->resolveActingRespondent();
                    if ($respondent) {
                        $this->recordRespondentCaseView(
                            $case->id,
                            $respondent->id,
                            $case->case_number ?? null
                        );
                    }
                }
            }
        }

        return view('applicant.respondent.cases.search', [
            'caseNumber' => $caseNumber,
            'case' => $case,
        ]);
    }

    public function show(string $caseNumber)
    {
        Session::put('acting_as_respondent', true);
        $case = DB::table('court_cases as c')
            ->leftJoin('case_types as ct', 'ct.id', '=', 'c.case_type_id')
            ->leftJoin('applicants as a', 'a.id', '=', 'c.applicant_id')
            ->select(
                'c.*',
                'ct.name as case_type',
                'c.applicant_id',
                'a.first_name as applicant_first_name',
                'a.middle_name as applicant_middle_name',
                'a.last_name as applicant_last_name',
                'a.email as applicant_record_email',
                'a.phone as applicant_record_phone'
            )
            ->where('c.case_number', trim($caseNumber))
            ->first();

        abort_if(!$case, 404);

        // Prevent applicants from viewing/searching their own cases while in respondent mode.
        $applicantId = Auth::guard('applicant')->id();
        if ($applicantId && (int) $case->applicant_id === (int) $applicantId) {
            abort(404);
        }

        if (auth('applicant')->check()) {
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

        $letters = DB::table('letters as l')
            ->leftJoin('letter_templates as lt', 'lt.id', '=', 'l.letter_template_id')
            ->leftJoin('users as u', 'u.id', '=', 'l.user_id')
            ->select(
                'l.id',
                'l.subject',
                'l.reference_number',
                'l.approval_status',
                'l.created_at',
                'lt.title as template_title',
                'u.name as author_name'
            )
            ->where('l.case_number', $case->case_number)
            ->where('l.approval_status', 'approved')
            ->orderByDesc('l.created_at')
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

        return view('applicant.respondent.cases.show', compact(
            'case',
            'timeline',
            'letters',
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
        $respondent = $this->resolveActingRespondent();
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
        Session::put('acting_as_respondent', true);
        $respondent = $this->resolveActingRespondent();
        $cases = collect();

        if ($respondent) {
            $cases = DB::table('respondent_case_views as r')
                ->join('court_cases as c', 'c.id', '=', 'r.case_id')
                ->leftJoin('case_types as ct', 'ct.id', '=', 'c.case_type_id')
                ->select('c.*', 'ct.name as case_type', 'r.viewed_at')
                ->where('r.respondent_id', $respondent->id)
                ->orderByDesc('r.viewed_at')
                ->get();
        }

        return view('applicant.respondent.cases.my', [
            'cases' => $cases,
        ]);
    }

    private function resolveActingRespondent(): ?Respondent
    {
        $applicant = Auth::guard('applicant')->user();
        if (!$applicant) {
            return null;
        }

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
        // Normalize to digits-only and trim to 16 characters to respect DB column length.
        $digits = preg_replace('/\D/', '', (string) ($applicant->getRawOriginal('national_id_number') ?? $applicant->national_id_number ?? ''));
        if ($digits === '') {
            return null;
        }
        return substr($digits, 0, 16);
    }
}
