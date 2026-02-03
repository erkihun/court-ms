<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CaseInspection;
use App\Models\CourtCase;
use App\Models\User;
use Illuminate\Http\Request;

class CaseInspectionController extends Controller
{
    public function index()
    {
        $query = CaseInspection::with(['case', 'inspectedBy'])
            ->orderByDesc('inspection_date')
            ->orderByDesc('id');

        $user = auth()->user();
        $canAssign = $user?->hasPermission('assign.inspections') ?? false;
        $canManage = $user?->hasPermission('case-inspections.manage') ?? false;

        if (!$canAssign && !$canManage) {
            $query->where('inspected_by_user_id', $user?->id);
        }

        $caseId = request()->integer('case_id');
        if ($caseId) {
            $query->where('court_case_id', $caseId);
        }

        $inspections = $query->paginate(10)->withQueryString();

        $cases = CourtCase::query()
            ->whereNotNull('assigned_member_user_id')
            ->orWhereNotNull('assigned_team_id')
            ->orderByDesc('created_at')
            ->get(['id', 'case_number', 'title', 'status', 'notes']);

        return view('admin.case-inspections.index', compact('inspections', 'cases', 'caseId'));
    }

    public function create()
    {
        $cases = CourtCase::orderBy('case_number')->get(['id', 'case_number', 'title']);
        $inspectors = $this->inspectorsForAssign();
        $prefillCaseId = request()->integer('case_id');
        $prefillInspectorId = request()->integer('inspected_by_user_id');

        return view('admin.case-inspections.create', compact('cases', 'inspectors', 'prefillCaseId', 'prefillInspectorId'));
    }

    public function store(Request $request)
    {
        $rules = [
            'court_case_id' => ['required', 'exists:court_cases,id'],
            'inspection_date' => ['required', 'date'],
            'summary' => ['required', 'string', 'max:255'],
            'details' => ['required', 'string'],
        ];

        if ($request->user()?->hasPermission('assign.inspections')) {
            $rules['inspected_by_user_id'] = ['nullable', 'exists:users,id'];
        }

        $data = $request->validate($rules);

        CaseInspection::create([
            'court_case_id' => $data['court_case_id'],
            'inspection_date' => $data['inspection_date'],
            'summary' => $data['summary'],
            'details' => $data['details'],
            'inspected_by_user_id' => $request->user()?->hasPermission('assign.inspections')
                ? ($data['inspected_by_user_id'] ?? $request->user()?->id)
                : $request->user()?->id,
            'created_by_user_id' => $request->user()?->id,
            'updated_by_user_id' => $request->user()?->id,
        ]);

        return redirect()->route('case-inspections.index')->with('success', __('case_inspections.saved'));
    }

    public function show(CaseInspection $caseInspection)
    {
        $caseInspection->load(['case', 'inspectedBy']);

        return view('admin.case-inspections.show', compact('caseInspection'));
    }

    public function edit(CaseInspection $caseInspection)
    {
        $cases = CourtCase::orderBy('case_number')->get(['id', 'case_number', 'title']);
        $inspectors = $this->inspectorsForAssign();
        $prefillCaseId = null;
        $prefillInspectorId = null;

        return view('admin.case-inspections.edit', compact('caseInspection', 'cases', 'inspectors', 'prefillCaseId', 'prefillInspectorId'));
    }

    public function update(Request $request, CaseInspection $caseInspection)
    {
        $rules = [
            'court_case_id' => ['required', 'exists:court_cases,id'],
            'inspection_date' => ['required', 'date'],
            'summary' => ['required', 'string', 'max:255'],
            'details' => ['required', 'string'],
        ];

        if ($request->user()?->hasPermission('assign.inspections')) {
            $rules['inspected_by_user_id'] = ['nullable', 'exists:users,id'];
        }

        $data = $request->validate($rules);

        $caseInspection->update([
            'court_case_id' => $data['court_case_id'],
            'inspection_date' => $data['inspection_date'],
            'summary' => $data['summary'],
            'details' => $data['details'],
            'inspected_by_user_id' => $request->user()?->hasPermission('assign.inspections')
                ? ($data['inspected_by_user_id'] ?? $caseInspection->inspected_by_user_id)
                : $caseInspection->inspected_by_user_id,
            'updated_by_user_id' => $request->user()?->id,
        ]);

        return redirect()->route('case-inspections.index')->with('success', __('case_inspections.updated'));
    }

    public function destroy(CaseInspection $caseInspection)
    {
        $caseInspection->delete();

        return redirect()->route('case-inspections.index')->with('success', __('case_inspections.deleted'));
    }

    private function inspectorsForAssign()
    {
        return User::query()
            ->where('status', 'active')
            ->whereHas('roles.permissions', fn($q) => $q->where('name', 'case-inspections.manage'))
            ->orderBy('name')
            ->get(['id', 'name']);
    }
}
