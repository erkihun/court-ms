<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\Team;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;

class CaseController extends Controller
{
    /**
     * Admin: list cases with optional search & filters.
     */
    public function index(Request $request)
    {
        // Permission is enforced in routes via perm:cases.view
        $q          = trim($request->string('q')->toString());
        $status     = $request->string('status')->toString();          // pending|active|adjourned|dismissed|closed
        $caseTypeId = $request->integer('case_type_id');

        $assigneeId = $request->integer('assignee_id');
        $from       = $request->date('from'); // yyyy-mm-dd
        $to         = $request->date('to');   // yyyy-mm-dd

        $isReviewer = false;
        if (function_exists('userHasPermission')) {
            $isReviewer = (bool) userHasPermission('cases.review');
        } elseif (Auth::user()) {
            $isReviewer = (bool) Auth::user()->can('cases.review');
        }

        $builder = DB::table('court_cases as c')
            ->leftJoin('case_types as ct', 'ct.id', '=', 'c.case_type_id')

            ->leftJoin('users as ass', 'ass.id', '=', 'c.assigned_user_id')
            ->select(
                'c.*',
                'ct.name as case_type',

                'ass.name as assignee_name'
            );

        if ($q !== '') {
            $builder->where(function ($w) use ($q) {
                $w->where('c.case_number', 'like', "%{$q}%")
                    ->orWhere('c.title', 'like', "%{$q}%")

                    ->orWhere('ct.name', 'like', "%{$q}%");
            });
        }

        if ($status !== '')   $builder->where('c.status', $status);
        if ($caseTypeId)      $builder->where('c.case_type_id', $caseTypeId);

        if ($assigneeId)      $builder->where('c.assigned_user_id', $assigneeId);
        if ($from)            $builder->whereDate('c.filing_date', '>=', $from->format('Y-m-d'));
        if ($to)              $builder->whereDate('c.filing_date', '<=', $to->format('Y-m-d'));

        $memberScopeIds = $this->teamLeaderAssignmentIds(Auth::user());
        if (!empty($memberScopeIds)) {
            $builder->whereIn('c.assigned_user_id', $memberScopeIds);
        }

        $memberScopeIds = $this->teamLeaderAssignmentIds(Auth::user());
        if (!empty($memberScopeIds)) {
            $builder->whereIn('c.assigned_user_id', $memberScopeIds);
        }

        if (!$isReviewer)     $builder->where('c.review_status', 'accepted');

        $cases = $builder
            ->orderByRaw('COALESCE(c.created_at, c.filing_date) DESC')
            ->paginate(10)
            ->withQueryString();

        // For filter dropdowns
        $types  = DB::table('case_types')->orderBy('name')->get(['id', 'name']);

        $users  = DB::table('users')->where('status', 'active')->orderBy('name')->get(['id', 'name']);

        return view('admin.cases.index', compact(
            'cases',
            'q',
            'status',
            'caseTypeId',

            'assigneeId',
            'from',
            'to',
            'types',

            'users',
            'isReviewer'
        ));
    }

