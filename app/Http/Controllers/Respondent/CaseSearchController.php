<?php

namespace App\Http\Controllers\Respondent;

use App\Http\Controllers\Controller;
use App\Mail\RespondentViewedCaseMail;
use App\Models\Decision;
use App\Models\Respondent;
use App\Services\Party\ActingRespondent;
use App\Services\ResponseNotificationService;
use App\Support\DecisionPdf;
use App\Support\EthiopianDate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use Inertia\Inertia;

class CaseSearchController extends Controller
{
    /**
     * Search page. Results come from submit() via the session, so the access
     * code never appears in a URL. A legacy ?case_code= link still works.
     */
    public function index(Request $request)
    {
        Session::put('acting_as_respondent', true);
        $caseNumber = trim((string) $request->get('case_number', ''));
        $case = null;
        $codeError = (bool) session('respondent_search_failed', false);

        if ($caseNumber !== '' && $request->filled('case_code')) {
            [$case, $codeError] = $this->attempt($caseNumber, trim((string) $request->get('case_code')));
        } elseif ($caseNumber !== '') {
            $found = $this->findCaseForSearch($caseNumber);
            $case = $found && $this->hasCaseAccess((int) $found->id) ? $found : null;
        }

        return Inertia::render('Respondent/Cases/Search', [
            'caseNumber' => $caseNumber,
            'searched' => $caseNumber !== '' && ($case !== null || $codeError || $request->has('case_code')),
            'codeError' => $codeError,
            // The access granted above lets the details page open without the code in its URL.
            'case' => $case ? [
                'caseNumber' => (string) $case->case_number,
                'title' => (string) $case->title,
                'status' => (string) $case->status,
                'filed' => EthiopianDate::format($case->created_at),
                'href' => route('respondent.cases.show', $case->case_number),
            ] : null,
        ]);
    }

    public function submit(Request $request): RedirectResponse
    {
        Session::put('acting_as_respondent', true);
        $data = $request->validate([
            'case_number' => ['required', 'string', 'max:64'],
            'case_code' => ['required', 'string', 'max:32'],
        ]);
        $caseNumber = trim($data['case_number']);

        [$case, $codeError] = $this->attempt($caseNumber, trim($data['case_code']));

        return redirect()
            ->route('respondent.case.search', ['case_number' => $caseNumber])
            ->with('respondent_search_failed', $case === null && $codeError);
    }

    /**
     * Look a case up by number and access code; on success grant this session
     * access and record the view.
     *
     * @return array{0: object|null, 1: bool} [case, wrong code]
     */
    private function attempt(string $caseNumber, string $caseCode): array
    {
        $case = $this->findCaseForSearch($caseNumber);

        // Applicants cannot look up their own cases while in respondent mode.
        $applicantId = Auth::guard('applicant')->id();
        if ($case && $applicantId && (int) $case->applicant_id === (int) $applicantId) {
            $case = null;
        }

        if ($case && ! $this->caseCodeMatches($case, $caseCode)) {
            return [null, true];
        }

        if ($case) {
            $this->grantCaseAccess($case->id);
            $history = collect(session('respondent_viewed_cases', []))->prepend($caseNumber)->unique()->take(12);
            session(['respondent_viewed_cases' => $history->values()->all()]);

            $respondent = $this->resolveActingRespondent();
            if ($respondent) {
                $this->recordRespondentCaseView($case->id, $respondent->id, $case->case_number ?? null);
            }
        }

        return [$case, false];
    }

