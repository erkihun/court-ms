<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BenchNote;
use App\Models\CourtCase;
use Illuminate\Http\Request;
use Mews\Purifier\Facades\Purifier;

class BenchNoteController extends Controller
{
    public function index(Request $request)
    {
        $caseId = $request->integer('case_id') ?: null;

        $benchNotes = BenchNote::query()
            ->with(['case:id,case_number,title', 'user:id,name'])
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

        return view('admin.bench-notes.create', compact('cases', 'caseId'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'case_id' => ['required', 'integer', 'exists:court_cases,id'],
            'title'   => ['required', 'string', 'max:255'],
            'note'    => ['required', 'string', 'max:20000'],
        ]);

        $cleanNote = $this->sanitizeNote($data['note']);

        $benchNote = BenchNote::create([
            'case_id'   => $data['case_id'],
            'user_id'   => $request->user()->id,
            'title'     => trim($data['title']),
            'note'      => $cleanNote,
            'hearing_id'=> null,
        ]);

        return redirect()
            ->route('bench-notes.index', ['case_id' => $benchNote->case_id])
            ->with('success', 'Bench note created.');
    }

    public function show(BenchNote $benchNote)
    {
        return redirect()->route('bench-notes.index', ['case_id' => $benchNote->case_id]);
    }

    public function edit(BenchNote $benchNote)
    {
        $benchNote->load(['case:id,case_number,title']);

        $cases = CourtCase::query()
            ->select('id', 'case_number', 'title')
            ->orderBy('case_number')
            ->limit(300)
            ->get();

        return view('admin.bench-notes.edit', compact('benchNote', 'cases'));
    }

    public function update(Request $request, BenchNote $benchNote)
    {
        $data = $request->validate([
            'case_id' => ['required', 'integer', 'exists:court_cases,id'],
            'title'   => ['required', 'string', 'max:255'],
            'note'    => ['required', 'string', 'max:20000'],
        ]);

        $cleanNote = $this->sanitizeNote($data['note']);

        $benchNote->update([
            'case_id' => $data['case_id'],
            'title'   => trim($data['title']),
            'note'    => $cleanNote,
        ]);

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
}
