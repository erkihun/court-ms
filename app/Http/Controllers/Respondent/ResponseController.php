<?php

namespace App\Http\Controllers\Respondent;

use App\Http\Controllers\Controller;
use App\Models\RespondentResponse;
use App\Services\LegalHoldService;
use App\Services\Party\ActingRespondent;
use App\Services\ResponseNotificationService;
use App\Services\SecureUploadService;
use App\Support\EthiopianDate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Mews\Purifier\Facades\Purifier;

class ResponseController extends Controller
{
    public function index(Request $request): InertiaResponse
    {
        $respondentId = $this->currentRespondentId();
        Session::put('acting_as_respondent', true);

        $responses = RespondentResponse::where('respondent_id', $respondentId)
            ->orderByDesc('created_at')
            ->get();

        return Inertia::render('Respondent/Responses/Index', [
            'responses' => $responses->map(fn (RespondentResponse $response): array => [
                'id' => (int) $response->id,
                'title' => (string) $response->title,
                'caseNumber' => $response->case_number,
                'reviewStatus' => $this->reviewStatus($response),
                'reviewNote' => $response->review_status === 'returned' ? ($response->review_note ?: null) : null,
                'hasPdf' => ! empty($response->pdf_path),
                'uploaded' => EthiopianDate::format($response->created_at),
                'locked' => $this->isLocked($response),
            ])->values(),
        ]);
    }

    public function create(Request $request): InertiaResponse
    {
        return Inertia::render('Respondent/Responses/Form', [
            'response' => null,
            'caseNumber' => (string) $request->query('case_number', ''),
        ]);
    }