    public function show(string $caseNumber)
    {
        Session::put('acting_as_respondent', true);
        $caseCode = trim((string) request()->query('case_code', ''));
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

        abort_if(! $case, 404);

        if (! $this->hasCaseAccess($case->id)) {
            $hasDbAccess = false;
            if (auth('applicant')->check()) {
                $applicant = auth('applicant')->user();
                $respondent = Respondent::where('email', $applicant->email)->first();
                if ($respondent) {
                    $hasDbAccess = DB::table('respondent_case_views')
                        ->where('respondent_id', $respondent->id)
                        ->where('case_id', $case->id)
                        ->exists();
                }
            }
            if (! $hasDbAccess) {
                abort_if(! $this->caseCodeMatches($case, $caseCode), 404);
            }
            $this->grantCaseAccess($case->id);
        }

        // Prevent applicants from viewing/searching their own cases while in respondent mode.
        $applicantId = Auth::guard('applicant')->id();
        if ($applicantId && (int) $case->applicant_id === (int) $applicantId) {
            abort(404);
        }

        if (auth('applicant')->check()) {
            $this->handleRespondentCaseView($case);
        }

        $hearings = DB::table('case_hearings')->where('case_id', $case->id)->orderBy('hearing_at')->get();
        $docs = DB::table('case_evidences')->where('case_id', $case->id)->where('type', 'document')->orderBy('id')->get();

        $letters = DB::table('letters as l')
            ->leftJoin('letter_templates as lt', 'lt.id', '=', 'l.letter_template_id')
            ->leftJoin('users as u', 'u.id', '=', 'l.user_id')
            ->select('l.id', 'l.subject', 'l.reference_number', 'l.created_at', 'lt.title as template_title', 'u.name as author_name')
            ->where('l.case_number', $case->case_number)
            ->where('l.approval_status', 'approved')
            ->orderByDesc('l.created_at')
            ->get();

        // Only published + approved decisions are downloadable by the respondent.
        $decisions = DB::table('decisions')
            ->select('id', 'name', 'decision_date', 'created_at')
            ->where('case_number', $case->case_number)
            ->where('status', 'published')
            ->whereNotNull('approved_at')
            ->orderByDesc('decision_date')
            ->orderByDesc('created_at')
            ->get();

        $at = fn ($value): string => $value ? EthiopianDate::format($value, withTime: true) : '';
        $applicantName = trim(implode(' ', array_filter([$case->applicant_first_name, $case->applicant_middle_name, $case->applicant_last_name])));

        return Inertia::render('Respondent/Cases/Show', [
            'case' => [
                'id' => (int) $case->id,
                'caseNumber' => (string) $case->case_number,
                'status' => (string) $case->status,
                'reviewStatus' => $case->review_status ?: 'accepted',
                'reviewNote' => $case->review_note ?: null,
                'caseType' => $case->case_type,
                'filed' => EthiopianDate::format($case->filing_date ?? $case->created_at),
                'applicantName' => $applicantName !== '' ? $applicantName : ($case->title ?: null),
                'applicantEmail' => $case->applicant_record_email ?: null,
                'respondentName' => $case->respondent_name ?: null,
                'respondentAddress' => $case->respondent_address ?: null,
                'descriptionHtml' => (string) clean($case->description ?? '', 'cases'),
                'reliefHtml' => empty($case->relief_requested) ? null : (string) clean($case->relief_requested, 'cases'),
                'respondHref' => route('respondent.responses.create', ['case_number' => $case->case_number]),
            ],
            'documents' => $docs->map(fn (object $doc): array => [
                'id' => (int) $doc->id,
                'title' => $doc->title ?? $doc->label ?? (($doc->file_path ?? $doc->path ?? null) ? basename($doc->file_path ?? $doc->path) : __('cases.document')),
                'mime' => $doc->mime ?? null,
                'created' => $at($doc->created_at ?? null),
                'descriptionHtml' => empty($doc->description) ? null : (string) clean($doc->description, 'cases'),
                'downloadUrl' => ($doc->file_path ?? $doc->path ?? null)
                    ? route('applicant.cases.evidences.download', ['id' => $case->id, 'evidenceId' => $doc->id])
                    : null,
            ])->values(),
            'hearings' => $hearings->map(fn (object $hearing): array => [
                'id' => (int) $hearing->id,
                'at' => EthiopianDate::format($hearing->hearing_at, withTime: true, separator: ' · '),
                'type' => $hearing->type ?: null,
                'location' => $hearing->location ?: null,
                'notes' => $hearing->notes ?: null,
            ])->values(),
            'letters' => $letters->map(fn (object $letter): array => [
                'id' => (int) $letter->id,
                'title' => $letter->subject ?? $letter->template_title ?? __('cases.letters_section.letter'),
                'reference' => $letter->reference_number ?: null,
                'template' => $letter->template_title ?: null,
                'author' => $letter->author_name ?: null,
                'created' => $at($letter->created_at),
                'previewUrl' => URL::signedRoute('letters.case-preview', ['letter' => $letter->id]),
            ])->values(),
            'decisions' => $decisions->map(fn (object $decision): array => [
                'id' => (int) $decision->id,
                'name' => $decision->name ?? __('dashboard.decisions'),
                'date' => EthiopianDate::format($decision->decision_date ?: $decision->created_at),
                'downloadUrl' => route('respondent.decisions.download', $decision->id),
            ])->values(),
        ]);
    }

