<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CaseInspectionRequest;
use App\Models\CourtCase;
use App\Models\User;
use App\Support\EthiopianDate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class CaseInspectionRequestController extends Controller
{
    public function index(): Response
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

        $requests = $query->paginate(12)->withQueryString()
            ->through(fn (CaseInspectionRequest $row): array => $this->presentRow($row));

        return Inertia::render('Admin/CaseInspections/Requests/Index', [
            'requests' => $requests,
            'cases' => $this->caseOptions(),
            'filters' => ['case_id' => $caseId ? (string) $caseId : '', 'status' => $status],
            'statuses' => CaseInspectionRequest::STATUSES,
        ]);
    }

    public function create(): Response
    {
        return $this->form(null, request()->integer('case_id') ?: null, request()->integer('assigned_inspector_user_id') ?: null);
    }

    public function store(Request $request)
    {
        $rules = [
            'court_case_id' => ['required', 'exists:court_cases,id'],
            'request_date' => ['required', 'date'],
            'subject' => ['required', 'string', 'max:255'],
            'request_note' => ['nullable', 'string'],
            'status' => ['required', Rule::in(CaseInspectionRequest::STATUSES)],
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

    public function show(CaseInspectionRequest $requestRecord): Response
    {
        $requestRecord->load(['case', 'requestedBy', 'assignedInspector', 'createdBy', 'findings.recordedBy']);

        return Inertia::render('Admin/CaseInspections/Requests/Show', [
            'request' => [
                ...$this->presentRow($requestRecord),
                'caseTitle' => $requestRecord->case?->title,
                'note' => $requestRecord->request_note,
                'findings' => $requestRecord->findings->map(fn ($finding): array => [
                    'id' => (int) $finding->id,
                    'title' => (string) $finding->title,
                    'date' => EthiopianDate::format($finding->finding_date),
                    'severity' => (string) $finding->severity,
                ])->values(),
            ],
        ]);
    }

    public function edit(CaseInspectionRequest $requestRecord): Response|RedirectResponse
    {
        if ($requestRecord->status === 'completed') {
            return redirect()
                ->route('case-inspection-requests.show', $requestRecord)
                ->with('error', __('case_inspections.requests.completed_locked'));
        }

        return $this->form($requestRecord, null, null);
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
            'status' => ['required', Rule::in(CaseInspectionRequest::STATUSES)],
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

    private function form(?CaseInspectionRequest $record, ?int $prefillCaseId, ?int $prefillInspectorId): Response
    {
        $canAssign = auth()->user()?->hasPermission('assign.inspections') ?? false;

        return Inertia::render('Admin/CaseInspections/Requests/Form', [
            'record' => $record ? [
                'id' => (int) $record->id,
                'court_case_id' => (string) $record->court_case_id,
                'request_date' => $record->request_date?->format('Y-m-d') ?? '',
                'subject' => (string) $record->subject,
                'request_note' => (string) ($record->request_note ?? ''),
                'status' => (string) $record->status,
                'assigned_inspector_user_id' => $record->assigned_inspector_user_id ? (string) $record->assigned_inspector_user_id : '',
            ] : null,
            'defaults' => [
                'court_case_id' => $prefillCaseId ? (string) $prefillCaseId : '',
                'request_date' => now()->format('Y-m-d'),
                'assigned_inspector_user_id' => $prefillInspectorId ? (string) $prefillInspectorId : '',
            ],
            'cases' => $this->caseOptions(),
            'inspectors' => $canAssign
                ? $this->inspectorsForAssign()->map(fn (User $user): array => ['id' => (int) $user->id, 'name' => (string) $user->name])->values()
                : [],
            'canAssign' => $canAssign,
            'statuses' => CaseInspectionRequest::STATUSES,
        ]);
    }

    /** @return array<string, mixed> */
    private function presentRow(CaseInspectionRequest $row): array
    {
        return [
            'id' => (int) $row->id,
            'caseNumber' => $row->case?->case_number,
            'subject' => (string) $row->subject,
            'date' => EthiopianDate::format($row->request_date),
            'status' => (string) $row->status,
            'inspector' => $row->assignedInspector?->name,
            'createdBy' => $row->createdBy?->name,
            'locked' => $row->status === 'completed',
        ];
    }

    /** @return Collection<int, array{id: int, label: string}> */
    private function caseOptions(): Collection
    {
        return CourtCase::orderBy('case_number')->get(['id', 'case_number', 'title'])
            ->map(fn (CourtCase $case): array => ['id' => (int) $case->id, 'label' => trim($case->case_number.' - '.$case->title)])
            ->values();
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