    /**
     * Admin: export filtered list as CSV.
     */
    public function export(Request $request)
    {
        $q          = trim($request->string('q')->toString());
        $status     = $request->string('status')->toString();
        $caseTypeId = $request->integer('case_type_id');

        $assigneeId = $request->integer('assignee_id');
        $from       = $request->date('from');
        $to         = $request->date('to');

        $builder = DB::table('court_cases as c')
            ->leftJoin('case_types as ct', 'ct.id', '=', 'c.case_type_id')

            ->leftJoin('users as ass', 'ass.id', '=', 'c.assigned_user_id')
            ->select(
                'c.case_number',
                'c.title',
                'ct.name as case_type',

                'c.status',
                'c.filing_date',
                'ass.name as assignee_name'
            );

        if ($q !== '') {
            $builder->where(function ($w) use ($q) {
                $w->where('c.case_number', 'like', "%{$q}%")
                    ->orWhere('c.title', 'like', "%{$q}%")

                    ->orWhere('ct.name', 'like', "%{$q}%");
            });
        }
        if ($status !== '')   $builder->where('c.status', $status);
        if ($caseTypeId)      $builder->where('c.case_type_id', $caseTypeId);

        if ($assigneeId)      $builder->where('c.assigned_user_id', $assigneeId);
        if ($from)            $builder->whereDate('c.filing_date', '>=', $from->format('Y-m-d'));
        if ($to)              $builder->whereDate('c.filing_date', '<=', $to->format('Y-m-d'));

        $rows = $builder->orderBy('c.filing_date')->get();

        $filename = 'cases-export-' . now()->format('Ymd_His') . '.csv';
        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Case #', 'Title', 'Type',  'Status', 'Filed', 'Assignee']);
            foreach ($rows as $r) {
                fputcsv($out, [
                    $r->case_number,
                    $r->title,
                    $r->case_type,

                    ucfirst($r->status),
                    \Illuminate\Support\Carbon::parse($r->filing_date)->format('Y-m-d'),
                    $r->assignee_name,
                ]);
            }
            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Admin: show assign form.
     */
    public function assignForm(int $caseId)
    {
        $case = DB::table('court_cases as c')
            ->leftJoin('users as u', 'u.id', '=', 'c.assigned_user_id')
            ->select('c.*', 'u.name as assignee_name', 'u.email as assignee_email')
            ->where('c.id', $caseId)
            ->first();

        abort_if(!$case, 404);

        $scope = $this->buildAssignmentScope();

        return view('admin.cases.assign', [
            'case' => $case,
            'assignmentMode' => $scope['mode'],
            'teams' => $scope['teams'],
            'leaderTeam' => $scope['leaderTeam'],
        ]);
    }

    /**
     * Admin: update assignment (assign / unassign).
     */
    public function assignUpdate(Request $request, int $caseId)
    {
        $scope = $this->buildAssignmentScope();

        $rules = [
            'assigned_user_id' => [
                'nullable',
                'integer',
                Rule::exists('users', 'id')->where(fn($q) => $q->where('status', 'active')),
            ],
            'unassign' => ['sometimes', 'boolean'],
        ];

        if (!empty($scope['allowedUserIds'])) {
            $rules['assigned_user_id'][] = Rule::in($scope['allowedUserIds']);
        }

        $validated = $request->validate($rules);

        $case = DB::table('court_cases')->where('id', $caseId)->first();
        abort_if(!$case, 404);

        $assigning = !$request->boolean('unassign') && !empty($validated['assigned_user_id']);

        DB::table('court_cases')
            ->where('id', $caseId)
            ->update([
                'assigned_user_id' => $assigning ? $validated['assigned_user_id'] : null,
                'assigned_at'      => $assigning ? Carbon::now() : null,
                'updated_at'       => Carbon::now(),
            ]);

        $this->logCaseAudit($caseId, $assigning ? 'assigned' : 'unassigned', [
            'assigned_user_id' => $assigning ? $validated['assigned_user_id'] : null,
        ]);

        return redirect()
            ->route('cases.index')
            ->with('success', $assigning ? 'Case assigned successfully.' : 'Case unassigned.');
    }

    private function buildAssignmentScope(): array
    {
        $user = Auth::user();
        abort_if(!$user, 403, 'Authenticated user required.');
        if (!$user->hasPermission('cases.assign')) {
            throw new HttpResponseException(
                redirect()->route('cases.index')
                    ->with('error', 'You are not allowed to access case assignment.')
            );
        }

        $leaderTeam = null;
        if ($user->hasPermission('cases.assign.member')) {
            $leaderTeam = Team::with(['users' => fn ($q) => $q->where('status', 'active')->orderBy('name')])
                ->where('team_leader_id', $user->id)
                ->first();

            if ($leaderTeam) {
                return [
                    'mode' => 'leader',
                    'leaderTeam' => $leaderTeam,
                    'teams' => collect(),
                    'allowedUserIds' => $leaderTeam->users->pluck('id')->unique()->values()->all(),
                ];
            }
        }

        if (!$user->hasPermission('cases.assign.team')) {
            throw new HttpResponseException(
                redirect()->route('cases.index')
            ->with('error', 'Assigning to team leaders requires the cases.assign.team permission.')
            );
        }

        $teams = Team::with(['leader' => fn ($q) => $q->where('status', 'active')])
            ->whereNotNull('team_leader_id')
            ->whereHas('leader', fn ($q) => $q->where('status', 'active'))
            ->orderBy('name')
            ->get();

        $leaderIds = $teams->pluck('team_leader_id')->filter()->unique();

        return [
            'mode' => 'admin',
            'leaderTeam' => null,
            'teams' => $teams,
            'allowedUserIds' => $leaderIds->unique()->values()->all(),
        ];
    }

    /**
     * Admin: show a case (with context).
     * Evidence REMOVED (we use Uploaded Files only). $docs left empty to keep the Blade happy.
     *
     * NOTE: description/relief were already purified on applicant save/update.
     * We expose them as *_html so Blade can render with `{!! $case->description_html !!}` safely.
     */
    public function show(int $id)
    {
        $this->authorizeView();

        $case = DB::table('court_cases as c')
            ->leftJoin('case_types as ct', 'ct.id', '=', 'c.case_type_id')

            ->leftJoin('users as ass', 'ass.id', '=', 'c.assigned_user_id')
            ->leftJoin('applicants as ap', 'ap.id', '=', 'c.applicant_id')
            ->select(
                'c.*',
                'ct.name as case_type',

                'ass.name as assignee_name',
                'ass.email as assignee_email',
                DB::raw("CONCAT(COALESCE(ap.first_name,''),' ',COALESCE(ap.last_name,'')) as applicant_name"),
                'ap.email as applicant_email'
            )
            ->where('c.id', $id)
            ->first();

        abort_if(!$case, 404, 'Case not found.');

        // Make HTML fields explicit for Blade raw rendering
        $case->description_html        = (string) ($case->description ?? '');
        $case->relief_requested_html   = (string) ($case->relief_requested ?? '');

        $timeline = DB::table('case_status_logs')
            ->select(
                'case_status_logs.*',
                DB::raw('NULL AS note') // so $t->note always exists
            )
            ->where('case_id', $id)
            ->orderBy('created_at')
            ->get();

        // Uploaded Files (admin + applicant)
        $files = DB::table('case_files as f')
            ->leftJoin('applicants as a', 'a.id', '=', 'f.uploaded_by_applicant_id')
            ->leftJoin('users as u', 'u.id', '=', 'f.uploaded_by_user_id')
            ->select('f.*', 'a.first_name', 'a.last_name', 'u.name as uploader_name')
            ->where('f.case_id', $id)
            ->orderByDesc('f.created_at')
            ->get();

        // Messages
        $messages = DB::table('case_messages as m')
            ->leftJoin('applicants as a', 'a.id', '=', 'm.sender_applicant_id')
            ->leftJoin('users as u', 'u.id', '=', 'm.sender_user_id')
            ->select('m.*', 'a.first_name', 'a.last_name', 'u.name as admin_name')
            ->where('m.case_id', $id)
            ->orderBy('m.created_at')
            ->get();

        // Hearings
        $hearings = DB::table('case_hearings')
            ->where('case_id', $id)
            ->orderBy('hearing_at')
            ->get();

        // Submitted documents – tolerate partial schemas by selecting only existing columns.
        $docColumns = ['e.id', 'e.created_at'];
        $hasColumn = fn(string $col) => Schema::hasColumn('case_evidences', $col);

        foreach (['title', 'description', 'file_path', 'path', 'mime', 'size', 'type'] as $col) {
            if ($hasColumn($col)) {
                $docColumns[] = "e.{$col}";
            }
        }

        $docs = DB::table('case_evidences as e')
            ->select($docColumns)
            ->where('e.case_id', $id)
            ->when(
                $hasColumn('type'),
                fn($q) => $q->where('e.type', 'document')
            )
            ->orderBy('e.id')
            ->get();

        // Witnesses
        $witnesses = DB::table('case_witnesses')
            ->where('case_id', $id)
            ->orderBy('full_name')
            ->get();

        $audits = DB::table('case_audits')
            ->where('case_id', $id)
            ->orderByDesc('id')
            ->limit(200)
            ->get();

        // Enrich audits with actor names
        $userNames = collect();
        $applicantNames = collect();
        $userIds = $audits->where('actor_type', 'user')->pluck('actor_id')->filter()->unique();
        $applicantIds = $audits->where('actor_type', 'applicant')->pluck('actor_id')->filter()->unique();

        if ($userIds->isNotEmpty()) {
            $userNames = DB::table('users')->whereIn('id', $userIds)->pluck('name', 'id');
        }
        if ($applicantIds->isNotEmpty()) {
            $applicantNames = DB::table('applicants')
                ->whereIn('id', $applicantIds)
                ->select('id', 'first_name', 'last_name')
                ->get()
                ->mapWithKeys(fn($r) => [$r->id => trim(($r->first_name ?? '') . ' ' . ($r->last_name ?? ''))]);
        }

        foreach ($audits as $a) {
            if ($a->actor_type === 'user' && $a->actor_id) {
                $a->actor_name = $userNames[$a->actor_id] ?? null;
            } elseif ($a->actor_type === 'applicant' && $a->actor_id) {
                $a->actor_name = $applicantNames[$a->actor_id] ?? null;
            } else {
                $a->actor_name = null;
            }
        }

        return view('admin.cases.show', [
            'case'       => $case,
            'timeline'   => $timeline,
            'files'      => $files,
            'messages'   => $messages,
            'hearings'   => $hearings,
            'docs'       => $docs,        // empty collection; Blade stays compatible
            'witnesses'  => $witnesses,
            'audits'     => $audits,
        ]);
    }

    /**
     * Registrar/Reviewer: accept, return for correction, or reject a submitted case.
     */
    public function reviewDecision(Request $request, int $id)
    {
        $case = DB::table('court_cases')->where('id', $id)->first();
        abort_if(!$case, 404, 'Case not found.');

        $data = $request->validate([
            'decision' => ['required', 'in:accept,return,reject'],
            'note'     => ['nullable', 'string', 'max:2000'],
        ]);

        $decision = $data['decision'];
        $note     = trim((string) ($data['note'] ?? ''));

        if (in_array($decision, ['return', 'reject'], true) && $note === '') {
            return back()->withErrors(['note' => 'Please add a note when returning or rejecting.'])->withInput();
        }

        $newStatus = match ($decision) {
            'accept' => 'accepted',
            'return' => 'returned', // needs applicant correction
            'reject' => 'rejected',
        };

        DB::table('court_cases')->where('id', $id)->update([
            'review_status'       => $newStatus,
            'review_note'         => $note !== '' ? $note : null,
            'reviewed_by_user_id' => Auth::id(),
            'reviewed_at'         => now(),
            'updated_at'          => now(),
        ]);

        $this->logCaseAudit($id, 'review_decision', [
            'decision' => $newStatus,
            'note'     => $note ?: null,
        ]);

        $this->notifyApplicantOfReview($case, $newStatus, $note);

        return back()->with('success', 'Review updated: ' . ucfirst($newStatus) . '.');
    }

    /**
     * POST alias (legacy) for reviewDecision; expects case_id/id in the payload.
     */
    public function review(Request $request)
    {
        $caseId = $request->integer('case_id') ?: $request->integer('id');
        abort_if(!$caseId, 400, 'Case id is required.');

        return $this->reviewDecision($request, $caseId);
    }

    /**
     * Admin: update case status + log timeline + email applicant.
     */
    public function updateStatus(Request $request, int $id)
    {
        $allowed = ['pending', 'active', 'adjourned', 'dismissed', 'closed'];

        $data = $request->validate([
            'status' => ['required', 'in:' . implode(',', $allowed)],
            'note'   => ['nullable', 'string', 'max:2000'],
        ]);

        $case = DB::table('court_cases')->where('id', $id)->first();
        abort_if(!$case, 404);

        $old = $case->status;
        $new = $data['status'];

        if ($old === $new) {
            return back()->with('success', 'Status unchanged (already ' . $new . ').');
        }

        DB::table('court_cases')->where('id', $id)->update([
            'status'     => $new,
            'updated_at' => now(),
        ]);

        // Email applicant (best-effort) — uses applicant_notification_prefs
        try {
            if (!empty($case->applicant_id)) {
                $to = DB::table('applicants')->where('id', $case->applicant_id)->value('email');
                if ($to) {
                    $prefs = DB::table('applicant_notification_prefs')->where('applicant_id', $case->applicant_id)->first();
                    $wants = !$prefs || ($prefs->email_status ?? true);

                    if ($wants) {
                        Mail::to($to)->send(
                            new \App\Mail\CaseStatusChangedMail($case, $old, $new, $data['note'] ?? null)
                        );
                    }
                } else {
                    Log::info('Status mail skipped (no applicant email)', ['case_id' => $case->id]);
                }
            }
        } catch (\Throwable $e) {
            Log::error('Failed sending case status email', ['case_id' => $case->id, 'error' => $e->getMessage()]);
        }

        // Log timeline
        DB::table('case_status_logs')->insert([
            'case_id'            => $id,
            'from_status'        => $old,
            'to_status'          => $new,
            'changed_by_user_id' => Auth::id(),
            'created_at'         => now(),
            'updated_at'         => now(),
        ]);

        $this->logCaseAudit($id, 'status_updated', [
            'from' => $old,
            'to'   => $new,
            'note' => $data['note'] ?? null,
        ]);

        // Optional: visible note to thread
        if (!empty($data['note'])) {
            DB::table('case_messages')->insert([
                'case_id'             => $id,
                'sender_user_id'      => Auth::id(),
                'sender_applicant_id' => null,
                'body'                => '[Status changed to ' . ucfirst($new) . '] ' . $data['note'],
                'created_at'          => now(),
                'updated_at'          => now(),
            ]);
        }

        return back()->with('success', 'Case status updated to ' . ucfirst($new) . '.');
    }

    /**
     * Admin → Applicant message (and email).
     */
    public function postAdminMessage(Request $request, int $id)
    {
        $data = $request->validate([
            'body' => ['required', 'string', 'max:5000'],
        ]);

        $case = DB::table('court_cases')->where('id', $id)->first();
        abort_if(!$case, 404);

        DB::table('case_messages')->insert([
            'case_id'             => $id,
            'sender_user_id'      => Auth::id(),
            'sender_applicant_id' => null,
            'body'                => $data['body'],
            'created_at'          => now(),
            'updated_at'          => now(),
        ]);

        // Notify applicant by email (best-effort)
        try {
            if (!empty($case->applicant_id)) {
                $to = DB::table('applicants')->where('id', $case->applicant_id)->value('email');
                if ($to) {
                    $prefs = DB::table('applicant_notification_prefs')->where('applicant_id', $case->applicant_id)->first();
                    $wants = !$prefs || ($prefs->email_message ?? true);

                    if ($wants) {
                        $preview = mb_strimwidth($data['body'], 0, 180, '…');
                        Mail::to($to)->send(new \App\Mail\CaseMessageMail($case, 'Court Staff', $preview));
                    }
                } else {
                    Log::info('Message mail skipped (no applicant email)', ['case_id' => $id]);
                }
            }
        } catch (\Throwable $e) {
            Log::error('Admin message email failed', ['case_id' => $id, 'error' => $e->getMessage()]);
        }

        $this->logCaseAudit($id, 'message_posted', [
            'by'   => 'admin',
            'body' => mb_strimwidth($data['body'], 0, 200, '...'),
        ]);

        return back()->with('success', 'Message sent to applicant.');
    }

    /**
     * Admin: create hearing and email applicant.
     */
    public function storeHearing(Request $request, int $case)
    {
        $c = DB::table('court_cases')->where('id', $case)->first();
        abort_if(!$c, 404);

        $data = $request->validate([
            'hearing_at' => ['required', 'date'],             // e.g., "2025-10-20 10:30"
            'location'   => ['nullable', 'string', 'max:255'],
            'type'       => ['nullable', 'string', 'max:100'],
            'notes'      => ['nullable', 'string', 'max:2000'],
        ]);

        $hearingId = DB::table('case_hearings')->insertGetId([
            'case_id'            => $case,
            'hearing_at'         => $data['hearing_at'],
            'location'           => $data['location'] ?? null,
            'type'               => $data['type'] ?? null,
            'notes'              => $data['notes'] ?? null,
            'created_by_user_id' => Auth::id(),
            'created_at'         => now(),
            'updated_at'         => now(),
        ]);

        $this->logCaseAudit($case, 'hearing_created', [
            'hearing_id' => $hearingId,
            'when'       => $data['hearing_at'],
            'location'   => $data['location'] ?? null,
            'type'       => $data['type'] ?? null,
        ]);

        // Email applicant (best-effort)
        try {
            if (!empty($c->applicant_id)) {
                $to = DB::table('applicants')->where('id', $c->applicant_id)->value('email');
                if ($to) {
                    $prefs = DB::table('applicant_notification_prefs')->where('applicant_id', $c->applicant_id)->first();
                    $wants = !$prefs || ($prefs->email_hearing ?? true);

                    if ($wants) {
                        $hearing = DB::table('case_hearings')->where('id', $hearingId)->first();
                        Mail::to($to)->send(new \App\Mail\CaseHearingScheduledMail($c, $hearing));
                    }
                } else {
                    Log::info('Hearing mail skipped (no applicant email)', ['case_id' => $case]);
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Hearing mail failed', ['case_id' => $case, 'error' => $e->getMessage()]);
        }

        return back()->with('success', 'Hearing scheduled.');
    }

    /**
     * Admin: update hearing.
     */
    public function updateHearing(Request $request, int $hearing)
    {
        $h = DB::table('case_hearings')->where('id', $hearing)->first();
        abort_if(!$h, 404);

        $data = $request->validate([
            'hearing_at' => ['required', 'date'],
            'location'   => ['nullable', 'string', 'max:255'],
            'type'       => ['nullable', 'string', 'max:100'],
            'notes'      => ['nullable', 'string', 'max:2000'],
        ]);

        DB::table('case_hearings')->where('id', $hearing)->update([
            'hearing_at' => $data['hearing_at'],
            'location'   => $data['location'] ?? null,
            'type'       => $data['type'] ?? null,
            'notes'      => $data['notes'] ?? null,
            'updated_at' => now(),
        ]);

        $this->logCaseAudit($h->case_id, 'hearing_updated', [
            'hearing_id' => $hearing,
            'when'       => $data['hearing_at'],
        ]);

        return back()->with('success', 'Hearing updated.');
    }

    /**
     * Admin: delete hearing.
     */
    public function deleteHearing(int $hearing)
    {
        $caseId = DB::table('case_hearings')->where('id', $hearing)->value('case_id');
        DB::table('case_hearings')->where('id', $hearing)->delete();
        if ($caseId) {
            $this->logCaseAudit($caseId, 'hearing_deleted', ['hearing_id' => $hearing]);
        }
        return back()->with('success', 'Hearing removed.');
    }

    /**
     * Uploaded Files (Evidence merged here) — store.
     */
    public function storeFile(Request $request, int $case)
    {
        $c = DB::table('court_cases')->where('id', $case)->first();
        abort_if(!$c, 404);

        $data = $request->validate([
            'label' => ['nullable', 'string', 'max:255'],
            'file'  => ['required', 'file', 'max:10240'], // 10MB
        ]);

        $stored = $request->file('file')->store('case_files', 'public');

        DB::table('case_files')->insert([
            'case_id'                   => $case,
            'label'                     => $data['label'] ?? null,
            'path'                      => $stored,
            'mime'                      => $request->file('file')->getClientMimeType(),
            'size'                      => $request->file('file')->getSize(),
            'uploaded_by_user_id'       => Auth::id(),
            'uploaded_by_applicant_id'  => null,
            'created_at'                => now(),
            'updated_at'                => now(),
        ]);

        $this->logCaseAudit($case, 'file_uploaded', [
            'label' => $data['label'] ?? null,
            'path'  => $stored,
        ]);

        return back()->with('success', 'File uploaded.');
    }

    /**
     * Uploaded Files — delete.
     */
    public function deleteFile(int $case, int $file)
    {
        $row = DB::table('case_files')->where('id', $file)->where('case_id', $case)->first();
        abort_if(!$row, 404);

        if (!empty($row->path)) {
            Storage::disk('public')->delete($row->path);
        }
        DB::table('case_files')->where('id', $file)->delete();

        $this->logCaseAudit($row->case_id, 'file_deleted', [
            'file_id' => $file,
            'label'   => $row->label ?? null,
        ]);

        return back()->with('success', 'File removed.');
    }

    /**
     * View a submitted document (case_evidences).
     */
    public function viewDocument(int $caseId, int $docId)
    {
        $this->authorizeView();

        $doc = DB::table('case_evidences')
            ->where('id', $docId)
            ->where('case_id', $caseId)
            ->first();

        abort_if(!$doc, 404, 'Document not found.');

        $filePath = $doc->file_path ?? $doc->path ?? null;
        abort_if(!$filePath, 404, 'Document file missing.');

        $disk = Storage::disk('public');
        abort_if(!$disk->exists($filePath), 404, 'Stored file missing.');

        $downloadName = ($doc->title ?? basename($filePath)) ?: basename($filePath);
        $mime = $doc->mime ?? $disk->mimeType($filePath) ?? 'application/octet-stream';

        return $disk->response($filePath, $downloadName, [
            'Content-Type'        => $mime,
            'Content-Disposition' => 'inline; filename="' . basename($downloadName) . '"',
        ]);
    }

    /**
     * Witnesses — create.
     */
    public function storeWitness(Request $request, int $case)
    {
        $c = DB::table('court_cases')->where('id', $case)->first();
        abort_if(!$c, 404);

        $data = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'phone'     => ['nullable', 'string', 'max:60'],
            'email'     => ['nullable', 'email', 'max:150'],
            'address'   => ['nullable', 'string', 'max:255'],
            'notes'     => ['nullable', 'string', 'max:2000'],
        ]);

        DB::table('case_witnesses')->insert([
            'case_id'            => $case,
            'full_name'          => $data['full_name'],
            'phone'              => $data['phone'] ?? null,
            'email'              => $data['email'] ?? null,
            'address'            => $data['address'] ?? null,
            'notes'              => $data['notes'] ?? null,
            'created_by_user_id' => Auth::id(),
            'updated_by_user_id' => Auth::id(),
            'created_at'         => now(),
            'updated_at'         => now(),
        ]);

        return back()->with('success', 'Witness added.');
    }

    /**
     * Witnesses — update.
     */
    public function updateWitness(Request $request, int $case, int $witness)
    {
        $exists = DB::table('case_witnesses')
            ->where('id', $witness)
            ->where('case_id', $case)
            ->exists();
        abort_if(!$exists, 404);

        $data = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'phone'     => ['nullable', 'string', 'max:60'],
            'email'     => ['nullable', 'email', 'max:150'],
            'address'   => ['nullable', 'string', 'max:255'],
            'notes'     => ['nullable', 'string', 'max:2000'],
        ]);

