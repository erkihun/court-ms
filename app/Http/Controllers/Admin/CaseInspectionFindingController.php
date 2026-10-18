<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CaseInspectionFinding;
use App\Models\CaseInspectionRequest;
use App\Support\EthiopianDate;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class CaseInspectionFindingController extends Controller
{
    public function index(): Response
    {
        $user = auth()->user();
        $query = CaseInspectionFinding::with(['request.case', 'recordedBy'])
            ->orderByDesc('finding_date')
            ->orderByDesc('id');

        if (!$this->canAccessAllFindings()) {
            $query->whereHas('request', function ($q) use ($user) {
                $q->where('assigned_inspector_user_id', $user?->id);
            });
        }

        $requestId = request()->integer('request_id');
        if ($requestId) {
            $query->where('case_inspection_request_id', $requestId);
        }

        $caseId = request()->integer('case_id');
        if ($caseId) {
            $query->whereHas('request', function ($q) use ($caseId) {
                $q->where('court_case_id', $caseId);
            });
        }

        $severity = trim((string) request('severity', ''));
        if ($severity !== '') {
            $query->where('severity', $severity);
        }

        $findings = $query->paginate(12)->withQueryString()
            ->through(fn (CaseInspectionFinding $finding): array => $this->presentRow($finding));

        return Inertia::render('Admin/CaseInspections/Findings/Index', [
            'findings' => $findings,
            'requests' => $this->accessibleRequests($caseId ?: null)
                ->map(fn (CaseInspectionRequest $req): array => ['id' => (int) $req->id, 'label' => trim(($req->case?->case_number ?? '').' - '.$req->subject)])
                ->values(),
            'filters' => [
                'request_id' => $requestId ? (string) $requestId : '',
                'severity' => $severity,
                'case_id' => $caseId ? (string) $caseId : '',
            ],
            'severities' => CaseInspectionFinding::SEVERITIES,
        ]);
    }

    public function create(): Response
    {
        return $this->form(null, request()->integer('request_id') ?: null);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'case_inspection_request_id' => ['required', 'exists:case_inspection_requests,id'],
            'finding_date' => ['required', 'date'],
            'title' => ['required', 'string', 'max:255'],
            'details' => ['required', 'string'],
            'severity' => ['required', Rule::in(CaseInspectionFinding::SEVERITIES)],
            'attachment_pdf' => ['nullable', 'file', 'mimes:pdf', 'max:2048'],
        ]);

        $requestRecord = CaseInspectionRequest::findOrFail((int) $data['case_inspection_request_id']);
        if (!$this->canAccessAllFindings() && (int) ($requestRecord->assigned_inspector_user_id ?? 0) !== (int) $request->user()?->id) {
            return back()
                ->withErrors(['case_inspection_request_id' => __('case_inspections.findings.create_assigned_only')])
                ->withInput();
        }

        $attachmentPath = null;
        $attachmentOriginalName = null;
        if ($request->hasFile('attachment_pdf')) {
            $file = $request->file('attachment_pdf');
            $attachmentPath = app(\App\Services\SecureUploadService::class)->store(
                $file,
                'case-inspections/findings',
                'local',
                ['related_type' => 'case_inspection_finding', 'user_id' => $request->user()?->id]
            );
            $attachmentOriginalName = $file->getClientOriginalName();
        }

        CaseInspectionFinding::create([
            'case_inspection_request_id' => $data['case_inspection_request_id'],
            'finding_date' => $data['finding_date'],
            'title' => $data['title'],
            'details' => $data['details'],
            'attachment_path' => $attachmentPath,
            'attachment_original_name' => $attachmentOriginalName,
            'severity' => $data['severity'],
            'recorded_by_user_id' => $request->user()?->id,
            'created_by_user_id' => $request->user()?->id,
            'updated_by_user_id' => $request->user()?->id,
        ]);

        // Auto-complete request once at least one finding is submitted.
        $requestRecord->update([
            'status' => 'completed',
            'updated_by_user_id' => $request->user()?->id,
        ]);

        return redirect()->route('case-inspection-findings.index')->with('success', __('case_inspections.findings.created'));
    }

    public function show(CaseInspectionFinding $finding): Response
    {
        $this->authorizeFindingAccess($finding);
        $finding->load(['request.case', 'recordedBy', 'acceptedBy']);

        return Inertia::render('Admin/CaseInspections/Findings/Show', [
            'finding' => [
                ...$this->presentRow($finding),
                'details' => (string) $finding->details,
                'recordedBy' => $finding->recordedBy?->name,
                'acceptedBy' => $finding->acceptedBy?->name,
            ],
            'canAccept' => $this->canAccessAllFindings() && $finding->accepted_at === null,
        ]);
    }

    public function edit(CaseInspectionFinding $finding): Response|RedirectResponse
    {
        $this->authorizeFindingAccess($finding);
        if ($this->isAcceptedLocked($finding)) {
            return redirect()
                ->route('case-inspection-findings.show', $finding)
                ->with('error', __('case_inspections.findings.accepted_locked'));
        }

        return $this->form($finding, null);
    }

    public function update(Request $request, CaseInspectionFinding $finding)
    {
        $this->authorizeFindingAccess($finding);
        if ($this->isAcceptedLocked($finding)) {
            return redirect()
                ->route('case-inspection-findings.show', $finding)
                ->with('error', __('case_inspections.findings.accepted_locked'));
        }

        $data = $request->validate([
            'case_inspection_request_id' => ['required', 'exists:case_inspection_requests,id'],
            'finding_date' => ['required', 'date'],
            'title' => ['required', 'string', 'max:255'],
            'details' => ['required', 'string'],
            'severity' => ['required', Rule::in(CaseInspectionFinding::SEVERITIES)],
            'attachment_pdf' => ['nullable', 'file', 'mimes:pdf', 'max:2048'],
        ]);

        $requestRecord = CaseInspectionRequest::findOrFail((int) $data['case_inspection_request_id']);
        if (!$this->canAccessAllFindings() && (int) ($requestRecord->assigned_inspector_user_id ?? 0) !== (int) $request->user()?->id) {
            return back()
                ->withErrors(['case_inspection_request_id' => __('case_inspections.findings.update_assigned_only')])
                ->withInput();
        }

        $attachmentPath = $finding->attachment_path;
        $attachmentOriginalName = $finding->attachment_original_name;
        if ($request->hasFile('attachment_pdf')) {
            if ($attachmentPath && Storage::disk('local')->exists($attachmentPath)) {
                app(\App\Services\LegalHoldService::class)->assertFileMayBeDeleted((string) $attachmentPath);
                Storage::disk('local')->delete($attachmentPath);
            }
            $file = $request->file('attachment_pdf');
            $attachmentPath = app(\App\Services\SecureUploadService::class)->store(
                $file,
                'case-inspections/findings',
                'local',
                ['related_type' => 'case_inspection_finding', 'related_id' => (int) $finding->id, 'user_id' => $request->user()?->id]
            );
            $attachmentOriginalName = $file->getClientOriginalName();
        }

        $finding->update([
            'case_inspection_request_id' => $data['case_inspection_request_id'],
            'finding_date' => $data['finding_date'],
            'title' => $data['title'],
            'details' => $data['details'],
            'attachment_path' => $attachmentPath,
            'attachment_original_name' => $attachmentOriginalName,
            'severity' => $data['severity'],
            'updated_by_user_id' => $request->user()?->id,
        ]);

        return redirect()->route('case-inspection-findings.index')->with('success', __('case_inspections.findings.updated'));
    }

    public function destroy(CaseInspectionFinding $finding)
    {
        $this->authorizeFindingAccess($finding);
        if ($this->isAcceptedLocked($finding)) {
            return redirect()
                ->route('case-inspection-findings.show', $finding)
                ->with('error', __('case_inspections.findings.accepted_locked'));
        }

        if ($finding->attachment_path && Storage::disk('local')->exists($finding->attachment_path)) {
            app(\App\Services\LegalHoldService::class)->assertFileMayBeDeleted((string) $finding->attachment_path);
            Storage::disk('local')->delete($finding->attachment_path);
        }

        $finding->delete();

        return redirect()->route('case-inspection-findings.index')->with('success', __('case_inspections.findings.deleted'));
    }

    public function accept(CaseInspectionFinding $finding)
    {
        $this->authorizeFindingAccess($finding);
        abort_unless($this->canAccessAllFindings(), 403);

        if ($finding->accepted_at) {
            return back()->with('success', __('case_inspections.findings.already_accepted'));
        }

        $finding->update([
            'accepted_at' => now(),
            'accepted_by_user_id' => auth()->id(),
            'updated_by_user_id' => auth()->id(),
        ]);

        return back()->with('success', __('case_inspections.findings.accepted_success'));
    }

    public function downloadAttachment(CaseInspectionFinding $finding)
    {
        $this->authorizeFindingAccess($finding);
        abort_if(!$finding->attachment_path, 404);
        abort_unless(Storage::disk('local')->exists($finding->attachment_path), 404);

        $filename = $finding->attachment_original_name ?: ('finding-' . $finding->id . '.pdf');

        return Storage::disk('local')->response(
            $finding->attachment_path,
            $filename,
            ['Content-Type' => 'application/pdf']
        );
    }

    private function form(?CaseInspectionFinding $finding, ?int $prefillRequestId): Response
    {
        return Inertia::render('Admin/CaseInspections/Findings/Form', [
            'finding' => $finding ? [
                'id' => (int) $finding->id,
                'case_inspection_request_id' => (string) $finding->case_inspection_request_id,
                'finding_date' => $finding->finding_date?->format('Y-m-d') ?? '',
                'title' => (string) $finding->title,
                'details' => (string) $finding->details,
                'severity' => (string) $finding->severity,
                'attachmentName' => $finding->attachment_path ? ($finding->attachment_original_name ?? basename((string) $finding->attachment_path)) : null,
                'attachmentUrl' => $finding->attachment_path ? route('case-inspection-findings.attachment', $finding) : null,
            ] : null,
            'defaults' => [
                'case_inspection_request_id' => $prefillRequestId ? (string) $prefillRequestId : '',
                'finding_date' => now()->format('Y-m-d'),
            ],
            'requests' => $this->accessibleRequests(null)
                ->map(fn (CaseInspectionRequest $req): array => [
                    'id' => (int) $req->id,
                    'label' => trim(EthiopianDate::format($req->request_date).' - '.($req->case?->case_number ?? '').' - '.$req->subject),
                ])
                ->values(),
            'severities' => CaseInspectionFinding::SEVERITIES,
        ]);
    }

    /** @return array<string, mixed> */
    private function presentRow(CaseInspectionFinding $finding): array
    {
        return [
            'id' => (int) $finding->id,
            'caseNumber' => $finding->request?->case?->case_number,
            'requestSubject' => $finding->request?->subject,
            'title' => (string) $finding->title,
            'date' => EthiopianDate::format($finding->finding_date),
            'severity' => (string) $finding->severity,
            'accepted' => $finding->accepted_at !== null,
            'attachmentName' => $finding->attachment_path ? ($finding->attachment_original_name ?? null) : null,
            'attachmentUrl' => $finding->attachment_path ? route('case-inspection-findings.attachment', $finding) : null,
        ];
    }

    /** Requests the current user may file findings against (all for admins, otherwise their assignments). */
    private function accessibleRequests(?int $caseId): Collection
    {
        $query = CaseInspectionRequest::with('case')
            ->orderByDesc('request_date')
            ->select(['id', 'court_case_id', 'subject', 'request_date']);

        if (!$this->canAccessAllFindings()) {
            $query->where('assigned_inspector_user_id', auth()->id());
        }
        if ($caseId) {
            $query->where('court_case_id', $caseId);
        }

        return $query->get();
    }

    private function canAccessAllFindings(): bool
    {
        return auth()->user()?->hasRole('admin') ?? false;
    }

    private function isAcceptedLocked(CaseInspectionFinding $finding): bool
    {
        return !is_null($finding->accepted_at);
    }

    private function authorizeFindingAccess(CaseInspectionFinding $finding): void
    {
        if ($this->canAccessAllFindings()) {
            return;
        }

        $assignedInspectorId = (int) ($finding->request?->assigned_inspector_user_id
            ?? CaseInspectionRequest::where('id', $finding->case_inspection_request_id)->value('assigned_inspector_user_id')
            ?? 0);

        abort_unless($assignedInspectorId === (int) auth()->id(), 403);
    }
}