    public function store(Request $request)
    {
        $applicant = Auth::guard('applicant')->user();
        abort_unless($applicant, 403);
        $respondentId = $this->currentRespondentId();
        Session::put('acting_as_respondent', true);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'case_number' => ['required', 'string', 'max:64'],
            'pdf' => ['required', 'file', 'mimes:pdf', 'max:5120'],
        ]);

        $caseNumber = $this->normalizeCaseNumber($data['case_number'] ?? null);

        if ($caseNumber !== null) {
            $case = DB::table('court_cases')
                ->select(['id', 'status'])
                ->where('case_number', $caseNumber)
                ->first();
            if ($case && $case->status === 'closed') {
                return back()
                    ->withErrors(['case_number' => __('respondent.case_closed')])
                    ->withInput();
            }
        }

        $this->assertNotOwnCase($caseNumber, (int) $applicant->id);
        $this->assertCaseAuthorized($caseNumber, $respondentId, false);

        $path = app(SecureUploadService::class)->store(
            $request->file('pdf'),
            'respondent/responses',
            'private',
            ['related_type' => 'court_case', 'related_id' => (int) $case->id]
        );

        $descHtml = $this->cleanHtml($data['description'] ?? '');

        $response = DB::transaction(function () use ($respondentId, $caseNumber, $data, $path, $descHtml) {
            return RespondentResponse::create([
                'respondent_id' => $respondentId,
                'case_number' => $caseNumber,
                'response_number' => $this->nextResponseNumber($caseNumber),
                'title' => $data['title'],
                'description' => $descHtml ?: null,
                'pdf_path' => $path,
            ]);
        });

        ResponseNotificationService::notifyRespondentResponseCreated($response);

        return redirect()->route('respondent.responses.show', $response);
    }

    public function show(RespondentResponse $response): InertiaResponse
    {
        $this->authorizeOwnership($response);

        return Inertia::render('Respondent/Responses/Show', [
            'response' => [
                'id' => (int) $response->id,
                'title' => (string) $response->title,
                'reviewStatus' => $this->reviewStatus($response),
                'reviewNote' => $response->review_status !== 'accepted' ? ($response->review_note ?: null) : null,
                'created' => EthiopianDate::format($response->created_at, withTime: true),
                'responseNumber' => $response->response_number,
                'caseNumber' => $response->case_number,
                // Purified on save; purified again here for rows stored before that.
                'descriptionHtml' => empty($response->description) ? null : (string) clean($response->description, 'cases'),
                'pdf' => empty($response->pdf_path) ? null : [
                    'downloadUrl' => route('applicant.respondent.responses.download', $response),
                    'inlineUrl' => route('applicant.respondent.responses.download', [$response, 'inline' => 1]),
                ],
                'locked' => $this->isLocked($response),
            ],
        ]);
    }

    public function edit(RespondentResponse $response): InertiaResponse
    {
        $this->authorizeOwnership($response);
        $this->ensureEditable($response);

        return Inertia::render('Respondent/Responses/Form', [
            'response' => [
                'id' => (int) $response->id,
                'title' => (string) $response->title,
                'description' => (string) clean($response->description ?? '', 'cases'),
                'hasPdf' => ! empty($response->pdf_path),
            ],
            'caseNumber' => (string) ($response->case_number ?? ''),
        ]);
    }

    public function update(Request $request, RespondentResponse $response)
    {
        $this->authorizeOwnership($response);
        $this->ensureEditable($response);
        $applicant = Auth::guard('applicant')->user();
        abort_unless($applicant, 403);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'case_number' => ['nullable', 'string', 'max:64'],
            'pdf' => ['nullable', 'file', 'mimes:pdf', 'max:5120'],
        ]);

        $caseNumber = $this->normalizeCaseNumber($data['case_number'] ?? null);

        $this->assertNotOwnCase($caseNumber, (int) $applicant->id);
        $this->assertCaseAuthorized($caseNumber, $this->currentRespondentId(), false);

        if ($request->hasFile('pdf')) {
            app(LegalHoldService::class)->assertFileMayBeDeleted((string) $response->pdf_path);
            Storage::disk('private')->delete($response->pdf_path);
            Storage::disk('public')->delete($response->pdf_path); // legacy cleanup
            $path = app(SecureUploadService::class)->store(
                $request->file('pdf'),
                'respondent/responses',
                'private',
                ['related_type' => 'respondent_response', 'related_id' => (int) $response->id]
            );
            $response->pdf_path = $path;
        }

        $refreshResponseNumber = $caseNumber !== $response->case_number
            || ($caseNumber !== null && empty($response->response_number));

        $descHtml = $this->cleanHtml($data['description'] ?? '');

        DB::transaction(function () use ($response, $caseNumber, $data, $refreshResponseNumber, $descHtml) {
            $response->fill([
                'case_number' => $caseNumber,
                'response_number' => $refreshResponseNumber
                    ? $this->nextResponseNumber($caseNumber, (int) $response->id)
                    : $response->response_number,
                'title' => $data['title'],
                'description' => $descHtml ?: null,
                // A changed response goes back to the court for review.
                'review_status' => 'awaiting_review',
                'review_note' => null,
                'reviewed_by_user_id' => null,
                'reviewed_at' => null,
            ]);
            $response->save();
        });

        return redirect()->route('respondent.responses.show', $response);
    }

    public function destroy(RespondentResponse $response)
    {
        $this->authorizeOwnership($response);
        $this->ensureEditable($response);
        app(LegalHoldService::class)->assertFileMayBeDeleted((string) $response->pdf_path);
        Storage::disk('private')->delete($response->pdf_path);
        Storage::disk('public')->delete($response->pdf_path); // legacy cleanup
        $response->delete();

        return redirect()->route('respondent.responses.index');
    }

    /** Once the court accepts a response it is part of the case record and cannot change. */
    private function isLocked(RespondentResponse $response): bool
    {
        return in_array($response->review_status, ['accepted', 'accept'], true);
    }

    private function ensureEditable(RespondentResponse $response): void
    {
        abort_if($this->isLocked($response), 403, __('respondent.response_locked'));
    }

    private function reviewStatus(RespondentResponse $response): string
    {
        return match ($response->review_status) {
            'accepted', 'accept' => 'accepted',
            'returned', 'rejected' => $response->review_status,
            default => 'awaiting_review',
        };
    }

    private function authorizeOwnership(RespondentResponse $response): void
    {
        abort_if($response->respondent_id !== $this->currentRespondentId(), 403);
    }

    private function currentRespondentId(): int
    {
        // Respondent actions use the applicant guard while acting-as-respondent.
        $applicant = Auth::guard('applicant')->user();
        abort_unless($applicant, 403);

        return (int) app(ActingRespondent::class)->resolve($applicant)->id;
    }

    private function assertNotOwnCase(?string $caseNumber, int $applicantId): void
    {
        if (! $caseNumber) {
            return;
        }

        $case = DB::table('court_cases')
            ->select('applicant_id')
            ->where('case_number', $caseNumber)
            ->first();

        if ($case && (int) $case->applicant_id === $applicantId) {
            abort(403, __('respondent.cannot_respond_own_case'));
        }
    }

    private function assertCaseAuthorized(?string $caseNumber, int $respondentId, bool $api): void
    {
        if (! $caseNumber) {
            return;
        }

        $caseExists = DB::table('court_cases')->where('case_number', $caseNumber)->exists();
        if (! $caseExists) {
            $this->throwCaseNumberError(__('respondent.case_not_found'), $api);
        }

        $authorized = DB::table('respondent_case_views')
            ->where('respondent_id', $respondentId)
            ->where('case_number', $caseNumber)
            ->exists();

        if (! $authorized) {
            $this->throwCaseNumberError(__('respondent.case_not_authorized'), $api);
        }
    }

    private function throwCaseNumberError(string $message, bool $api): void
    {
        if ($api) {
            throw ValidationException::withMessages(['case_number' => [$message]]);
        }

        throw ValidationException::withMessages(['case_number' => [$message]]);
    }

    private function normalizeCaseNumber(?string $caseNumber): ?string
    {
        if ($caseNumber === null) {
            return null;
        }

        $normalized = trim($caseNumber);

        return $normalized === '' ? null : $normalized;
    }

    private function nextResponseNumber(?string $caseNumber, ?int $excludeResponseId = null): ?string
    {
        if ($caseNumber === null) {
            return null;
        }

        DB::table('court_cases')
            ->where('case_number', $caseNumber)
            ->lockForUpdate()
            ->first();

        return RespondentResponse::nextResponseNumberForCase($caseNumber, $excludeResponseId);
    }

    private function cleanHtml(?string $html): string
    {
        $s = (string) ($html ?? '');
        if ($s === '') {
            return '';
        }
        if (str_contains($s, '&lt;') || str_contains($s, '&gt;')) {
            $s = htmlspecialchars_decode($s, ENT_QUOTES);
        }

        return Purifier::clean($s, 'cases');
    }
}
