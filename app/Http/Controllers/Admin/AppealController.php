<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AppealController extends Controller
{
    public function index(Request $request)
    {
        $appeals = DB::table('appeals as a')
            ->join('court_cases as c', 'c.id', '=', 'a.court_case_id')
            ->leftJoin('users as u', 'u.id', '=', 'a.decided_by_user_id')
            ->select('a.*', 'c.case_number', 'u.name as decided_by')
            ->when($request->filled('status'), fn($q) => $q->where('a.status', $request->status))
            ->orderByDesc('a.created_at')
            ->paginate(15)
            ->withQueryString();

        return view('admin.appeals.index', ['appeals' => $appeals]);
    }

    public function create()
    {
        $cases = DB::table('court_cases')
            ->orderByDesc('created_at')
            ->limit(50)
            ->get(['id', 'case_number', 'title']);

        return view('admin.appeals.create', compact('cases'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'court_case_id' => ['required', 'integer', 'exists:court_cases,id'],
            'title'         => ['required', 'string', 'max:255'],
            'grounds'       => ['nullable', 'string', 'max:10000'],
        ]);

        return DB::transaction(function () use ($data) {
            // 1) Insert draft to get the ID
            $appealId = DB::table('appeals')->insertGetId([
                'court_case_id'         => $data['court_case_id'],
                'applicant_id'          => null,
                'submitted_by_user_id'  => auth()->id(),
                'appeal_number'         => 'TMP-' . uniqid(), // temporary to satisfy NOT NULL+UNIQUE
                'title'                 => $data['title'],
                'grounds'               => $data['grounds'] ?? null,
                'status'                => 'draft',
                'created_at'            => now(),
                'updated_at'            => now(),
            ]);

            // 2) Generate final number using the ID
            $finalNumber = 'APL-' . now()->format('Y') . '-' . str_pad((string)$appealId, 4, '0', STR_PAD_LEFT);

            DB::table('appeals')->where('id', $appealId)->update([
                'appeal_number' => $finalNumber,
                'updated_at'    => now(),
            ]);

            return redirect()
                ->route('appeals.show', $appealId)
                ->with('success', 'Appeal created.');
        });
    }

    public function show($appealId)
    {
        $appeal = DB::table('appeals as a')
            ->join('court_cases as c', 'c.id', '=', 'a.court_case_id')
            ->leftJoin('users as du', 'du.id', '=', 'a.decided_by_user_id')
            ->select('a.*', 'c.case_number', 'c.title as case_title', 'du.name as decided_by')
            ->where('a.id', $appealId)
            ->first();

        abort_if(!$appeal, 404);

        $docs = DB::table('appeal_documents')
            ->where('appeal_id', $appealId)
            ->orderByDesc('created_at')
            ->get();

        return view('admin.appeals.show', compact('appeal', 'docs'));
    }

    public function edit($appealId)
    {
        $appeal = DB::table('appeals')->where('id', $appealId)->first();
        abort_if(!$appeal, 404);

        if (!in_array($appeal->status, ['draft', 'submitted'], true)) {
            return redirect()->route('appeals.show', $appealId)
                ->with('error', 'Cannot edit at this stage.');
        }

        return view('admin.appeals.edit', compact('appeal'));
    }

    public function update(Request $request, $appealId)
    {
        $appeal = DB::table('appeals')->where('id', $appealId)->first();
        abort_if(!$appeal, 404);

        $data = $request->validate([
            'title'   => ['required', 'string', 'max:255'],
            'grounds' => ['nullable', 'string', 'max:10000'],
        ]);

        DB::table('appeals')->where('id', $appealId)->update([
            'title'      => $data['title'],
            'grounds'    => $data['grounds'] ?? null,
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Updated.');
    }

    public function submit($appealId)
    {
        $appeal = DB::table('appeals')->where('id', $appealId)->first();
        abort_if(!$appeal, 404);

        if ($appeal->status !== 'draft') {
            return back()->with('error', 'Only drafts can be submitted.');
        }

        DB::table('appeals')->where('id', $appealId)->update([
            'status'       => 'submitted',
            'submitted_at' => now(),
            'updated_at'   => now(),
        ]);

        return back()->with('success', 'Appeal submitted.');
    }

    public function decide(Request $request, $appealId)
    {
        $appeal = DB::table('appeals')->where('id', $appealId)->first();
        abort_if(!$appeal, 404);

        $data = $request->validate([
            'decision'        => ['required', 'in:approved,rejected,closed'],
            'decision_notes'  => ['nullable', 'string', 'max:5000'],
        ]);

        if (!in_array($appeal->status, ['submitted', 'under_review'], true)) {
            return back()->with('error', 'Not in a decidable state.');
        }

        DB::table('appeals')->where('id', $appealId)->update([
            'status'             => $data['decision'],
            'decided_by_user_id' => auth()->id(),
            'decided_at'         => now(),
            'decision_notes'     => $data['decision_notes'] ?? null,
            'updated_at'         => now(),
        ]);

        return back()->with('success', 'Decision recorded.');
    }

    public function uploadDoc(Request $request, $appealId)
    {
        $appeal = DB::table('appeals')->where('id', $appealId)->first();
        abort_if(!$appeal, 404);

        $data = $request->validate([
            'label' => ['nullable', 'string', 'max:255'],
            'file'  => ['required', 'file', 'mimes:pdf,doc,docx,jpg,jpeg,png,webp', 'max:5120'], // 5MB
        ]);

        $file = $request->file('file');
        $path = $file->store('appeals', 'private');

        DB::table('appeal_documents')->insert([
            'appeal_id'  => $appealId,
            'label'      => $data['label'] ?? null,
            'path'       => $path,
            'mime'       => $file->getClientMimeType(),
            'size'       => $file->getSize(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Document uploaded.');
    }

    public function deleteDoc($appealId, $docId)
    {
        $doc = DB::table('appeal_documents')
            ->where('id', $docId)
            ->where('appeal_id', $appealId)
            ->first();

        abort_if(!$doc, 404);

        if (!empty($doc->path)) {
            Storage::disk('private')->delete($doc->path);
            Storage::disk('public')->delete($doc->path); // legacy cleanup
        }

        DB::table('appeal_documents')->where('id', $docId)->delete();

        return back()->with('success', 'Document removed.');
    }
}
