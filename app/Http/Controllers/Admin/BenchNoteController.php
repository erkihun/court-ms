<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BenchNote;
use App\Models\CourtCase;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Mews\Purifier\Facades\Purifier;

class BenchNoteController extends Controller
{
    public function index(Request $request)
    {
        $caseId = $request->integer('case_id') ?: null;

        $benchNotes = BenchNote::query()
            ->with([
                'case:id,case_number,title',
                'user:id,name',
                'judgeOne:id,name,signature_path',
                'judgeTwo:id,name,signature_path',
                'judgeThree:id,name,signature_path',
            ])
            ->when($caseId, fn($q) => $q->where('case_id', $caseId))
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        $cases = CourtCase::query()
            ->select('id', 'case_number', 'title')
            ->orderBy('case_number')
            ->limit(300)
            ->get();

        return view('admin.bench-notes.index', compact('benchNotes', 'cases', 'caseId'));
    }

    public function create(Request $request)
    {
        $caseId = $request->integer('case_id') ?: null;

        $cases = CourtCase::query()
            ->select('id', 'case_number', 'title')
            ->orderBy('case_number')
            ->limit(300)
            ->get();

        $judgeUsers = $this->loadJudgeUsers();

        return view('admin.bench-notes.create', compact('cases', 'caseId', 'judgeUsers'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'case_id' => ['required', 'integer', 'exists:court_cases,id'],
            'title'   => ['required', 'string', 'max:255'],
            'note'    => ['required', 'string', 'max:20000'],
            'judges' => ['nullable', 'array', 'size:3'],
            'judges.*.admin_user_id' => ['nullable', 'integer', 'exists:users,id'],
        ]);

        $cleanNote = $this->sanitizeNote($data['note']);

        $panelJudges = $this->normalizePanelJudges($data['judges'] ?? []);

        $benchNote = BenchNote::create([
            'case_id'      => $data['case_id'],
            'user_id'      => $request->user()->id,
            'title'        => trim($data['title']),
            'note'         => $cleanNote,
            'hearing_id'   => null,
            'judge_one_id' => $panelJudges[0] ?? null,
            'judge_two_id' => $panelJudges[1] ?? null,
            'judge_three_id' => $panelJudges[2] ?? null,
        ]);

        return redirect()
            ->route('bench-notes.index', ['case_id' => $benchNote->case_id])
            ->with('success', 'Bench note created.');
    }

    public function show(BenchNote $benchNote)
    {
        $benchNote->load([
            'case:id,case_number,title',
            'user:id,name',
            'judgeOne:id,name,signature_path',
            'judgeTwo:id,name,signature_path',
            'judgeThree:id,name,signature_path',
        ]);

        return view('admin.bench-notes.show', compact('benchNote'));
    }

    public function edit(BenchNote $benchNote)
    {
        $benchNote->load([
            'case:id,case_number,title',
            'judgeOne:id,name,signature_path',
            'judgeTwo:id,name,signature_path',
            'judgeThree:id,name,signature_path',
        ]);

        $cases = CourtCase::query()
            ->select('id', 'case_number', 'title')
            ->orderBy('case_number')
            ->limit(300)
            ->get();

        $judgeUsers = $this->loadJudgeUsers();

        return view('admin.bench-notes.edit', compact('benchNote', 'cases', 'judgeUsers'));
    }

    public function update(Request $request, BenchNote $benchNote)
    {
        $data = $request->validate([
            'case_id' => ['required', 'integer', 'exists:court_cases,id'],
            'title'   => ['required', 'string', 'max:255'],
            'note'    => ['required', 'string', 'max:20000'],
            'judges' => ['nullable', 'array', 'size:3'],
            'judges.*.admin_user_id' => ['nullable', 'integer', 'exists:users,id'],
        ]);

        $cleanNote = $this->sanitizeNote($data['note']);

        $panelJudges = $request->has('judges')
            ? $this->normalizePanelJudges($data['judges'] ?? [])
            : null;

        $updatePayload = [
            'case_id' => $data['case_id'],
            'title'   => trim($data['title']),
            'note'    => $cleanNote,
        ];

        if ($panelJudges !== null) {
            $updatePayload['judge_one_id'] = $panelJudges[0] ?? null;
            $updatePayload['judge_two_id'] = $panelJudges[1] ?? null;
            $updatePayload['judge_three_id'] = $panelJudges[2] ?? null;
        }

        $benchNote->update($updatePayload);

        return redirect()
            ->route('bench-notes.index', ['case_id' => $benchNote->case_id])
            ->with('success', 'Bench note updated.');
    }

    public function destroy(BenchNote $benchNote)
    {
        $caseId = $benchNote->case_id;
        $benchNote->delete();

        return redirect()
            ->route('bench-notes.index', ['case_id' => $caseId])
            ->with('success', 'Bench note deleted.');
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
            $query->whereHas('teams', fn($q) => $q->whereIn('teams.id', $teamIds));
        }

        $judgeUsers = $query->get(['id', 'name']);
        if ($user && !$judgeUsers->contains('id', $user->id)) {
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
