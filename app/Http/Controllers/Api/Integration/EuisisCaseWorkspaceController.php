<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Integration;

use App\Http\Controllers\Applicant\ApplicantCaseController;
use App\Http\Controllers\Applicant\ApplicantDashboardController;
use App\Http\Controllers\Applicant\ApplicantResponseReplyController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\SecureFileController;
use App\Models\Applicant;
use App\Models\ApplicantResponseReply;
use App\Models\Decision;
use App\Models\Letter;
use App\Models\RespondentResponse;
use App\Services\Notifications\PortalNotificationFeed;
use Barryvdh\DomPDF\Facade\Pdf;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Response as InertiaResponse;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

/**
 * Flow 6 (docs/euisis-integration.md): the employee follows and works on their
 * cases from the EUISIS employee portal, so court-ms's own applicant portal is
 * not used. Every call names the EUISIS employee (`employee_uuid`); the linked
 * court account is signed in for that call only and the applicant portal's own
 * actions run unchanged, with their validation, ownership checks, audit and
 * notifications. Their results come back as JSON (pages as their props,
 * redirects as {message}) or as the file itself.
 */
class EuisisCaseWorkspaceController extends Controller
{
    /** The case page: facts, hearings, documents, messages, responses, letters, decisions and what may be changed. */
    public function workspace(Request $request, int $case): SymfonyResponse
    {
        return $this->asApplicant($request, function (Applicant $applicant) use ($case): JsonResponse {
            $props = $this->props(app(ApplicantCaseController::class)->show($case));
            unset($props['audits']);
            $row = DB::table('court_cases')->where('id', $case)->first(['case_number', 'status', 'review_status', 'review_note']);

            $props['decisions'] = Decision::query()->where('case_number', $row->case_number)->get()
                ->filter(fn (Decision $decision): bool => $decision->isDownloadableByParties())
                ->map(fn (Decision $decision): array => [
                    'id' => (int) $decision->id,
                    'title' => $decision->name ?: $decision->case_number,
                    'decided_at' => $decision->decision_date ? Carbon::parse($decision->decision_date)->toDateString() : null,
                ])->values()->all();
            $props['letters'] = array_values(array_map(function (array $letter): array {
                unset($letter['preview_url']);

                return $letter;
            }, array_filter((array) ($props['letters'] ?? []), fn (array $letter): bool => ($letter['approval_status'] ?? null) === 'approved')));
            $props['review'] = ['status' => $row->review_status, 'note' => $row->review_note];
            $props['can'] = [
                // As the applicant portal: a pending case not yet accepted may be corrected.
                'modify' => $row->status === 'pending' && $row->review_status !== 'accepted',
            ];

            return response()->json(['data' => $props]);
        });
    }

    /** The correction form of a case review sent back: its current values and options. */
    public function edit(Request $request, int $case): SymfonyResponse
    {
        return $this->asApplicant($request, fn (): JsonResponse => response()->json(['data' => $this->props(app(ApplicantCaseController::class)->edit($case))]));
    }

    public function correct(Request $request, int $case): SymfonyResponse
    {
        return $this->asApplicant($request, fn () => app(ApplicantCaseController::class)->update($request, $case));
    }

    public function postMessage(Request $request, int $case): SymfonyResponse
    {
        return $this->asApplicant($request, fn () => app(ApplicantCaseController::class)->postMessage($request, $case));
    }

    public function uploadFile(Request $request, int $case): SymfonyResponse
    {
        return $this->asApplicant($request, fn () => app()->call([app(ApplicantCaseController::class), 'uploadFile'], ['request' => $request, 'id' => $case]));
    }

    public function deleteFile(Request $request, int $case, int $file): SymfonyResponse
    {
        return $this->asApplicant($request, fn () => app()->call([app(ApplicantCaseController::class), 'deleteFile'], ['id' => $case, 'fileId' => $file]));
    }

    public function deleteEvidence(Request $request, int $case, int $evidence): SymfonyResponse
    {
        return $this->asApplicant($request, fn () => app(ApplicantCaseController::class)->deleteEvidence($case, $evidence));
    }

    public function deleteWitness(Request $request, int $case, int $witness): SymfonyResponse
    {
        return $this->asApplicant($request, fn () => app(ApplicantCaseController::class)->deleteWitness($case, $witness));
    }

