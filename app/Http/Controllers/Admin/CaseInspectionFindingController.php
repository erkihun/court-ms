<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CaseInspectionFinding;
use App\Models\CaseInspectionRequest;
use Illuminate\Http\Request;

class CaseInspectionFindingController extends Controller
{
    public function index()
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

        $findings = $query->paginate(12)->withQueryString();
        $requestsQuery = CaseInspectionRequest::with('case')
            ->orderByDesc('request_date')
            ->select(['id', 'court_case_id', 'subject', 'request_date']);

        if (!$this->canAccessAllFindings()) {
            $requestsQuery->where('assigned_inspector_user_id', $user?->id);
        }
        if ($caseId) {
            $requestsQuery->where('court_case_id', $caseId);
        }

        $requests = $requestsQuery->get();

        return view('admin.case-inspections.findings.index', compact('findings', 'requests', 'requestId', 'caseId', 'severity'));
    }

    public function create()
    {
        $user = auth()->user();
        $requestsQuery = CaseInspectionRequest::with('case')
            ->orderByDesc('request_date')
            ->select(['id', 'court_case_id', 'subject', 'request_date']);

        if (!$this->canAccessAllFindings()) {
            $requestsQuery->where('assigned_inspector_user_id', $user?->id);
        }

        $requests = $requestsQuery->get();
        $prefillRequestId = request()->integer('request_id');

        return view('admin.case-inspections.findings.create', compact('requests', 'prefillRequestId'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'case_inspection_request_id' => ['required', 'exists:case_inspection_requests,id'],
            'finding_date' => ['required', 'date'],
            'title' => ['required', 'string', 'max:255'],
            'details' => ['required', 'string'],
            'recommendation' => ['nullable', 'string'],
            'severity' => ['required', 'in:low,medium,high,critical'],
        ]);

        $requestRecord = CaseInspectionRequest::findOrFail((int) $data['case_inspection_request_id']);
        if (!$this->canAccessAllFindings() && (int) ($requestRecord->assigned_inspector_user_id ?? 0) !== (int) $request->user()?->id) {
            return back()
                ->withErrors(['case_inspection_request_id' => 'You can only create findings for requests assigned to you.'])
                ->withInput();
        }

        CaseInspectionFinding::create([
            'case_inspection_request_id' => $data['case_inspection_request_id'],
            'finding_date' => $data['finding_date'],
            'title' => $data['title'],
            'details' => $data['details'],
            'recommendation' => $data['recommendation'] ?? null,
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

        return redirect()->route('case-inspection-findings.index')->with('success', 'Inspection finding created.');
    }

    public function show(CaseInspectionFinding $finding)
    {
        $this->authorizeFindingAccess($finding);
        $finding->load(['request.case', 'recordedBy', 'acceptedBy']);

        return view('admin.case-inspections.findings.show', compact('finding'));
    }

    public function edit(CaseInspectionFinding $finding)
    {
        $this->authorizeFindingAccess($finding);
        if ($this->isAcceptedLocked($finding)) {
            return redirect()
                ->route('case-inspection-findings.show', $finding)
                ->with('error', __('case_inspections.findings.accepted_locked'));
        }

        $user = auth()->user();
        $requestsQuery = CaseInspectionRequest::with('case')
            ->orderByDesc('request_date')
            ->select(['id', 'court_case_id', 'subject', 'request_date']);

        if (!$this->canAccessAllFindings()) {
            $requestsQuery->where('assigned_inspector_user_id', $user?->id);
        }

        $requests = $requestsQuery->get();

        return view('admin.case-inspections.findings.edit', compact('finding', 'requests'));
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
            'recommendation' => ['nullable', 'string'],
            'severity' => ['required', 'in:low,medium,high,critical'],
        ]);

        $requestRecord = CaseInspectionRequest::findOrFail((int) $data['case_inspection_request_id']);
        if (!$this->canAccessAllFindings() && (int) ($requestRecord->assigned_inspector_user_id ?? 0) !== (int) $request->user()?->id) {
            return back()
                ->withErrors(['case_inspection_request_id' => 'You can only update findings for requests assigned to you.'])
                ->withInput();
        }

        $finding->update([
            'case_inspection_request_id' => $data['case_inspection_request_id'],
            'finding_date' => $data['finding_date'],
            'title' => $data['title'],
            'details' => $data['details'],
            'recommendation' => $data['recommendation'] ?? null,
            'severity' => $data['severity'],
            'updated_by_user_id' => $request->user()?->id,
        ]);

        return redirect()->route('case-inspection-findings.index')->with('success', 'Inspection finding updated.');
    }

    public function destroy(CaseInspectionFinding $finding)
    {
        $this->authorizeFindingAccess($finding);
        if ($this->isAcceptedLocked($finding)) {
            return redirect()
                ->route('case-inspection-findings.show', $finding)
                ->with('error', __('case_inspections.findings.accepted_locked'));
        }

        $finding->delete();

        return redirect()->route('case-inspection-findings.index')->with('success', 'Inspection finding deleted.');
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
