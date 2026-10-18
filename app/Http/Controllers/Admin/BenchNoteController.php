<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BenchNote;
use App\Models\CourtCase;
use App\Models\User;
use App\Support\EthiopianDate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;
use Inertia\Response;
use Mews\Purifier\Facades\Purifier;

/**
 * Routes are gated by perm:bench-notes.manage; each action additionally needs
 * its own bench-notes.{view,create,update,delete} permission, which the old
 * pages only enforced by hiding buttons.
 */
class BenchNoteController extends Controller
{
    private const JUDGE_RELATIONS = [
        'judgeOne:id,name,signature_path',
        'judgeTwo:id,name,signature_path',
        'judgeThree:id,name,signature_path',
    ];

    public function index(Request $request): Response
    {
        $this->authorizeAction('view');

        $caseId = $request->integer('case_id') ?: null;

        $benchNotes = BenchNote::query()
            ->with(['case:id,case_number,title', 'user:id,name', ...self::JUDGE_RELATIONS])
            ->when($caseId, fn ($q) => $q->where('case_id', $caseId))
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString()
            ->through(fn (BenchNote $note): array => $this->present($note));

        return Inertia::render('Admin/BenchNotes/Index', [
            'notes' => $benchNotes,
            'cases' => $this->caseOptions(),
            'filters' => ['case_id' => $caseId ? (string) $caseId : ''],
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorizeAction('create');

        $caseId = $request->integer('case_id') ?: null;

        return Inertia::render('Admin/BenchNotes/Form', [
            'note' => null,
            'defaultCaseId' => $caseId,
            ...$this->formOptions(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeAction('create');

        $data = $request->validate([
            'case_id' => ['required', 'integer', 'exists:court_cases,id'],
            'title' => ['required', 'string', 'max:255'],
            'note_date' => ['nullable', 'date'],
            'note' => ['required', 'string'],
            'judges' => ['nullable', 'array', 'size:3'],
            'judges.*.admin_user_id' => ['nullable', 'integer', 'exists:users,id'],
        ]);

        $cleanNote = $this->sanitizeNote($data['note']);

        $panelJudges = $this->normalizePanelJudges($data['judges'] ?? []);

        $payload = [
            'case_id' => $data['case_id'],
            'user_id' => $request->user()->id,
            'note' => $cleanNote,
            'hearing_id' => null,
        ];
        if (Schema::hasColumn('bench_notes', 'title')) {
            $payload['title'] = trim($data['title']);
        }
        if (Schema::hasColumn('bench_notes', 'note_date')) {
            $payload['note_date'] = $data['note_date'] ?? null;
        }
        if (Schema::hasColumn('bench_notes', 'judge_one_id')) {
            $payload['judge_one_id'] = $panelJudges[0] ?? null;
        }
        if (Schema::hasColumn('bench_notes', 'judge_two_id')) {
            $payload['judge_two_id'] = $panelJudges[1] ?? null;
        }
        if (Schema::hasColumn('bench_notes', 'judge_three_id')) {
            $payload['judge_three_id'] = $panelJudges[2] ?? null;
        }

        $benchNote = BenchNote::create($payload);

        return redirect()
            ->route('bench-notes.index', ['case_id' => $benchNote->case_id])
            ->with('success', [
                'key' => 'messages.success.created',
                'replace' => ['resource' => __('messages.resources.bench_note')],
            ]);
    }

    public function show(BenchNote $benchNote): Response
    {
        $this->authorizeAction('view');

        $benchNote->load(['case:id,case_number,title', 'user:id,name', ...self::JUDGE_RELATIONS]);

        return Inertia::render('Admin/BenchNotes/Show', ['note' => $this->present($benchNote)]);
    }

    public function edit(BenchNote $benchNote): Response
    {
        $this->authorizeAction('update');
        $this->authorizeOwnership($benchNote);

        $benchNote->load(['case:id,case_number,title', 'user:id,name', ...self::JUDGE_RELATIONS]);

        return Inertia::render('Admin/BenchNotes/Form', [
            'note' => [
                'id' => $benchNote->id,
                'caseId' => (int) $benchNote->case_id,
                'title' => (string) $benchNote->title,
                'noteDate' => $benchNote->note_date?->format('Y-m-d') ?? '',
                'note' => (string) $benchNote->note,
                'judgeIds' => [$benchNote->judge_one_id, $benchNote->judge_two_id, $benchNote->judge_three_id],
                'firstJudgeName' => $benchNote->judgeOne?->name,
                'author' => $benchNote->user?->name,
                'created' => EthiopianDate::format($benchNote->created_at, withTime: true),
                'updated' => $this->wasEdited($benchNote) ? EthiopianDate::format($benchNote->updated_at, withTime: true) : null,
            ],
            'defaultCaseId' => null,
            ...$this->formOptions(),
        ]);
    }

    public function update(Request $request, BenchNote $benchNote): RedirectResponse
    {
        $this->authorizeAction('update');
        $this->authorizeOwnership($benchNote);

        $data = $request->validate([
            'case_id' => ['required', 'integer', 'exists:court_cases,id'],
            'title' => ['required', 'string', 'max:255'],
            'note_date' => ['nullable', 'date'],
            'note' => ['required', 'string'],
            'judges' => ['nullable', 'array', 'size:3'],
            'judges.*.admin_user_id' => ['nullable', 'integer', 'exists:users,id'],
        ]);

        $cleanNote = $this->sanitizeNote($data['note']);

        $panelJudges = $request->has('judges')
            ? $this->normalizePanelJudges($data['judges'] ?? [])
            : null;

        $updatePayload = [
            'case_id' => $data['case_id'],
            'note' => $cleanNote,
        ];
        if (Schema::hasColumn('bench_notes', 'title')) {
            $updatePayload['title'] = trim($data['title']);
        }
        if (Schema::hasColumn('bench_notes', 'note_date')) {
            $updatePayload['note_date'] = $data['note_date'] ?? null;
        }

        if ($panelJudges !== null) {
            if (Schema::hasColumn('bench_notes', 'judge_one_id')) {
                $updatePayload['judge_one_id'] = $panelJudges[0] ?? null;
            }
            if (Schema::hasColumn('bench_notes', 'judge_two_id')) {
                $updatePayload['judge_two_id'] = $panelJudges[1] ?? null;
            }
            if (Schema::hasColumn('bench_notes', 'judge_three_id')) {
                $updatePayload['judge_three_id'] = $panelJudges[2] ?? null;
            }
        }

        $benchNote->update($updatePayload);

        return redirect()
            ->route('bench-notes.index', ['case_id' => $benchNote->case_id])
            ->with('success', [
                'key' => 'messages.success.updated',
                'replace' => ['resource' => __('messages.resources.bench_note')],
            ]);
    }

    public function destroy(BenchNote $benchNote): RedirectResponse
    {
        $this->authorizeAction('delete');
        $this->authorizeOwnership($benchNote);

        $caseId = $benchNote->case_id;
        $benchNote->delete();

        return redirect()
            ->route('bench-notes.index', ['case_id' => $caseId])
            ->with('success', [
                'key' => 'messages.success.deleted',
                'replace' => ['resource' => __('messages.resources.bench_note')],
            ]);
    }

    private function authorizeAction(string $action): void
    {
        abort_unless(userHasPermission('bench-notes.'.$action), 403);
    }

    /** A judge's notes are their own: only the author or an administrator may change or delete one. */
    private function authorizeOwnership(BenchNote $note): void
    {
        abort_unless($this->mayModify($note), 403);
    }

    private function mayModify(BenchNote $note): bool
    {
        $user = Auth::user();

        return $user !== null && ((int) $note->user_id === (int) $user->id || $user->hasRole('admin'));
    }

    /** @return array<string, mixed> */
    private function present(BenchNote $note): array
    {
        return [
            'id' => $note->id,
            'title' => (string) $note->title,
            'caseId' => $note->case_id ? (int) $note->case_id : null,
            'caseNumber' => $note->case?->case_number,
            'caseTitle' => $note->case?->title,
            'author' => $note->user?->name,
            'mine' => $this->mayModify($note),
            'created' => EthiopianDate::format($note->created_at),
            'createdTime' => EthiopianDate::formatTime($note->created_at, timeFormat: 'g:i A'),
            'updated' => $this->wasEdited($note) ? EthiopianDate::format($note->updated_at, withTime: true) : null,
            'noteDate' => $note->note_date ? EthiopianDate::format($note->note_date) : null,
            'judges' => [$note->judgeOne?->name, $note->judgeTwo?->name, $note->judgeThree?->name],
            'signatureUrl' => $note->judgeOne?->signature_url,
            // Purified on save; cleaned again here because older rows predate that.
            'noteHtml' => $note->note ? clean($note->note, 'default') : '',
        ];
    }

    private function wasEdited(BenchNote $note): bool
    {
        return $note->updated_at !== null && ! $note->updated_at->equalTo($note->created_at);
    }

    /** @return Collection<int, array{id: int, label: string}> */
    private function caseOptions(): Collection
    {
        return CourtCase::query()
            ->select('id', 'case_number', 'title')
            ->orderBy('case_number')
            ->limit(300)
            ->get()
            ->map(fn (CourtCase $case): array => ['id' => $case->id, 'label' => $case->case_number.' — '.$case->title])
            ->values();
    }

    /** @return array<string, mixed> */
    private function formOptions(): array
    {
        $user = auth()->user();

        return [
            'cases' => $this->caseOptions(),
            'judges' => $this->loadJudgeUsers()->map(fn (User $judge): array => ['id' => $judge->id, 'name' => $judge->name])->values(),
            'currentUser' => ['id' => (int) $user->id, 'name' => (string) $user->name],
        ];
    }

    private function sanitizeNote(?string $html): string
    {
        $s = (string) ($html ?? '');
        if ($s === '') {
            return '';
        }
        if (str_contains($s, '&lt;') || str_contains($s, '&gt;')) {
            $s = htmlspecialchars_decode($s, ENT_QUOTES);
        }

        return Purifier::clean($s, 'default');
    }

    private function loadJudgeUsers()
    {
        $user = auth()->user();
        $teamIds = $user ? $user->teams()->pluck('teams.id') : collect();

        $query = User::query()->orderBy('name');
        if ($teamIds->isNotEmpty()) {
            $query->whereHas('teams', fn ($q) => $q->whereIn('teams.id', $teamIds));
        }

        $judgeUsers = $query->get(['id', 'name']);
        if ($user && ! $judgeUsers->contains('id', $user->id)) {
            $judgeUsers->push($user);
        }

        return $judgeUsers->unique('id')->sortBy('name')->values();
    }

    private function normalizePanelJudges(array $panel): array
    {
        $ordered = collect($panel)->pad(3, [])->take(3)->values();

        return $ordered->map(function ($row) {
            $id = Arr::get($row, 'admin_user_id');
            if ($id === null || $id === '') {
                return null;
            }

            return (int) $id;
        })->all();
    }
}