    /** A respondent response to the case, with the employee's replies to it. */
    public function response(Request $request, int $case, int $response): SymfonyResponse
    {
        return $this->asApplicant($request, fn (): JsonResponse => response()->json(['data' => $this->props(app(ApplicantCaseController::class)->showRespondentResponse($case, $response))]));
    }

    public function reply(Request $request, int $case, int $response, int $reply): SymfonyResponse
    {
        return $this->asApplicant($request, fn (): JsonResponse => response()->json(['data' => $this->props(app(ApplicantResponseReplyController::class)->show($case, $response, $reply))]));
    }

    public function storeReply(Request $request, int $case, int $response): SymfonyResponse
    {
        return $this->asApplicant($request, fn () => app(ApplicantResponseReplyController::class)->store($request, $case, $response));
    }

    public function updateReply(Request $request, int $case, int $response, int $reply): SymfonyResponse
    {
        return $this->asApplicant($request, fn () => app(ApplicantResponseReplyController::class)->update($request, $case, $response, $reply));
    }

    public function destroyReply(Request $request, int $case, int $response, int $reply): SymfonyResponse
    {
        return $this->asApplicant($request, fn () => app(ApplicantResponseReplyController::class)->destroy($case, $response, $reply));
    }

    /**
     * One of the case's files, by kind: evidence, file, response, reply, decision,
     * letter, receipt or hearing (a calendar entry). The same checks as the portal.
     */
    public function download(Request $request, int $case): SymfonyResponse
    {
        $data = $request->validate([
            'kind' => ['required', 'string', 'in:evidence,file,response,reply,decision,letter,receipt,hearing'],
            'id' => ['nullable', 'integer', 'min:1'],
            'response_id' => ['nullable', 'integer', 'min:1'],
        ]);
        $id = (int) ($data['id'] ?? 0);

        return $this->asApplicant($request, function (Applicant $applicant) use ($data, $id, $case, $request): SymfonyResponse {
            $caseRow = DB::table('court_cases')->where('id', $case)->where('applicant_id', $applicant->id)->first();
            abort_if($caseRow === null, 404);
            $files = app(SecureFileController::class);

            return match ($data['kind']) {
                'evidence' => $files->caseEvidence($case, $id),
                'file' => $files->caseFile($case, $id),
                'response' => $files->respondentResponse($request, $this->caseResponse($caseRow, $id)),
                'reply' => $files->applicantResponseReply($request, $case, (int) ($data['response_id'] ?? 0), ApplicantResponseReply::query()->findOrFail($id)),
                'decision' => app(ApplicantDashboardController::class)->downloadDecision($this->caseDecision($caseRow, $id)),
                'letter' => $this->letterPdf($caseRow, $id),
                'receipt' => app(ApplicantCaseController::class)->receiptPdf($case),
                'hearing' => app(ApplicantCaseController::class)->downloadHearingIcs($case, $id),
            };
        });
    }

    /** The employee's court notices, newest first. */
    public function notices(Request $request): SymfonyResponse
    {
        $page = max(1, (int) $request->input('page', 1));

        return $this->asApplicant($request, fn (Applicant $applicant): JsonResponse => response()->json(['data' => PortalNotificationFeed::forApplicant((int) $applicant->id)->feed($page, 20)]));
    }

    public function readNotices(Request $request): SymfonyResponse
    {
        $data = $request->validate([
            'all' => ['nullable', 'boolean'],
            'id' => ['required_without:all', 'nullable', 'string', 'regex:/^[a-z_]+:\d+$/'],
        ]);

        return $this->asApplicant($request, function (Applicant $applicant) use ($data): JsonResponse {
            $feed = PortalNotificationFeed::forApplicant((int) $applicant->id);
            if ((bool) ($data['all'] ?? false)) {
                $feed->markAllRead();
            } else {
                [$type, $source] = explode(':', (string) $data['id'], 2);
                $feed->markRead($type, (int) $source);
            }

            return response()->json(['data' => ['ok' => true]]);
        });
    }

