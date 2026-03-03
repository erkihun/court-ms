<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CaseInspectionRequest;
use App\Models\CourtCase;
use App\Models\User;
use Illuminate\Http\Request;

class CaseInspectionRequestController extends Controller
{
    public function index()
    {
        $query = CaseInspectionRequest::with(['case', 'requestedBy', 'assignedInspector', 'createdBy'])
            ->orderByDesc('request_date')
            ->orderByDesc('id');

        $user = auth()->user();
        $canAssign = $user?->hasPermission('assign.inspections') ?? false;
        $canManage = $user?->hasPermission('inspection-requests.manage') ?? false;

        if (!$canAssign && !$canManage) {
            $query->where(function ($q) use ($user) {
                $q->where('assigned_inspector_user_id', $user?->id)
                    ->orWhere('requested_by_user_id', $user?->id);
            });
        }

        $caseId = request()->integer('case_id');
        if ($caseId) {
            $query->where('court_case_id', $caseId);
        }

        $status = trim((string) request('status', ''));
        if ($status !== '') {
            $query->where('status', $status);
        }

        $requests = $query->paginate(12)->withQueryString();
        $cases = CourtCase::orderBy('case_number')->get(['id', 'case_number', 'title']);

        return view('admin.case-inspections.requests.index', compact('requests', 'cases', 'caseId', 'status'));
    }

    public function create()
    {
        $cases = CourtCase::orderBy('case_number')->get(['id', 'case_number', 'title']);
        $inspectors = $this->inspectorsForAssign();
        $prefillCaseId = request()->integer('case_id');
        $prefillInspectorId = request()->integer('assigned_inspector_user_id');

        return view('admin.case-inspections.requests.create', compact('cases', 'inspectors', 'prefillCaseId', 'prefillInspectorId'));
    }

    public function store(Request $request)
    {
        $rules = [
            'court_case_id' => ['required', 'exists:court_cases,id'],
            'request_date' => ['required', 'date'],
            'subject' => ['required', 'string', 'max:255'],
            'request_note' => ['nullable', 'string'],
            'status' => ['required', 'in:pending,in_progress,completed,cancelled'],
        ];

        if ($request->user()?->hasPermission('assign.inspections')) {
            $rules['assigned_inspector_user_id'] = ['nullable', 'exists:users,id'];
        }

        $data = $request->validate($rules);

        CaseInspectionRequest::create([
            'court_case_id' => $data['court_case_id'],
            'request_date' => $data['request_date'],
            'subject' => $data['subject'],
            'request_note' => $data['request_note'] ?? null,
            'status' => $data['status'],
            'requested_by_user_id' => $request->user()?->id,
            'assigned_inspector_user_id' => $request->user()?->hasPermission('assign.inspections')
                ? ($data['assigned_inspector_user_id'] ?? null)
                : $request->user()?->id,
            'created_by_user_id' => $request->user()?->id,
            'updated_by_user_id' => $request->user()?->id,
        ]);

        return redirect()->route('case-inspection-requests.index')->with('success', __('case_inspections.requests.created'));
    }

    public function show(CaseInspectionRequest $requestRecord)
    {
        $requestRecord->load(['case', 'requestedBy', 'assignedInspector', 'createdBy', 'findings.recordedBy']);

        return view('admin.case-inspections.requests.show', [
            'requestRecord' => $requestRecord,
        ]);
    }

    public function edit(CaseInspectionRequest $requestRecord)
    {
        if ($requestRecord->status === 'completed') {
            return redirect()
                ->route('case-inspection-requests.show', $requestRecord)
                ->with('error', __('case_inspections.requests.completed_locked'));
        }

        $cases = CourtCase::orderBy('case_number')->get(['id', 'case_number', 'title']);
        $inspectors = $this->inspectorsForAssign();
        $prefillCaseId = null;
        $prefillInspectorId = null;

        return view('admin.case-inspections.requests.edit', compact('requestRecord', 'cases', 'inspectors', 'prefillCaseId', 'prefillInspectorId'));
    }

    public function update(Request $request, CaseInspectionRequest $requestRecord)
    {
        if ($requestRecord->status === 'completed') {
            return redirect()
                ->route('case-inspection-requests.show', $requestRecord)
                ->with('error', __('case_inspections.requests.completed_locked'));
        }

        $rules = [
            'court_case_id' => ['required', 'exists:court_cases,id'],
            'request_date' => ['required', 'date'],
            'subject' => ['required', 'string', 'max:255'],
            'request_note' => ['nullable', 'string'],
            'status' => ['required', 'in:pending,in_progress,completed,cancelled'],
        ];

        if ($request->user()?->hasPermission('assign.inspections')) {
            $rules['assigned_inspector_user_id'] = ['nullable', 'exists:users,id'];
        }

        $data = $request->validate($rules);

        $requestRecord->update([
            'court_case_id' => $data['court_case_id'],
            'request_date' => $data['request_date'],
            'subject' => $data['subject'],
            'request_note' => $data['request_note'] ?? null,
            'status' => $data['status'],
            'assigned_inspector_user_id' => $request->user()?->hasPermission('assign.inspections')
                ? ($data['assigned_inspector_user_id'] ?? null)
                : ($requestRecord->assigned_inspector_user_id ?? $request->user()?->id),
            'updated_by_user_id' => $request->user()?->id,
        ]);

        return redirect()->route('case-inspection-requests.index')->with('success', __('case_inspections.requests.updated'));
    }

    public function destroy(CaseInspectionRequest $requestRecord)
    {
        if ($requestRecord->status === 'completed') {
            return redirect()
                ->route('case-inspection-requests.show', $requestRecord)
                ->with('error', __('case_inspections.requests.completed_locked'));
        }

        $requestRecord->delete();

        return redirect()->route('case-inspection-requests.index')->with('success', __('case_inspections.requests.deleted'));
    }

    private function inspectorsForAssign()
    {
        return User::query()
            ->where('status', 'active')
            ->whereHas('roles.permissions', fn($q) => $q->where('name', 'inspection-findings.manage'))
            ->orderBy('name')
            ->get(['id', 'name']);
    }
}
