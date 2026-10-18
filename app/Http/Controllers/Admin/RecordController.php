<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CourtCase;
use App\Models\Team;
use App\Models\User;
use App\Support\EthiopianDate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class RecordController extends Controller
{
    private const CASE_STATUSES = ['pending', 'active', 'adjourned', 'dismissed', 'closed'];

    public function index(Request $request): Response
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
                'reviewer.name as reviewer_name',
                DB::raw('(SELECT COUNT(*) FROM decisions d WHERE d.court_case_id = c.id) as decisions_count')
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

        $cases->through(function (object $case) use ($isReviewer): array {
            $closedOrDecided = ($case->decisions_count ?? 0) > 0 || in_array($case->status ?? '', ['closed', 'dismissed'], true);

            return [
                'id' => (int) $case->id,
                'caseNumber' => (string) $case->case_number,
                'title' => Str::limit((string) $case->title, 48),
                'applicant' => $case->applicant_name ?: null,
                'caseType' => $case->case_type ?: null,
                'team' => $case->team_name ?: null,
                'assignee' => $case->assignee_name ?: null,
                'status' => (string) $case->status,
                'filed' => EthiopianDate::format($case->filing_date),
                'reviewStatus' => $isReviewer ? (string) ($case->review_status ?? 'accepted') : 'accepted',
                'reviewer' => $case->reviewer_name ?: null,
                'canAppealPdf' => $closedOrDecided,
            ];
        });

        return Inertia::render('Admin/Records/Index', [
            'cases' => $cases,
            'filters' => [
                'q' => $q,
                'status' => $status,
                'case_type_id' => $caseTypeId ? (string) $caseTypeId : '',
                'assignee_id' => $assigneeId ? (string) $assigneeId : '',
                'from' => $from?->format('Y-m-d') ?? '',
                'to' => $to?->format('Y-m-d') ?? '',
            ],
            'types' => DB::table('case_types')->orderBy('name')->get(['id', 'name'])
                ->map(fn (object $type): array => ['id' => (int) $type->id, 'name' => (string) $type->name])->values(),
            'users' => User::query()->where('status', 'active')->orderBy('name')->get(['id', 'name'])
                ->map(fn (User $user): array => ['id' => (int) $user->id, 'name' => (string) $user->name])->values(),
            'statuses' => self::CASE_STATUSES,
        ]);
    }

    public function show(CourtCase $case): Response
    {
        $this->authorizeRecordAccess($case);

        return Inertia::render('Admin/Records/Show', [
            'record' => $this->presentRecord($case, $this->recordData($case, withEvidenceEmbed: false)),
        ]);
    }

    public function pdf(CourtCase $case)
    {
        $this->authorizeRecordAccess($case);
        $data = $this->recordData($case);
        $safeCase = Str::of($case->case_number ?? $case->id)->replace(['/', '\\'], '-')->slug('-');
        $data['pdfFilename'] = 'case-record-' . ($safeCase ?: $case->id) . '.pdf';

        return view('admin.recordes.record-pdf', $data);
    }

    /**
     * Server-side, consolidated "appeal record" PDF — the entire court process
     * from start to finish, for a party appealing to a higher court.
     */
    public function appealPdf(CourtCase $case)
    {
        $this->authorizeRecordAccess($case);
        $data = $this->recordData($case);
        $data['generatedAt'] = now();

        $safeCase = Str::of($case->case_number ?? $case->id)->replace(['/', '\\'], '-')->slug('-');
        $filename = 'appeal-record-' . ($safeCase ?: $case->id) . '.pdf';

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.appeal-record', $data)
            ->setPaper('a4')
            ->setOption('isRemoteEnabled', true);

        return $pdf->download($filename);
    }

    private function recordData(CourtCase $case, bool $withEvidenceEmbed = true): array
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

        // Only the PDF embeds the first evidence file; reading it is wasted work for the page.
        $firstEvidenceEmbed = null;
        foreach ($withEvidenceEmbed ? $evidences : [] as $ev) {
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

    /**
     * The record shows the whole case file, so it follows the case page's rules: the
     * case policy (unaccepted cases only for reviewers and the assignee) plus the
     * team scope that limits the record list.
     */
    private function authorizeRecordAccess(CourtCase $case): void
    {
        $user = Auth::user();
        abort_unless($user instanceof User && Gate::forUser($user)->allows('view', $case), 403);

        $memberScopeIds = $this->teamLeaderAssignmentIds($user);
        if (!empty($memberScopeIds)) {
            $leaderTeamId = Team::where('team_leader_id', $user->id)->value('id');
            $allowed = in_array((int) $case->assigned_user_id, array_map('intval', $memberScopeIds), true)
                || ($leaderTeamId && (int) $case->assigned_team_id === (int) $leaderTeamId);
            abort_unless($allowed, 403);

            return;
        }

        $isTeamMember = DB::table('team_user')->where('user_id', $user->id)->exists();
        if ($isTeamMember && !$user->hasPermission('cases.assign.member') && !$user->hasPermission('cases.assign.team')) {
            $assignedTo = (int) ($case->assigned_member_user_id ?? $case->assigned_user_id ?? 0);
            abort_unless($assignedTo === (int) $user->id, 403);
        }
    }

    /** @return array<string, mixed> */
    private function presentRecord(CourtCase $case, array $data): array
    {
        $html = fn (?string $value): ?string => filled($value) ? clean((string) $value, 'cases') : null;
        $when = fn ($value): string => $value ? EthiopianDate::smartFormat($value, true, '') : '';
        $caseFileUrl = fn (object $file): ?string => Route::has('cases.files.download') ? route('cases.files.download', [$case->id, $file->id]) : null;

        return [
            'id' => (int) $case->id,
            'caseNumber' => $case->case_number,
            'title' => $case->title,
            'status' => (string) $case->status,
            'caseType' => $case->caseType?->name,
            'filed' => EthiopianDate::smartFormat($case->filing_date, false, '—'),
            'firstHearing' => $case->first_hearing_date ? EthiopianDate::smartFormat($case->first_hearing_date, false, '—') : null,
            'generated' => EthiopianDate::smartFormat(now(), true, ''),
            'descriptionHtml' => $html($case->description),
            'reliefHtml' => $html($case->relief_requested),
            'applicant' => [
                'name' => $case->applicant?->full_name,
                'email' => $case->applicant?->email,
                'phone' => $case->applicant?->phone,
            ],
            'respondent' => [
                'name' => $case->respondent_name,
                'address' => $case->respondent_address,
            ],
            'pdfUrl' => route('recordes.pdf', $case->id),
            // Same rule as the record list: only once there is a decision or the case is closed.
            'appealPdfUrl' => $data['decision'] || in_array($case->status, ['closed', 'dismissed'], true) ? route('recordes.appeal-pdf', $case->id) : null,
            'files' => $data['files']->map(fn (object $file): array => [
                'id' => (int) $file->id,
                'label' => $file->label ?: null,
                'created' => $when($file->created_at),
                'url' => $caseFileUrl($file),
            ])->values(),
            'evidences' => $data['evidences']->map(fn (object $evidence): array => [
                'id' => (int) $evidence->id,
                'title' => $evidence->title ?: null,
                'description' => $evidence->description ?: null,
                'created' => $when($evidence->created_at),
            ])->values(),
            'witnesses' => $data['witnesses']->map(fn (object $witness): array => [
                'id' => (int) $witness->id,
                'name' => $witness->full_name,
                'phone' => $witness->phone,
                'email' => $witness->email,
                'address' => $witness->address,
            ])->values(),
            'responses' => $data['respondentResponses']->map(fn (object $response): array => [
                'id' => (int) $response->id,
                'title' => $response->title ?: null,
                'number' => $response->response_number ?? null,
                'excerpt' => filled($response->description) ? Str::limit(strip_tags((string) $response->description), 260) : null,
                'created' => $when($response->created_at),
                'downloadUrl' => filled($response->pdf_path) ? $response->download_url : null,
            ])->values(),
            'letters' => $data['letters']->map(fn (object $letter): array => [
                'id' => (int) $letter->id,
                'subject' => $letter->subject ?: null,
                'reference' => $letter->reference_number ?: null,
                'author' => $letter->author_name,
                'created' => $when($letter->created_at),
                'bodyHtml' => $html($letter->body),
            ])->values(),
            'hearings' => $data['hearings']->map(fn (object $hearing): array => [
                'id' => (int) $hearing->id,
                'when' => $hearing->hearing_at ? EthiopianDate::smartFormat($hearing->hearing_at, true, '', 'h:i A', 'F j, Y') : null,
                'location' => $hearing->location ?: null,
                'type' => $hearing->type ?: null,
                'notes' => filled($hearing->notes) ? strip_tags((string) $hearing->notes) : null,
            ])->values(),
            'benchNotes' => $data['benchNotes']->map(fn (object $note): array => [
                'id' => (int) $note->id,
                'created' => $when($note->created_at),
                'author' => $note->author,
                'html' => $html($note->note),
            ])->values(),
            'decision' => $data['decision'] ? [
                'name' => $data['decision']->name ?: null,
                'created' => $when($data['decision']->created_at),
                'html' => $html($data['decision']->decision_content),
            ] : null,
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