    /**
     * Runs a portal action as the employee's linked court account, for this call only.
     *
     * @param  Closure(Applicant): mixed  $action
     */
    private function asApplicant(Request $request, Closure $action): SymfonyResponse
    {
        $request->validate(['employee_uuid' => ['required', 'uuid']]);
        $applicant = Applicant::query()->where('euisis_employee_uuid', (string) $request->input('employee_uuid'))->first();
        if ($applicant === null) {
            return $this->refusal($request, 404, 'not_linked', 'The employee has no court account yet.');
        }
        if (! $applicant->is_active) {
            return $this->refusal($request, 409, 'account_inactive', 'The court account is deactivated.');
        }

        // The portal actions flash their outcome and read the signed-in applicant.
        $session = app('session')->driver('array');
        $session->start();
        $request->setLaravelSession($session);
        Auth::guard('applicant')->setUser($applicant);

        try {
            $result = $action($applicant);
        } catch (ValidationException $e) {
            return response()->json(['message' => $e->getMessage(), 'errors' => $e->errors(), 'request_id' => $request->attributes->get('request_id')], 422);
        } catch (HttpExceptionInterface $e) {
            $code = match ($e->getStatusCode()) {
                403 => 'forbidden', 404 => 'not_found', 409 => 'conflict', default => 'refused'
            };

            return $this->refusal($request, $e->getStatusCode(), $code, $e->getMessage() ?: $code);
        } finally {
            Auth::guard('applicant')->forgetUser();
        }

        if ($result instanceof RedirectResponse) {
            // A form error flashed by the action (withErrors) is a validation failure.
            $bag = $session->get('errors');
            if ($bag !== null && method_exists($bag, 'getBag') && $bag->getBag('default')->isNotEmpty()) {
                return response()->json(['message' => (string) $bag->getBag('default')->first(), 'errors' => $bag->getBag('default')->toArray(), 'request_id' => $request->attributes->get('request_id')], 422);
            }
            $error = $session->get('error');
            if ($error !== null) {
                return $this->refusal($request, 409, 'refused', is_array($error) ? (string) __((string) ($error['key'] ?? ''), (array) ($error['replace'] ?? [])) : (string) $error);
            }
            $message = $session->get('success') ?? $session->get('ok');

            return response()->json(['data' => ['ok' => true, 'message' => is_array($message) ? (string) __((string) ($message['key'] ?? ''), (array) ($message['replace'] ?? [])) : ($message !== null ? __((string) $message) : null)]]);
        }

        return $result instanceof SymfonyResponse ? $result : response()->json(['data' => $result]);
    }

    /** @return array<string, mixed> a portal page's props, as the page would have received them */
    private function props(InertiaResponse $page): array
    {
        $inertia = Request::create('/', 'GET', server: ['HTTP_X_INERTIA' => 'true']);
        $json = json_decode((string) $page->toResponse($inertia)->getContent(), true);

        return (array) ($json['props'] ?? []);
    }

    private function caseResponse(object $case, int $id): RespondentResponse
    {
        $response = RespondentResponse::query()->findOrFail($id);
        abort_unless((string) $response->case_number === (string) $case->case_number, 404);

        return $response;
    }

    private function caseDecision(object $case, int $id): Decision
    {
        $decision = Decision::query()->findOrFail($id);
        abort_unless((string) $decision->case_number === (string) $case->case_number, 404);

        return $decision;
    }

    /** An approved letter on the case, sent to the applicant, as its PDF. */
    private function letterPdf(object $case, int $id): SymfonyResponse
    {
        $letter = Letter::query()->whereKey($id)->where('case_number', $case->case_number)->where('approval_status', 'approved')->firstOrFail();
        abort_unless((bool) ($letter->send_to_applicant ?? true), 403);

        $pdf = Pdf::loadView('pdf.letter-approved', ['letter' => $letter, 'case' => $case, 'caseNumber' => $case->case_number])->setPaper('a4')->output();
        $name = 'letter-'.preg_replace('/[^A-Za-z0-9_-]+/', '-', (string) ($letter->reference_number ?: $letter->id)).'.pdf';

        return response($pdf, 200, ['Content-Type' => 'application/pdf', 'Content-Disposition' => 'attachment; filename="'.$name.'"']);
    }

    private function refusal(Request $request, int $status, string $code, string $message): JsonResponse
    {
        return response()->json(['message' => $message, 'error_code' => $code, 'request_id' => $request->attributes->get('request_id')], $status);
    }
}
