<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Carbon;
use Inertia\Inertia;

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

        // The register (React, Court/Appeals/Index).
        return Inertia::render('Court/Appeals/Index', [
            'appeals' => $appeals->through(fn (object $row): array => [
                'id' => (int) $row->id,
                'appeal_number' => (string) $row->appeal_number,
                'case_number' => (string) $row->case_number,
                'title' => $row->title,
                'status' => (string) $row->status,
                'decided_by' => $row->decided_by,
                'created_at' => $row->created_at ? Carbon::parse($row->created_at)->toIso8601String() : null,
            ]),
            'filters' => ['status' => (string) $request->query('status', '')],
            'canCreate' => (bool) ($request->user()?->hasPermission('appeals.create') ?? false),
        ]);
    }

    public function create()
    {
        $cases = DB::table('court_cases')
            ->orderByDesc('created_at')
            ->limit(50)
            ->get(['id', 'case_number', 'title']);

        return Inertia::render('Court/Appeals/Form', ['appeal' => null, 'cases' => $cases]);
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
                ->with('success', [
                    'key' => 'messages.success.created',
                    'replace' => ['resource' => __('messages.resources.appeal')],
                ]);
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

        $user = auth()->user();
        $can = fn (string $permission): bool => (bool) ($user?->hasPermission($permission) ?? false);
        $iso = fn (mixed $value): ?string => $value ? Carbon::parse($value)->toIso8601String() : null;

        return Inertia::render('Court/Appeals/Show', [
            'appeal' => [
                'id' => (int) $appeal->id,
                'appeal_number' => (string) $appeal->appeal_number,
                'title' => (string) $appeal->title,
                'grounds' => $appeal->grounds,
                'status' => (string) $appeal->status,
                'case_id' => (int) $appeal->court_case_id,
                'case_number' => (string) $appeal->case_number,
                'case_title' => $appeal->case_title,
                'decided_by' => $appeal->decided_by,
                'decision_notes' => $appeal->decision_notes ?? null,
                'submitted_at' => $iso($appeal->submitted_at ?? null),
                'decided_at' => $iso($appeal->decided_at ?? null),
                'created_at' => $iso($appeal->created_at),
            ],
            'documents' => $docs->map(fn (object $doc): array => ['id' => (int) $doc->id, 'label' => $doc->label, 'created_at' => $iso($doc->created_at)])->values()->all(),
            'can' => ['edit' => $can('appeals.edit'), 'decide' => $can('appeals.decide'), 'upload' => $can('appeals.edit')],
        ]);
    }

    public function edit($appealId)
    {
        $appeal = DB::table('appeals')->where('id', $appealId)->first();
        abort_if(!$appeal, 404);

        if (!in_array($appeal->status, ['draft', 'submitted'], true)) {
            return redirect()->route('appeals.show', $appealId)
                ->with('error', 'messages.error.cannot_edit_stage');
        }

        return Inertia::render('Court/Appeals/Form', [
            'appeal' => ['id' => (int) $appeal->id, 'appeal_number' => (string) $appeal->appeal_number, 'title' => (string) $appeal->title, 'grounds' => $appeal->grounds],
            'cases' => [],
        ]);
    }

    public function update(Request $request, $appealId)
    {
        $appeal = DB::table('appeals')->where('id', $appealId)->first();
        abort_if(!$appeal, 404);

        // Same stage rule as edit(): a decided appeal is a record and must not change.
        if (!in_array($appeal->status, ['draft', 'submitted'], true)) {
            return redirect()->route('appeals.show', $appealId)
                ->with('error', 'messages.error.cannot_edit_stage');
        }

        $data = $request->validate([
            'title'   => ['required', 'string', 'max:255'],
            'grounds' => ['nullable', 'string', 'max:10000'],
        ]);

        DB::table('appeals')->where('id', $appealId)->update([
            'title'      => $data['title'],
            'grounds'    => $data['grounds'] ?? null,
            'updated_at' => now(),
        ]);

        return back()->with('success', [
            'key' => 'messages.success.updated',
            'replace' => ['resource' => __('messages.resources.appeal')],
        ]);
    }

    public function submit($appealId)
    {
        $appeal = DB::table('appeals')->where('id', $appealId)->first();
        abort_if(!$appeal, 404);

        if ($appeal->status !== 'draft') {
            return back()->with('error', 'messages.error.only_drafts_submit');
        }

        DB::table('appeals')->where('id', $appealId)->update([
            'status'       => 'submitted',
            'submitted_at' => now(),
            'updated_at'   => now(),
        ]);

        return back()->with('success', [
            'key' => 'messages.success.submitted',
            'replace' => ['resource' => __('messages.resources.appeal')],
        ]);
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
            return back()->with('error', 'messages.error.not_decidable');
        }

        DB::table('appeals')->where('id', $appealId)->update([
            'status'             => $data['decision'],
            'decided_by_user_id' => auth()->id(),
            'decided_at'         => now(),
            'decision_notes'     => $data['decision_notes'] ?? null,
            'updated_at'         => now(),
        ]);

        return back()->with('success', [
            'key' => 'messages.success.recorded',
            'replace' => ['resource' => __('messages.resources.decision')],
        ]);
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
        $path = app(\App\Services\SecureUploadService::class)->store($file, 'appeals', 'private', [
            'related_type' => 'appeal',
            'related_id' => $appeal->id,
            'user_id' => auth()->id(),
        ]);

        DB::table('appeal_documents')->insert([
            'appeal_id'  => $appealId,
            'label'      => $data['label'] ?? null,
            'path'       => $path,
            'mime'       => $file->getClientMimeType(),
            'size'       => $file->getSize(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', [
            'key' => 'messages.success.uploaded',
            'replace' => ['resource' => __('messages.resources.document')],
        ]);
    }

    public function deleteDoc($appealId, $docId)
    {
        $doc = DB::table('appeal_documents')
            ->where('id', $docId)
            ->where('appeal_id', $appealId)
            ->first();

        abort_if(!$doc, 404);

        if (!empty($doc->path)) {
            app(\App\Services\LegalHoldService::class)->assertFileMayBeDeleted((string) $doc->path);
            Storage::disk('private')->delete($doc->path);
            Storage::disk('public')->delete($doc->path); // legacy cleanup
        }

        DB::table('appeal_documents')->where('id', $docId)->delete();

        return back()->with('success', [
            'key' => 'messages.success.removed',
            'replace' => ['resource' => __('messages.resources.document')],
        ]);
    }
}