        DB::table('case_witnesses')->where('id', $witness)->update([
            'full_name'          => $data['full_name'],
            'phone'              => $data['phone'] ?? null,
            'email'              => $data['email'] ?? null,
            'address'            => $data['address'] ?? null,
            'notes'              => $data['notes'] ?? null,
            'updated_by_user_id' => Auth::id(),
            'updated_at'         => now(),
        ]);

        return back()->with('success', 'Witness updated.');
    }

    /**
     * Witnesses — delete.
     */
    public function deleteWitness(int $case, int $witness)
    {
        DB::table('case_witnesses')
            ->where('id', $witness)
            ->where('case_id', $case)
            ->delete();

        return back()->with('success', 'Witness deleted.');
    }

    /**
     * Gate for admin view (uses your helper).
     */
    private function authorizeView(): void
    {
        if (!userHasPermission('cases.view')) {
            abort(403, 'You do not have permission: cases.view');
        }
    }

    private function teamLeaderAssignmentIds(?\App\Models\User $user): array
    {
        if (!$user) {
            return [];
        }

        $isLeader = $user->hasPermission('cases.assign.member');
        $hasAdminAssign = $user->hasPermission('cases.assign.team');

        if (!$isLeader || $hasAdminAssign) {
            return [];
        }

        $leaderTeam = Team::with(['users' => fn ($q) => $q->where('status', 'active')->orderBy('name')])
            ->where('team_leader_id', $user->id)
            ->first();

        $ids = collect([$user->id]);

        if ($leaderTeam) {
            $ids = $ids->merge($leaderTeam->users->pluck('id'));
        }

        return $ids->filter()->unique()->values()->all();
    }

    /**
     * Send a review decision note to the applicant (message + optional email).
     */
    private function notifyApplicantOfReview(object $case, string $reviewStatus, ?string $note): void
    {
        if (empty($case->applicant_id)) {
            return;
        }

        $decisionText = match ($reviewStatus) {
            'accepted' => 'accepted',
            'returned' => 'returned for correction',
            'rejected' => 'rejected',
            default    => $reviewStatus,
        };

        $body = "Your case {$case->case_number} has been {$decisionText}.";
        if ($note !== '') {
            $body .= "\n\nNotes from reviewer:\n{$note}";
        }

        DB::table('case_messages')->insert([
            'case_id'             => $case->id,
            'sender_user_id'      => Auth::id(),
            'sender_applicant_id' => null,
            'body'                => $body,
            'created_at'          => now(),
            'updated_at'          => now(),
        ]);

        try {
            $to = DB::table('applicants')->where('id', $case->applicant_id)->value('email');
            if ($to) {
                $prefs = DB::table('applicant_notification_prefs')->where('applicant_id', $case->applicant_id)->first();
                $wants = !$prefs || ($prefs->email_message ?? true);

                if ($wants) {
                    $preview = mb_strimwidth($body, 0, 180, '...');
                    Mail::to($to)->send(new \App\Mail\CaseMessageMail($case, 'Court Staff', $preview));
                }
            } else {
                Log::info('Review note email skipped (no applicant email)', ['case_id' => $case->id]);
            }
        } catch (\Throwable $e) {
            Log::error('Failed sending review decision email', ['case_id' => $case->id, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Record a case audit trail entry.
     */
    private function logCaseAudit(int $caseId, string $action, array $meta = []): void
    {
        try {
            DB::table('case_audits')->insert([
                'case_id'    => $caseId,
                'action'     => $action,
                'actor_type' => Auth::check() ? 'user' : 'system',
                'actor_id'   => Auth::id(),
                'meta'       => empty($meta) ? null : json_encode($meta),
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('Failed to log case audit', ['case_id' => $caseId, 'action' => $action, 'error' => $e->getMessage()]);
        }
    }
}
