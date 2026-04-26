<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CourtCase;
use App\Models\Team;
use App\Models\User;
use Barryvdh\Snappy\Facades\SnappyPdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class RecordController extends Controller
{
    public function index(Request $request)
    {
        $q = trim($request->string('q')->toString());
        $status = $request->string('status')->toString();
        $caseTypeId = $request->integer('case_type_id');
        $assigneeId = $request->integer('assignee_id');
        $from = $request->date('from');
        $to = $request->date('to');

        $isReviewer = false;
        if (function_exists('userHasPermission')) {
            $isReviewer = (bool) userHasPermission('cases.review');
        } elseif (Auth::user()) {
            $isReviewer = (bool) Auth::user()->can('cases.review');
        }

        $teamNameSubquery = DB::table('teams as t')
            ->join('team_user as tu', 'tu.team_id', '=', 't.id')
            ->whereColumn('tu.user_id', 'ass.id')
            ->orderBy('t.name')
            ->limit(1)
            ->select('t.name');

        $builder = DB::table('court_cases as c')
            ->leftJoin('case_types as ct', 'ct.id', '=', 'c.case_type_id')
            ->leftJoin('applicants as ap', 'ap.id', '=', 'c.applicant_id')
            ->leftJoin('users as ass', 'ass.id', '=', 'c.assigned_user_id')
            ->leftJoin('users as reviewer', 'reviewer.id', '=', 'c.reviewed_by_user_id')
            ->select(
                'c.*',
                'ct.name as case_type',
                DB::raw("TRIM(CONCAT(COALESCE(ap.first_name,''),' ',COALESCE(ap.middle_name,''),' ',COALESCE(ap.last_name,''))) as applicant_name"),
                'ass.name as assignee_name',
                'reviewer.name as reviewer_name'
            )
            ->selectSub($teamNameSubquery, 'team_name');

        if ($q !== '') {
            $builder->where(function ($inner) use ($q) {
                $inner->where('c.case_number', 'like', "%{$q}%")
                    ->orWhere('c.title', 'like', "%{$q}%")
                    ->orWhere('ct.name', 'like', "%{$q}%");
            });
        }

        if ($status !== '') {
            $builder->where('c.status', $status);
        }
        if ($caseTypeId) {
            $builder->where('c.case_type_id', $caseTypeId);
        }
        if ($assigneeId) {
            $builder->where('c.assigned_user_id', $assigneeId);
        }
        if ($from) {
            $builder->whereDate('c.filing_date', '>=', $from->format('Y-m-d'));
        }
        if ($to) {
            $builder->whereDate('c.filing_date', '<=', $to->format('Y-m-d'));
        }

        $memberScopeIds = $this->teamLeaderAssignmentIds(Auth::user());
        if (!empty($memberScopeIds)) {
            $leaderTeamId = Team::where('team_leader_id', Auth::id())->value('id');
            $builder->where(function ($inner) use ($memberScopeIds, $leaderTeamId) {
                $inner->whereIn('c.assigned_user_id', $memberScopeIds);
                if ($leaderTeamId) {
                    $inner->orWhere('c.assigned_team_id', $leaderTeamId);
                }
            });
        } else {
            $userId = Auth::id();
            $isTeamMember = $userId && DB::table('team_user')->where('user_id', $userId)->exists();
            $isLeader = Auth::user()?->hasPermission('cases.assign.member') ?? false;
            $canAssignTeams = Auth::user()?->hasPermission('cases.assign.team') ?? false;

            if ($isTeamMember && !$isLeader && !$canAssignTeams) {
                $builder->where('c.assigned_member_user_id', $userId);
            }
        }

        if (!$isReviewer) {
            $builder->where('c.review_status', 'accepted');
        }

        $cases = $builder
            ->orderByRaw('COALESCE(c.created_at, c.filing_date) DESC')
            ->paginate(10)
            ->withQueryString();

        $types = DB::table('case_types')->orderBy('name')->get(['id', 'name']);
        $users = User::query()
            ->where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('admin.recordes.index', compact(
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

    public function show(CourtCase $case)
    {
        $data = $this->recordData($case);

        return view('admin.recordes.record', $data);
    }

    public function pdf(Request $request, CourtCase $case)
    {
        $data = $this->recordData($case);
        $safeCase = Str::of($case->case_number ?? $case->id)->replace(['/', '\\'], '-')->slug('-');
        $data['pdfFilename'] = 'case-record-' . ($safeCase ?: $case->id) . '.pdf';
        $data['serverPdfMode'] = false;

        if ($request->boolean('html_preview')) {
            return view('admin.recordes.record-pdf', $data);
        }

        $data['serverPdfMode'] = true;

        return SnappyPdf::loadView('admin.recordes.record-pdf', $data)
            ->setPaper('A4')
            ->setOptions([
                'margin-top' => 12,
                'margin-right' => 14,
                'margin-bottom' => 14,
                'margin-left' => 14,
                'encoding' => 'utf-8',
                'enable-local-file-access' => true,
                'enable-javascript' => true,
                'no-stop-slow-scripts' => true,
                'print-media-type' => true,
                'images' => true,
                'javascript-delay' => 1500,
                'window-status' => 'record-preview-ready',
                'load-error-handling' => 'ignore',
                'load-media-error-handling' => 'ignore',
            ])
            ->inline($data['pdfFilename']);
    }

    private function recordData(CourtCase $case): array
    {
        $letters = DB::table('letters as l')
            ->leftJoin('users as u', 'u.id', '=', 'l.user_id')
            ->select('l.*', 'u.name as author_name')
            ->where('l.case_number', $case->case_number)
            ->where('l.approval_status', 'approved')
            ->orderByDesc('l.created_at')
            ->get();

        $respondentResponses = DB::table('respondent_responses')
            ->where('case_number', $case->case_number)
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($resp) {
                $resp->pdf_embed = null;
                $resp->download_url = $resp->id ? route('respondent-responses.download', $resp->id) : null;
                return $resp;
            });

        $hearings = DB::table('case_hearings')
            ->where('case_id', $case->id)
            ->orderBy('hearing_at')
            ->get();

        $benchNotes = DB::table('bench_notes as b')
            ->leftJoin('users as u', 'u.id', '=', 'b.user_id')
            ->leftJoin('users as j1', 'j1.id', '=', 'b.judge_one_id')
            ->leftJoin('users as j2', 'j2.id', '=', 'b.judge_two_id')
            ->leftJoin('users as j3', 'j3.id', '=', 'b.judge_three_id')
            ->select(
                'b.*',
                'u.name as author',
                'j1.name as judge_one_name',
                'j1.signature_path as judge_one_signature',
                DB::raw('COALESCE(j1.position, "") as judge_one_title'),
                'j2.name as judge_two_name',
                'j2.signature_path as judge_two_signature',
                DB::raw('COALESCE(j2.position, "") as judge_two_title'),
                'j3.name as judge_three_name',
                'j3.signature_path as judge_three_signature',
                DB::raw('COALESCE(j3.position, "") as judge_three_title')
            )
            ->where('b.case_id', $case->id)
            ->orderByDesc('b.created_at')
            ->get();

        $decision = DB::table('decisions')
            ->where('court_case_id', $case->id)
            ->orderByDesc('decision_date')
            ->orderByDesc('id')
            ->first();

        $files = DB::table('case_files')
            ->where('case_id', $case->id)
            ->orderByDesc('created_at')
            ->get();

        $evidences = DB::table('case_evidences')
            ->where('case_id', $case->id)
            ->orderByDesc('created_at')
            ->get();

        $firstEvidenceEmbed = null;
        foreach ($evidences as $ev) {
            $path = $ev->file_path ?? $ev->path ?? null;
            if (!$path) {
                continue;
            }

            $size = (int) ($ev->size ?? 0);
            if ($size > 1500000) {
                continue;
            }

            $content = null;
            if (Storage::disk('private')->exists($path)) {
                $content = Storage::disk('private')->get($path);
            } elseif (Storage::disk('public')->exists($path)) {
                $content = Storage::disk('public')->get($path);
            } elseif (file_exists($path)) {
                $content = @file_get_contents($path);
            }

            if ($content) {
                $mime = $ev->mime ?? 'application/pdf';
                $firstEvidenceEmbed = [
                    'mime' => $mime,
                    'data' => base64_encode($content),
                    'title' => $ev->title ?? 'Document',
                ];
                break;
            }
        }

        $messages = DB::table('case_messages as m')
            ->leftJoin('users as u', 'u.id', '=', 'm.sender_user_id')
            ->leftJoin('applicants as a', 'a.id', '=', 'm.sender_applicant_id')
            ->select(
                'm.id',
                'm.body',
                'm.created_at',
                'm.sender_user_id',
                'm.sender_applicant_id',
                'u.name as user_name',
                DB::raw("TRIM(COALESCE(a.first_name,'') || ' ' || COALESCE(a.middle_name,'') || ' ' || COALESCE(a.last_name,'')) as applicant_name")
            )
            ->where('m.case_id', $case->id)
            ->orderByDesc('m.created_at')
            ->get();

        $statusLogs = DB::table('case_status_logs')
            ->where('case_id', $case->id)
            ->orderBy('created_at')
            ->get();

        $witnesses = DB::table('case_witnesses')
            ->where('case_id', $case->id)
            ->orderBy('id')
            ->get();

        $assignedUser = null;
        $assignedTeams = collect();
        if (!empty($case->assigned_user_id)) {
            $assignedUser = DB::table('users')->where('id', $case->assigned_user_id)->first();
            $assignedTeams = DB::table('team_user as tu')
                ->join('teams as t', 't.id', '=', 'tu.team_id')
                ->where('tu.user_id', $case->assigned_user_id)
                ->pluck('t.name');
        }

        $closedAt = optional(
            $statusLogs->filter(function ($log) {
                return in_array($log->to_status, ['closed', 'dismissed'], true);
            })->sortByDesc('created_at')->first()
        )->created_at;

        $case->loadMissing([
            'applicant:id,first_name,middle_name,last_name,email,phone',
            'caseType:id,name,prefix',
        ]);

        return [
            'case' => $case,
            'letters' => $letters,
            'respondentResponses' => $respondentResponses,
            'hearings' => $hearings,
            'benchNotes' => $benchNotes,
            'decision' => $decision,
            'files' => $files,
            'evidences' => $evidences,
            'messages' => $messages,
            'statusLogs' => $statusLogs,
            'witnesses' => $witnesses,
            'assignedUser' => $assignedUser,
            'assignedTeams' => $assignedTeams,
            'closedAt' => $closedAt,
            'firstEvidenceEmbed' => $firstEvidenceEmbed,
        ];
    }

    private function teamLeaderAssignmentIds(?User $user): array
    {
        if (!$user) {
            return [];
        }

        $isLeader = $user->hasPermission('cases.assign.member');
        $hasAdminAssign = $user->hasPermission('cases.assign.team');

        if (!$isLeader || $hasAdminAssign) {
            return [];
        }

        $leaderTeam = Team::with(['users' => fn ($query) => $query->where('status', 'active')->orderBy('name')])
            ->where('team_leader_id', $user->id)
            ->first();

        $ids = collect([$user->id]);

        if ($leaderTeam) {
            $ids = $ids->merge($leaderTeam->users->pluck('id'));
        }

        return $ids->filter()->unique()->values()->all();
    }
}