    /**
     * Download the sealed decision PDF for a case the respondent has access to.
     * Only available once the decision is approved AND published.
     */
    public function downloadDecision(Decision $decision)
    {
        $caseId = DB::table('court_cases')->where('case_number', $decision->case_number)->value('id');

        abort_if(! $caseId, 404);
        abort_unless($this->hasCaseAccess((int) $caseId), 403);
        abort_unless($decision->isDownloadableByParties(), 403);

        return DecisionPdf::render($decision)->download(DecisionPdf::filename($decision));
    }

    private function handleRespondentCaseView(object $case): void
    {
        $respondent = $this->resolveActingRespondent();
        if (! $respondent) {
            return;
        }

        $respondentName = $this->formatRespondentName($respondent);
        $caseUrl = route('respondent.cases.show', $case->case_number);

        $this->recordRespondentCaseView($case->id, $respondent->id, $case->case_number ?? null);

        // Every view is recorded and audited, but the applicant and the court are
        // told at most once a day per respondent and case, not on every page load.
        if (Cache::add("respondent-viewed-notice:{$respondent->id}:{$case->id}", true, now()->addDay())) {
            $this->notifyRespondentViewParticipants($case, $respondentName, $caseUrl);
            ResponseNotificationService::notifyRespondentCaseViewed((int) $case->id, $respondentName);
        }

        $this->logRespondentCaseAudit($case->id, $respondent->id, $respondentName);
    }

    private function recordRespondentCaseView(int $caseId, ?int $respondentId, ?string $caseNumber): void
    {
        if (! $respondentId) {
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
        if (! empty($case->assigned_user_id)) {
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
            $name = trim(($respondent->first_name ?? '').' '.($respondent->last_name ?? ''));
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

        return Inertia::render('Respondent/Cases/My', [
            // Access is recorded in respondent_case_views, so links need no access code.
            'cases' => $cases->map(fn (object $case): array => [
                'caseNumber' => (string) $case->case_number,
                'status' => (string) ($case->status ?? 'pending'),
                'caseType' => $case->case_type,
                'href' => route('respondent.cases.show', $case->case_number),
            ])->values(),
        ]);
    }

    private function resolveActingRespondent(): ?Respondent
    {
        $applicant = Auth::guard('applicant')->user();

        return $applicant ? app(ActingRespondent::class)->resolve($applicant) : null;
    }

    private function caseCodeMatches(object $case, string $provided): bool
    {
        $stored = $this->normalizeCaseCode((string) ($case->code ?? ''));
        $input = $this->normalizeCaseCode($provided);

        if ($stored === '' || $input === '') {
            return false;
        }

        return hash_equals($stored, $input);
    }

    private function findCaseForSearch(string $caseNumber): ?object
    {
        $select = ['id', 'case_number', 'code', 'title', 'status', 'created_at', 'applicant_id'];

        $exact = DB::table('court_cases')
            ->select($select)
            ->where('case_number', $caseNumber)
            ->first();
        if ($exact) {
            return $exact;
        }

        $normalizedInput = $this->normalizeCaseNumber($caseNumber);
        if ($normalizedInput === '') {
            return null;
        }

        // Fallback for copied values that include hidden/variant separators or extra spaces.
        return DB::table('court_cases')
            ->select($select)
            ->get()
            ->first(fn ($row) => $this->normalizeCaseNumber((string) ($row->case_number ?? '')) === $normalizedInput);
    }

    private function normalizeCaseNumber(string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return '';
        }

        // Remove invisible format characters and all spaces.
        $value = preg_replace('/\p{Cf}+/u', '', $value) ?? $value;
        $value = preg_replace('/\s+/u', '', $value) ?? $value;

        // Normalize common slash variants users may paste.
        $value = str_replace(['／', '⁄', '∕', '\\'], '/', $value);

        return mb_strtoupper($value, 'UTF-8');
    }

    private function normalizeCaseCode(string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return '';
        }

        $map = [
            '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
            '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
            '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4',
            '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
        ];
        $value = strtr($value, $map);
        $value = preg_replace('/\s+/u', '', $value) ?? $value;

        return mb_strtoupper($value, 'UTF-8');
    }

    private function grantCaseAccess(int $caseId): void
    {
        $access = session('respondent_case_access', []);
        $access[$caseId] = now()->timestamp;
        session(['respondent_case_access' => $access]);
    }

    private function hasCaseAccess(int $caseId): bool
    {
        $access = session('respondent_case_access', []);
        if (! is_array($access)) {
            return false;
        }

        $lifetime = (int) config('session.lifetime', 120);
        $threshold = now()->subMinutes($lifetime)->timestamp;
        $access = array_filter($access, fn ($ts) => is_int($ts) && $ts >= $threshold);

        session(['respondent_case_access' => $access]);

        return array_key_exists($caseId, $access);
    }
}
