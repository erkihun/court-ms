<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\LegalHoldService;
use App\Services\SecureUploadService;
use App\Support\EthiopianDate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class AppealController extends Controller
{
    public const STATUSES = ['draft', 'submitted', 'under_review', 'approved', 'rejected', 'closed'];

    /** Appeals may be edited only before a decision is recorded. */
    private const EDITABLE_STATUSES = ['draft', 'submitted'];

    private const DECIDABLE_STATUSES = ['submitted', 'under_review'];

    public function index(Request $request): Response
    {
        $status = $request->string('status')->toString();
        $status = in_array($status, self::STATUSES, true) ? $status : '';

        $appeals = DB::table('appeals as a')
            ->join('court_cases as c', 'c.id', '=', 'a.court_case_id')
            ->leftJoin('users as u', 'u.id', '=', 'a.decided_by_user_id')
            ->select('a.id', 'a.appeal_number', 'a.title', 'a.status', 'a.created_at', 'c.case_number', 'u.name as decided_by')
            ->when($status !== '', fn ($q) => $q->where('a.status', $status))
            ->orderByDesc('a.created_at')
            ->paginate(15)
            ->withQueryString()
            ->through(fn (object $appeal): array => [
                'id' => (int) $appeal->id,
                'appealNumber' => (string) $appeal->appeal_number,
                'caseNumber' => (string) $appeal->case_number,
                'title' => (string) $appeal->title,
                'status' => (string) $appeal->status,
                'decidedBy' => $appeal->decided_by,
                'created' => EthiopianDate::format($appeal->created_at),
            ]);

        return Inertia::render('Admin/Appeals/Index', [
            'appeals' => $appeals,
            'filters' => ['status' => $status],
            'statuses' => self::STATUSES,
        ]);
    }

    public function create(): Response
    {
        $cases = DB::table('court_cases')
            ->orderByDesc('created_at')
            ->limit(50)
            ->get(['id', 'case_number', 'title'])
            ->map(fn (object $case): array => [
                'id' => (int) $case->id,
                'label' => trim($case->case_number.' - '.$case->title),
            ])
            ->values();

        return Inertia::render('Admin/Appeals/Form', [
            'appeal' => null,
            'cases' => $cases,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'court_case_id' => ['required', 'integer', 'exists:court_cases,id'],
            'title' => ['required', 'string', 'max:255'],
            'grounds' => ['nullable', 'string', 'max:10000'],
        ]);

        return DB::transaction(function () use ($data): RedirectResponse {
            // Insert first to get the id, which the final appeal number is built from.
            $appealId = DB::table('appeals')->insertGetId([
                'court_case_id' => $data['court_case_id'],
                'applicant_id' => null,
                'submitted_by_user_id' => auth()->id(),
                'appeal_number' => 'TMP-'.uniqid(), // temporary to satisfy NOT NULL + UNIQUE
                'title' => $data['title'],
                'grounds' => $data['grounds'] ?? null,
                'status' => 'draft',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('appeals')->where('id', $appealId)->update([
                'appeal_number' => 'APL-'.now()->format('Y').'-'.str_pad((string) $appealId, 4, '0', STR_PAD_LEFT),
                'updated_at' => now(),
            ]);

            return redirect()
                ->route('appeals.show', $appealId)
                ->with('success', [
                    'key' => 'messages.success.created',
                    'replace' => ['resource' => __('messages.resources.appeal')],
                ]);
        });
    }

    public function show(int $appealId): Response
    {
        $appeal = DB::table('appeals as a')
            ->join('court_cases as c', 'c.id', '=', 'a.court_case_id')
            ->leftJoin('users as du', 'du.id', '=', 'a.decided_by_user_id')
            ->select('a.*', 'c.case_number', 'c.title as case_title', 'du.name as decided_by')
            ->where('a.id', $appealId)
            ->first();

        abort_if(! $appeal, 404);

        $documents = DB::table('appeal_documents')
            ->where('appeal_id', $appealId)
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (object $doc): array => [
                'id' => (int) $doc->id,
                'label' => $doc->label ?: basename((string) $doc->path),
                'uploaded' => EthiopianDate::format($doc->created_at, withTime: true),
                'downloadUrl' => route('appeals.docs.download', [$appealId, $doc->id]),
            ])
            ->values();

        return Inertia::render('Admin/Appeals/Show', [
            'appeal' => [
                'id' => (int) $appeal->id,
                'appealNumber' => (string) $appeal->appeal_number,
                'caseNumber' => (string) $appeal->case_number,
                'caseTitle' => (string) $appeal->case_title,
                'title' => (string) $appeal->title,
                'grounds' => $appeal->grounds,
                'status' => (string) $appeal->status,
                'submitted' => $appeal->submitted_at ? EthiopianDate::format($appeal->submitted_at, withTime: true) : null,
                'decidedBy' => $appeal->decided_by,
                'decided' => $appeal->decided_at ? EthiopianDate::format($appeal->decided_at, withTime: true) : null,
                'decisionNotes' => $appeal->decision_notes,
                'editable' => in_array($appeal->status, self::EDITABLE_STATUSES, true),
                'decidable' => in_array($appeal->status, self::DECIDABLE_STATUSES, true),
            ],
            'documents' => $documents,
        ]);
    }

    public function edit(int $appealId): Response|RedirectResponse
    {
        $appeal = DB::table('appeals')->where('id', $appealId)->first();
        abort_if(! $appeal, 404);

        if (! in_array($appeal->status, self::EDITABLE_STATUSES, true)) {
            return redirect()->route('appeals.show', $appealId)
                ->with('error', 'messages.error.cannot_edit_stage');
        }

        return Inertia::render('Admin/Appeals/Form', [
            'appeal' => [
                'id' => (int) $appeal->id,
                'appealNumber' => (string) $appeal->appeal_number,
                'title' => (string) $appeal->title,
                'grounds' => (string) ($appeal->grounds ?? ''),
            ],
            'cases' => [],
        ]);
    }

    public function update(Request $request, int $appealId): RedirectResponse
    {
        $appeal = DB::table('appeals')->where('id', $appealId)->first();
        abort_if(! $appeal, 404);

        // Same stage rule as edit(): a decided appeal is a record and must not change.
        if (! in_array($appeal->status, self::EDITABLE_STATUSES, true)) {
            return redirect()->route('appeals.show', $appealId)
                ->with('error', 'messages.error.cannot_edit_stage');
        }

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'grounds' => ['nullable', 'string', 'max:10000'],
        ]);

        DB::table('appeals')->where('id', $appealId)->update([
            'title' => $data['title'],
            'grounds' => $data['grounds'] ?? null,
            'updated_at' => now(),
        ]);

        return redirect()->route('appeals.show', $appealId)->with('success', [
            'key' => 'messages.success.updated',
            'replace' => ['resource' => __('messages.resources.appeal')],
        ]);
    }

    public function submit(int $appealId): RedirectResponse
    {
        $appeal = DB::table('appeals')->where('id', $appealId)->first();
        abort_if(! $appeal, 404);

        if ($appeal->status !== 'draft') {
            return back()->with('error', 'messages.error.only_drafts_submit');
        }

        DB::table('appeals')->where('id', $appealId)->update([
            'status' => 'submitted',
            'submitted_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', [
            'key' => 'messages.success.submitted',
            'replace' => ['resource' => __('messages.resources.appeal')],
        ]);
    }

    public function decide(Request $request, int $appealId): RedirectResponse
    {
        $appeal = DB::table('appeals')->where('id', $appealId)->first();
        abort_if(! $appeal, 404);

        $data = $request->validate([
            'decision' => ['required', Rule::in(['approved', 'rejected', 'closed'])],
            'decision_notes' => ['nullable', 'string', 'max:5000'],
        ]);

        if (! in_array($appeal->status, self::DECIDABLE_STATUSES, true)) {
            return back()->with('error', 'messages.error.not_decidable');
        }

        DB::table('appeals')->where('id', $appealId)->update([
            'status' => $data['decision'],
            'decided_by_user_id' => auth()->id(),
            'decided_at' => now(),
            'decision_notes' => $data['decision_notes'] ?? null,
            'updated_at' => now(),
        ]);

        return back()->with('success', [
            'key' => 'messages.success.recorded',
            'replace' => ['resource' => __('messages.resources.decision')],
        ]);
    }

    public function uploadDoc(Request $request, int $appealId): RedirectResponse
    {
        $appeal = DB::table('appeals')->where('id', $appealId)->first();
        abort_if(! $appeal, 404);

        $data = $request->validate([
            'label' => ['nullable', 'string', 'max:255'],
            'file' => ['required', 'file', 'mimes:pdf,doc,docx,jpg,jpeg,png,webp', 'max:5120'], // 5 MB
        ]);

        $file = $request->file('file');
        $path = app(SecureUploadService::class)->store($file, 'appeals', 'private', [
            'related_type' => 'appeal',
            'related_id' => $appeal->id,
            'user_id' => auth()->id(),
        ]);

        DB::table('appeal_documents')->insert([
            'appeal_id' => $appealId,
            'label' => $data['label'] ?? null,
            'path' => $path,
            'mime' => $file->getClientMimeType(),
            'size' => $file->getSize(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', [
            'key' => 'messages.success.uploaded',
            'replace' => ['resource' => __('messages.resources.document')],
        ]);
    }

    public function deleteDoc(int $appealId, int $docId): RedirectResponse
    {
        $doc = DB::table('appeal_documents')
            ->where('id', $docId)
            ->where('appeal_id', $appealId)
            ->first();

        abort_if(! $doc, 404);

        if (! empty($doc->path)) {
            app(LegalHoldService::class)->assertFileMayBeDeleted((string) $doc->path);
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
