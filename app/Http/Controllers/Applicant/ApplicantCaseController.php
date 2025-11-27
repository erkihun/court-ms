<?php

namespace App\Http\Controllers\Applicant;

use App\Http\Controllers\Controller;

use App\Models\ApplicantTermAcceptance;
use App\Models\TermsAndCondition;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use App\Mail\CaseMessageMail;
use App\Mail\ApplicantReceiptMail;
use Mews\Purifier\Facades\Purifier;
use App\Models\CourtCase;

class ApplicantCaseController extends Controller
{
    /**
     * List only the signed-in applicant's cases
     */
    public function index(Request $request)
    {
        $me = auth('applicant')->id();

        $cases = DB::table('court_cases as c')
            ->leftJoin('case_types as ct', 'ct.id', '=', 'c.case_type_id')

            ->select(
                'c.id',
                'c.case_number',
                'c.title',
                'c.status',
                'c.filing_date',
                'ct.name as case_type',

            )
            ->when(Schema::hasColumn('court_cases', 'applicant_id'), fn($q) => $q->where('c.applicant_id', $me))
            ->orderByDesc('c.created_at')
            ->paginate(10);

        return view('applicant.cases.index', compact('cases'));
    }

    /**
     * New case form
     */
    public function create()
    {
        $types  = DB::table('case_types')->orderBy('name')->get(['id', 'name']);
        $activeTerms = TermsAndCondition::published()->orderByDesc('published_at')->first();

        return view('applicant.cases.create', compact('types', 'activeTerms'));
    }

    private function generateCaseNumber(): string
    {
        $year = now()->year;

        $last = DB::table('court_cases')
            ->selectRaw("
                MAX(
                    CAST(SUBSTRING_INDEX(case_number, '-', -1) AS UNSIGNED)
                ) as max_seq
            ")
            ->where('case_number', 'LIKE', 'C-' . $year . '-%')
            ->value('max_seq');

        $next = (int) $last + 1;

        return sprintf('C-%d-%04d', $year, $next);
    }

    /**
     * Store new case (supports evidence PDFs & witness names/details)
     * NOTE: Sanitizes TinyMCE HTML fields on save.
     */
    public function store(Request $request)
    {
        $aid = auth('applicant')->id();
        abort_if(!$aid, 403);

        $activeTerms = TermsAndCondition::published()->orderByDesc('published_at')->first();

        $rules = [
            'title'              => ['required', 'string', 'max:255'],
            'description'        => ['required', 'string', 'max:10000'],
            'relief_requested'   => ['nullable', 'string', 'max:5000'],
            'certify_appeal'     => ['accepted'],
            'respondent_name'    => ['nullable', 'string', 'max:255'],
            'respondent_address' => ['nullable', 'string', 'max:500'],
            'case_type_id'       => ['required', 'integer', 'exists:case_types,id'],

            'filing_date'        => ['required', 'date'],
            'first_hearing_date' => ['nullable', 'date'],

            // initial evidence & witnesses
            'evidence_titles.*'  => ['nullable', 'string', 'max:255'],
            'evidence_files.*'   => ['nullable', 'file', 'mimes:pdf', 'max:5120'],

            'witnesses'              => ['array'],
            'witnesses.*.full_name'  => ['required', 'string', 'max:255'],
            'witnesses.*.phone'      => ['nullable', 'string', 'max:60'],
            'witnesses.*.email'      => ['nullable', 'email', 'max:150'],
            'witnesses.*.address'    => ['nullable', 'string', 'max:255'],
            'certify_evidence'       => ['accepted'],
        ];

        if ($activeTerms) {
            $rules['accept_terms'] = ['accepted'];
        }

        $messages = [
            'certify_appeal.accepted' => 'You must certify the validity of your appeal.',
            'certify_evidence.accepted' => 'You must certify that your evidence is true.',
            'accept_terms.accepted' => 'You must agree to the Terms & Conditions.',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        $validator->after(function ($validator) use ($request) {
            $witnesses = collect($request->input('witnesses', []));

            $phones = $witnesses->pluck('phone')->filter(fn($phone) => filled($phone))
                ->map(fn($phone) => mb_strtolower(trim($phone)));
            if ($phones->count() !== $phones->unique()->count()) {
                $validator->errors()->add('witnesses_duplicate_phone', 'Witness phone numbers must be unique.');
            }

            $emails = $witnesses->pluck('email')->filter(fn($email) => filled($email))
                ->map(fn($email) => mb_strtolower(trim($email)));
            if ($emails->count() !== $emails->unique()->count()) {
                $validator->errors()->add('witnesses_duplicate_email', 'Witness email addresses must be unique.');
            }
        });

        $data = $validator->validate();

        // Sanitize TinyMCE HTML (decode if entity-encoded, then Purifier 'cases' profile)
        $descHtml   = $this->cleanHtml($data['description'] ?? '');
        $reliefHtml = $this->cleanHtml($data['relief_requested'] ?? '');

        // Optional: enforce ~1,300 words like your UI label
        if ($this->wordCount($descHtml) > 1300 || $this->wordCount($reliefHtml) > 1300) {
            return back()
                ->withErrors(['description' => 'Please keep each editor under ~1,300 words.'])
                ->withInput();
        }

        DB::beginTransaction();

        try {
            // 1) Insert with temporary unique case number
            $tempNumber = 'TMP-' . Str::uuid();

            $caseId = DB::table('court_cases')->insertGetId([
                'applicant_id'       => $aid,
                'respondent_name'    => isset($data['respondent_name']) ? strip_tags($data['respondent_name']) : null,
                'respondent_address' => isset($data['respondent_address']) ? strip_tags($data['respondent_address']) : null,
                'case_number'        => $tempNumber,
                'title'              => trim($data['title']),
                'description'        => $descHtml,      // sanitized HTML
                'case_type_id'       => $data['case_type_id'],

                'relief_requested'   => $reliefHtml ?: null, // sanitized HTML
                'filing_date'        => $data['filing_date'],
                'first_hearing_date' => $data['first_hearing_date'] ?? null,
                'status'             => 'pending',
                'assigned_user_id'   => null,
                'assigned_at'        => null,
                'notes'              => $data['notes'] ?? null,
                'created_at'         => now(),
                'updated_at'         => now(),
            ]);

            // 2) Final readable case number
            $finalNumber = 'C-' . now()->format('Y') . '-' . str_pad((string) $caseId, 4, '0', STR_PAD_LEFT);

            DB::table('court_cases')->where('id', $caseId)->update([
                'case_number' => $finalNumber,
                'updated_at'  => now(),
            ]);

            // 3) Initial status log
            DB::table('case_status_logs')->insert([
                'case_id'            => $caseId,
                'from_status'        => null,
                'to_status'          => 'pending',
                'changed_by_user_id' => null,
                'created_at'         => now(),
                'updated_at'         => now(),
            ]);

            if ($activeTerms) {
                ApplicantTermAcceptance::updateOrCreate(
                    [
                        'applicant_id' => $aid,
                        'terms_and_condition_id' => $activeTerms->id,
                    ],
                    [
                        'accepted_at' => now(),
                    ]
                );
            }

            // 4) Initial evidence PDFs (ensure mime/size + file_path/path)
            if ($request->hasFile('evidence_files')) {
                $titles = $request->input('evidence_titles', []);
                foreach ($request->file('evidence_files') as $i => $file) {
                    if (!$file) continue;

                    /** @var UploadedFile $file */
                    $stored = $file->store('evidences', 'public');

                    $insert = [
                        'case_id'    => $caseId,
                        'type'       => 'document',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];

                    if (Schema::hasColumn('case_evidences', 'file_path')) {
                        $insert['file_path'] = $stored;
                    } elseif (Schema::hasColumn('case_evidences', 'path')) {
                        $insert['path'] = $stored;
                    }

                    if (Schema::hasColumn('case_evidences', 'title')) {
                        $insert['title'] = $titles[$i] ?? ('Document ' . ($i + 1));
                    }
                    if (Schema::hasColumn('case_evidences', 'description')) {
                        $insert['description'] = null;
                    }

                    if (Schema::hasColumn('case_evidences', 'mime')) {
                        $insert['mime'] = $file->getClientMimeType() ?: 'application/pdf';
                    }
                    if (Schema::hasColumn('case_evidences', 'size')) {
                        $insert['size'] = $file->getSize();
                    }

                    if (Schema::hasColumn('case_evidences', 'uploaded_by_applicant_id')) {
                        $insert['uploaded_by_applicant_id'] = $aid;
                    } elseif (Schema::hasColumn('case_evidences', 'uploaded_by_user_id')) {
                        $insert['uploaded_by_user_id'] = $aid;
                    }

                    DB::table('case_evidences')->insert($insert);
                }
            }

            // 5) Initial witnesses (from case_witnesses)
            $witnesses = $request->input('witnesses', []);
            foreach ($witnesses as $w) {
                $fullName = trim((string) ($w['full_name'] ?? ''));
                if ($fullName === '') continue;

                DB::table('case_witnesses')->insert([
                    'case_id'            => $caseId,
                    'full_name'          => $fullName,
                    'phone'              => $w['phone'] ?? null,
                    'email'              => $w['email'] ?? null,
                    'address'            => $w['address'] ?? null,

                    'created_by_user_id' => null,
                    'updated_by_user_id' => null,
                    'created_at'         => now(),
                    'updated_at'         => now(),
                ]);
            }

            DB::commit();

            return redirect()
                ->route('applicant.cases.show', $caseId)
                ->with('success', "Case created successfully. Number: {$finalNumber}");
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            return back()->withInput()->with('error', 'Failed to create case: ' . $e->getMessage());
        }
    }

    /**
     * Show a case the applicant owns
     */
    public function show(int $id)
    {
        $aid = auth('applicant')->id();

        $case = DB::table('court_cases as c')
            ->leftJoin('case_types as ct', 'ct.id', '=', 'c.case_type_id')

            ->select('c.*', 'ct.name as case_type')
            ->where('c.id', $id)
            ->where('c.applicant_id', $aid)
            ->first();

        abort_if(!$case, 404);

        $timeline = DB::table('case_status_logs')
            ->where('case_id', $id)
            ->orderBy('created_at')
            ->get();

        $files = DB::table('case_files')
            ->where('case_id', $id)
            ->orderByDesc('created_at')
            ->get();

        $msgs = DB::table('case_messages as m')
            ->leftJoin('users as u', 'u.id', '=', 'm.sender_user_id')
            ->select('m.*', 'u.name as admin_name')
            ->where('m.case_id', $id)
            ->orderBy('m.created_at')
            ->get();

        $hearings = DB::table('case_hearings')
            ->where('case_id', $id)
            ->orderBy('hearing_at')
            ->get();

        $docs = DB::table('case_evidences')
            ->where('case_id', $id)
            ->where('type', 'document')
            ->orderBy('id')
            ->get();

        // Witnesses
        $witnesses = DB::table('case_witnesses')
            ->where('case_id', $id)
            ->orderBy('id')
            ->get();

        // Auto-mark as seen
        $now  = now();
        $rows = [];

        $unseenMsgIds = DB::table('case_messages as m')
            ->where('m.case_id', $id)
            ->whereNotNull('m.sender_user_id')
            ->where('m.created_at', '>=', now()->subDays(14))
            ->whereNotExists(function ($q) use ($aid) {
                $q->from('notification_reads as nr')
                    ->whereColumn('nr.source_id', 'm.id')
                    ->where('nr.type', 'message')
                    ->where('nr.applicant_id', $aid);
            })
            ->pluck('m.id');

        foreach ($unseenMsgIds as $mid) {
            $rows[] = ['applicant_id' => $aid, 'type' => 'message', 'source_id' => $mid, 'seen_at' => $now, 'created_at' => $now, 'updated_at' => $now];
        }

        $unseenStatusIds = DB::table('case_status_logs as l')
            ->where('l.case_id', $id)
            ->where('l.created_at', '>=', now()->subDays(14))
            ->whereNotExists(function ($q) use ($aid) {
                $q->from('notification_reads as nr')
                    ->whereColumn('nr.source_id', 'l.id')
                    ->where('nr.type', 'status')
                    ->where('nr.applicant_id', $aid);
            })
            ->pluck('l.id');

        foreach ($unseenStatusIds as $sid) {
            $rows[] = ['applicant_id' => $aid, 'type' => 'status', 'source_id' => $sid, 'seen_at' => $now, 'created_at' => $now, 'updated_at' => $now];
        }

        $unseenHearingIds = DB::table('case_hearings as h')
            ->where('h.case_id', $id)
            ->whereBetween('h.hearing_at', [now()->subDay(), now()->addDays(60)])
            ->whereNotExists(function ($q) use ($aid) {
                $q->from('notification_reads as nr')
                    ->whereColumn('nr.source_id', 'h.id')
                    ->where('nr.type', 'hearing')
                    ->where('nr.applicant_id', $aid);
            })
            ->pluck('h.id');

        foreach ($unseenHearingIds as $hid) {
            $rows[] = ['applicant_id' => $aid, 'type' => 'hearing', 'source_id' => $hid, 'seen_at' => $now, 'created_at' => $now, 'updated_at' => $now];
        }

        if (!empty($rows)) {
            DB::table('notification_reads')->upsert(
                $rows,
                ['applicant_id', 'type', 'source_id'],
                ['seen_at', 'updated_at']
            );
        }

        return view('applicant.cases.show', compact(
            'case',
            'timeline',
            'files',
            'msgs',
            'hearings',
            'docs',
            'witnesses'
        ));
    }

    /**
     * Edit form (only while pending)
     */
    public function edit(int $id)
    {
        $me = auth('applicant')->id();

        $case = DB::table('court_cases')
            ->where('id', $id)
            ->where('applicant_id', $me)
            ->first();

        abort_if(!$case, 404);

        if ($case->status !== 'pending') {
            return redirect()->route('applicant.cases.show', $id)
                ->with('error', 'You can only edit pending cases.');
        }

        $types  = DB::table('case_types')->orderBy('name')->get(['id', 'name']);


        $docs = DB::table('case_evidences')
            ->where('case_id', $id)->where('type', 'document')
            ->orderBy('id')->get();

        // correct table for witnesses
        $witnesses = DB::table('case_witnesses')
            ->where('case_id', $id)
            ->orderBy('id')
            ->get();

        return view('applicant.cases.edit', compact('case', 'types', 'docs', 'witnesses'));
    }
    public function destroy($id)
    {
        $applicantId = auth('applicant')->id();

        // Ensure the case belongs to this applicant
        $case = CourtCase::where('applicant_id', $applicantId)->findOrFail($id);

        // Only allow deletion if status is pending
        if ($case->status !== 'pending') {
            abort(403, 'Only pending cases can be deleted.');
        }

        $case->delete(); // if you want soft delete, enable SoftDeletes on the model instead

        return redirect()
            ->route('applicant.cases.index')
            ->with('success', __('cases.deleted_successfully') ?? 'Case deleted successfully.');
    }
    /**
     * Update case (add more docs/witness names; only while pending)
     * NOTE: Sanitizes TinyMCE HTML fields on save.
     */
    public function update(Request $request, int $id)
    {
        $me = auth('applicant')->id();

        $case = DB::table('court_cases')
            ->where('id', $id)
            ->where('applicant_id', $me)
            ->first();

        abort_if(!$case, 404);

        if ($case->status !== 'pending') {
            return redirect()->route('applicant.cases.show', $id)
                ->with('error', 'You can only edit pending cases.');
        }

        $rules = [
            'title'              => ['required', 'string', 'max:255'],
            'description'        => ['required', 'string', 'max:10000'],
            'relief_requested'   => ['nullable', 'string', 'max:5000'],
            'certify_appeal'     => ['accepted'],
            'respondent_name'    => ['nullable', 'string', 'max:255'],
            'respondent_address' => ['nullable', 'string', 'max:500'],
            'case_type_id'       => ['required', 'integer', 'exists:case_types,id'],

            'filing_date'        => ['required', 'date'],

            // uploads
            'evidence_titles.*'  => ['nullable', 'string', 'max:255'],
            'evidence_files.*'   => ['nullable', 'file', 'mimes:pdf', 'max:5120'],

            // witnesses (match edit form names)
            'witnesses'                 => ['array'],
            'witnesses.*.full_name'     => ['nullable', 'string', 'max:255'],
            'witnesses.*.phone'         => ['nullable', 'string', 'max:60'],
            'witnesses.*.email'         => ['nullable', 'email', 'max:150'],
            'witnesses.*.address'       => ['nullable', 'string', 'max:255'],
            'certify_evidence'          => ['accepted'],
        ];

        $messages = [
            'certify_appeal.accepted' => 'You must certify the validity of your appeal.',
            'certify_evidence.accepted' => 'You must certify that your evidence is true.',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        $validator->after(function ($validator) use ($request, $id) {
            $newWitnesses = collect($request->input('witnesses', []));

            $newPhones = $newWitnesses->pluck('phone')
                ->filter(fn($phone) => filled($phone))
                ->map(fn($phone) => mb_strtolower(trim($phone)))
                ->values();
            if ($newPhones->count() !== $newPhones->unique()->count()) {
                $validator->errors()->add('witnesses_duplicate_phone', 'Witness phone numbers must be unique.');
            }

            $existingPhones = DB::table('case_witnesses')
                ->where('case_id', $id)
                ->pluck('phone')
                ->filter(fn($phone) => filled($phone))
                ->map(fn($phone) => mb_strtolower(trim($phone)));
            if ($existingPhones->intersect($newPhones)->isNotEmpty()) {
                $validator->errors()->add('witnesses_duplicate_phone', 'Witness phone numbers must be unique.');
            }

            $newEmails = $newWitnesses->pluck('email')
                ->filter(fn($email) => filled($email))
                ->map(fn($email) => mb_strtolower(trim($email)))
                ->values();
            if ($newEmails->count() !== $newEmails->unique()->count()) {
                $validator->errors()->add('witnesses_duplicate_email', 'Witness email addresses must be unique.');
            }

            $existingEmails = DB::table('case_witnesses')
                ->where('case_id', $id)
                ->pluck('email')
                ->filter(fn($email) => filled($email))
                ->map(fn($email) => mb_strtolower(trim($email)));
            if ($existingEmails->intersect($newEmails)->isNotEmpty()) {
                $validator->errors()->add('witnesses_duplicate_email', 'Witness email addresses must be unique.');
            }
        });

        $data = $validator->validate();

        // Sanitize TinyMCE HTML (decode if entity-encoded, then Purifier 'cases' profile)
        $descHtml   = $this->cleanHtml($data['description'] ?? '');
        $reliefHtml = $this->cleanHtml($data['relief_requested'] ?? '');

        // Optional word limit to mirror UI
        if ($this->wordCount($descHtml) > 1300 || $this->wordCount($reliefHtml) > 1300) {
            return back()
                ->withErrors(['description' => 'Please keep each editor under ~1,300 words.'])
                ->withInput();
        }

        DB::beginTransaction();

        try {
            // update core fields
            DB::table('court_cases')->where('id', $id)->update([
                'title'              => trim($data['title']),
                'description'        => $descHtml,           // sanitized HTML
                'relief_requested'   => $reliefHtml ?: null, // sanitized HTML
                'respondent_name'    => isset($data['respondent_name']) ? strip_tags($data['respondent_name']) : null,
                'respondent_address' => isset($data['respondent_address']) ? strip_tags($data['respondent_address']) : null,
                'case_type_id'       => $data['case_type_id'],

                'filing_date'        => $data['filing_date'],
                'updated_at'         => now(),
            ]);

            // add new PDFs (if any)
            if ($request->hasFile('evidence_files')) {
                $titles = $request->input('evidence_titles', []);
                foreach ($request->file('evidence_files') as $i => $file) {
                    if (!$file) continue;

                    /** @var \Illuminate\Http\UploadedFile $file */
                    $stored = $file->store('evidences', 'public');

                    $insert = [
                        'case_id'    => $id,
                        'type'       => 'document',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];

                    if (Schema::hasColumn('case_evidences', 'file_path')) {
                        $insert['file_path'] = $stored;
                    } elseif (Schema::hasColumn('case_evidences', 'path')) {
                        $insert['path'] = $stored;
                    }
                    if (Schema::hasColumn('case_evidences', 'title')) {
                        $insert['title'] = $titles[$i] ?? ('Document ' . ($i + 1));
                    }
                    if (Schema::hasColumn('case_evidences', 'description')) {
                        $insert['description'] = null;
                    }
                    if (Schema::hasColumn('case_evidences', 'mime')) {
                        $insert['mime'] = $file->getClientMimeType() ?: 'application/pdf';
                    }
                    if (Schema::hasColumn('case_evidences', 'size')) {
                        $insert['size'] = $file->getSize();
                    }
                    if (Schema::hasColumn('case_evidences', 'uploaded_by_applicant_id')) {
                        $insert['uploaded_by_applicant_id'] = $me;
                    } elseif (Schema::hasColumn('case_evidences', 'uploaded_by_user_id')) {
                        $insert['uploaded_by_user_id'] = $me;
                    }

                    DB::table('case_evidences')->insert($insert);
                }
            }

            // add new witnesses (from edit form)
            $incoming = $request->input('witnesses', []);
            foreach ($incoming as $w) {
                $full = trim((string) ($w['full_name'] ?? ''));
                if ($full === '') continue; // skip empty rows

                $row = [
                    'case_id'    => $id,
                    'full_name'  => $full,
                    'phone'      => $w['phone']   ?? null,
                    'email'      => $w['email']   ?? null,
                    'address'    => $w['address'] ?? null,

                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                if (Schema::hasColumn('case_witnesses', 'created_by_user_id')) {
                    $row['created_by_user_id'] = null;
                }
                if (Schema::hasColumn('case_witnesses', 'updated_by_user_id')) {
                    $row['updated_by_user_id'] = null;
                }

                DB::table('case_witnesses')->insert($row);
            }

            DB::commit();
            return redirect()->route('applicant.cases.show', $id)->with('success', 'Case updated.');
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            return back()->withInput()->with('error', 'Failed to update case: ' . $e->getMessage());
        }
    }

    public function deleteEvidence(int $id, int $evidenceId)
    {
        $applicantId = auth('applicant')->id();

        $case = DB::table('court_cases')
            ->where('id', $id)
            ->where('applicant_id', $applicantId)
            ->first();
        abort_if(!$case, 404);

        if ($case->status !== 'pending') {
            return back()->with('error', 'You can only remove evidence on pending cases.');
        }

        $ev = DB::table('case_evidences')
            ->where('id', $evidenceId)
            ->where('case_id', $id)
            ->first();
        abort_if(!$ev, 404);

        $filePath = $ev->file_path ?? $ev->path ?? null;
        if (!empty($filePath)) {
            Storage::disk('public')->delete($filePath);
        }

        DB::table('case_evidences')->where('id', $evidenceId)->delete();

        return back()->with('success', 'Evidence removed.');
    }

    public function deleteWitness(int $id, int $witnessId)
    {
        $applicantId = auth('applicant')->id();

        // enforce ownership + pending
        $case = DB::table('court_cases')
            ->where('id', $id)
            ->where('applicant_id', $applicantId)
            ->first();

        abort_if(!$case, 404);

        if ($case->status !== 'pending') {
            return back()->with('error', 'You can only remove witnesses on pending cases.');
        }

        $exists = DB::table('case_witnesses')
            ->where('id', $witnessId)
            ->where('case_id', $id)
            ->exists();

        abort_unless($exists, 404);

        DB::table('case_witnesses')
            ->where('id', $witnessId)
            ->where('case_id', $id)
            ->delete();

        return back()->with('success', 'Witness removed.');
    }

    /**
     * Upload a generic file to the case (separate from "evidences" PDFs)
     */
    public function uploadFile(Request $request, $id)
    {
        $applicantId = auth('applicant')->id();

        $own = DB::table('court_cases')->where('id', $id)->where('applicant_id', $applicantId)->exists();
        abort_unless($own, 403);

        $data = $request->validate([
            'label' => ['nullable', 'string', 'max:255'],
            'file'  => ['required', 'file', 'mimes:pdf,jpg,jpeg,png,webp,doc,docx', 'max:4096'],
        ]);

        /** @var UploadedFile $file */
        $file = $data['file'];
        $path = $file->store('case_files', 'public');

        DB::table('case_files')->insert([
            'case_id'                   => $id,
            'uploaded_by_applicant_id'  => $applicantId,
            'label'                     => isset($data['label']) ? strip_tags($data['label']) : null,
            'path'                      => $path,
            'mime'                      => $file->getClientMimeType(),
            'size'                      => $file->getSize(),
            'created_at'                => now(),
            'updated_at'                => now(),
        ]);

        return back()->with('success', 'File uploaded.');
    }

    /**
     * Delete a file uploaded by the applicant
     */
    public function deleteFile($id, $fileId)
    {
        $applicantId = auth('applicant')->id();

        $file = DB::table('case_files')->where('id', $fileId)->where('case_id', $id)->first();
        abort_unless($file, 404);

        abort_unless((int) $file->uploaded_by_applicant_id === (int) $applicantId, 403);

        Storage::disk('public')->delete($file->path);
        DB::table('case_files')->where('id', $fileId)->delete();

        return back()->with('success', 'File removed.');
    }

    /**
     * Applicant posts a message to staff
     */
    public function postMessage(Request $request, $id)
    {
        $applicantId = auth('applicant')->id();

        $own = DB::table('court_cases')->where('id', $id)->where('applicant_id', $applicantId)->exists();
        abort_unless($own, 403);

        $data = $request->validate([
            'body' => ['required', 'string', 'max:5000'],
        ]);

        DB::table('case_messages')->insert([
            'case_id'             => $id,
            'sender_applicant_id' => $applicantId,
            'body'                => $data['body'], // rendered with {{ }} so XSS-safe
            'created_at'          => now(),
            'updated_at'          => now(),
        ]);

        // notify assignee (or fallback inbox)
        $caseRow = DB::table('court_cases')->where('id', $id)->first();

        $assigneeEmail = null;
        if (!empty($caseRow->assigned_user_id)) {
            $assigneeEmail = DB::table('users')->where('id', $caseRow->assigned_user_id)->value('email');
        }
        $to = $assigneeEmail ?: config('mail.from.address');

        if ($to) {
            $preview = mb_strimwidth($data['body'], 0, 180, '…');
            Mail::to($to)->send(new CaseMessageMail($caseRow, 'Applicant', $preview));
        }

        return back()->with('success', 'Message sent.');
    }

    /**
     * Printable receipt page
     */
    public function receipt(int $id)
    {
        $me = auth('applicant')->id();

        $case = DB::table('court_cases as c')
            ->leftJoin('case_types as ct', 'ct.id', '=', 'c.case_type_id')

            ->leftJoin('applicants as a', 'a.id', '=', 'c.applicant_id')
            ->select(
                'c.*',
                'ct.name as case_type',

                'a.first_name',
                'a.middle_name',
                'a.last_name',
                'a.email',
                'a.phone'
            )
            ->where('c.id', $id)
            ->where('c.applicant_id', $me)
            ->first();

        abort_if(!$case, 404);

        $evidenceDocs = DB::table('case_evidences')
            ->where('case_id', $id)
            ->where('type', 'document')
            ->orderBy('id')
            ->get();

        // witnesses
        $witnesses = DB::table('case_witnesses')
            ->where('case_id', $id)
            ->orderBy('id')
            ->get();

        $files = DB::table('case_files')
            ->where('case_id', $id)
            ->orderBy('created_at', 'desc')
            ->get();

        $hearings = DB::table('case_hearings')
            ->where('case_id', $id)
            ->orderBy('hearing_at')
            ->get();

        return view('applicant.cases.receipt', compact('case', 'evidenceDocs', 'witnesses', 'files', 'hearings'));
    }

    /**
     * Download .ics for a hearing and mark it seen
     */
    public function downloadHearingIcs(int $id, int $hearingId)
    {
        $aid = auth('applicant')->id();

        $case = DB::table('court_cases')->where('id', $id)->where('applicant_id', $aid)->first();
        abort_if(!$case, 404);

        $hearing = DB::table('case_hearings')->where('id', $hearingId)->where('case_id', $id)->first();
        abort_if(!$hearing, 404);

        DB::table('notification_reads')->updateOrInsert(
            ['applicant_id' => $aid, 'type' => 'hearing', 'source_id' => $hearing->id],
            ['seen_at' => now(), 'updated_at' => now(), 'created_at' => now()]
        );

        $start = Carbon::parse($hearing->hearing_at)->utc();
        $end   = (clone $start)->addHour();
        $now   = Carbon::now('UTC');

        $host = parse_url(config('app.url', 'https://court-ms.local'), PHP_URL_HOST) ?: 'court-ms.local';
        $uid  = 'hearing-' . $hearing->id . '@' . $host;

        $escape = fn($t) => str_replace(["\\", ";", ","], ["\\\\", "\;", "\,"], $t ?? '');

        $summary     = $escape('Court Hearing: ' . ($case->case_number ?? ''));
        $description = $escape(($case->title ?? '') . ' — Please arrive early with any documents.');
        $location    = $escape($hearing->location ?? '');

        $ics = implode("\r\n", [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//Court-MS//EN',
            'CALSCALE:GREGORIAN',
            'METHOD:PUBLISH',
            'BEGIN:VEVENT',
            'UID:' . $uid,
            'DTSTAMP:' . $now->format('Ymd\THis\Z'),
            'DTSTART:' . $start->format('Ymd\THis\Z'),
            'DTEND:'   . $end->format('Ymd\THis\Z'),
            'SUMMARY:' . $summary,
            'DESCRIPTION:' . $description,
            'LOCATION:' . $location,
            'END:VEVENT',
            'END:VCALENDAR',
            '',
        ]);

        $filename = 'hearing-' . ($case->case_number ?? 'case') . '-' . $hearing->id . '.ics';

        return response($ics, 200, [
            'Content-Type'              => 'text/calendar; charset=utf-8',
            'Content-Disposition'       => 'attachment; filename="' . $filename . '"',
            'Content-Transfer-Encoding' => 'binary',
        ]);
    }

    /**
     * Download receipt as PDF
     */
    public function receiptPdf(int $id)
    {
        $me = auth('applicant')->id();

        $case = DB::table('court_cases as c')
            ->leftJoin('case_types as ct', 'ct.id', '=', 'c.case_type_id')

            ->leftJoin('applicants as a', 'a.id', '=', 'c.applicant_id')
            ->select(
                'c.*',
                'ct.name as case_type',

                'a.first_name',
                'a.middle_name',
                'a.last_name',
                'a.email',
                'a.phone'
            )
            ->where('c.id', $id)
            ->where('c.applicant_id', $me)
            ->first();

        abort_if(!$case, 404);

        $evidenceDocs = DB::table('case_evidences')->where('case_id', $id)->where('type', 'document')->orderBy('id')->get();

        // witnesses
        $witnesses    = DB::table('case_witnesses')->where('case_id', $id)->orderBy('id')->get();

        $files        = DB::table('case_files')->where('case_id', $id)->orderByDesc('created_at')->get();
        $hearings     = DB::table('case_hearings')->where('case_id', $id)->orderBy('hearing_at')->get();

        $pdf = PDF::loadView('applicant.cases.receipt-pdf', [
            'case'         => $case,
            'evidenceDocs' => $evidenceDocs,
            'witnesses'    => $witnesses,
            'files'        => $files,
            'hearings'     => $hearings,
        ])->setPaper('a4');

        $filename = 'receipt-' . ($case->case_number ?? 'case') . '.pdf';
        return $pdf->download($filename);
    }

    /**
     * Email receipt PDF to the applicant
     */
    public function emailReceipt(int $id)
    {
        $me    = auth('applicant')->id();
        $email = auth('applicant')->user()->email ?? null;

        $case = DB::table('court_cases as c')
            ->leftJoin('case_types as ct', 'ct.id', '=', 'c.case_type_id')

            ->leftJoin('applicants as a', 'a.id', '=', 'c.applicant_id')
            ->select(
                'c.*',
                'ct.name as case_type',

                'a.first_name',
                'a.middle_name',
                'a.last_name',
                'a.email',
                'a.phone'
            )
            ->where('c.id', $id)
            ->where('c.applicant_id', $me)
            ->first();

        abort_if(!$case, 404);

        $evidenceDocs = DB::table('case_evidences')->where('case_id', $id)->where('type', 'document')->orderBy('id')->get();

        // witnesses
        $witnesses    = DB::table('case_witnesses')->where('case_id', $id)->orderBy('id')->get();

        $files        = DB::table('case_files')->where('case_id', $id)->orderByDesc('created_at')->get();
        $hearings     = DB::table('case_hearings')->where('case_id', $id)->orderBy('hearing_at')->get();

        $pdf = PDF::loadView('applicant.cases.receipt-pdf', [
            'case'         => $case,
            'evidenceDocs' => $evidenceDocs,
            'witnesses'    => $witnesses,
            'files'        => $files,
            'hearings'     => $hearings,
        ])->setPaper('a4');

        if (!$email) {
            return back()->with('error', 'We could not find your email address.');
        }

        try {
            $filename = 'receipt-' . ($case->case_number ?? 'case') . '.pdf';
            Mail::to($email)->send(new ApplicantReceiptMail($case, $pdf->output(), $filename));
            Log::info('Applicant receipt PDF sent', ['case_id' => $id, 'to' => $email]);
        } catch (\Throwable $e) {
            Log::error('Failed sending applicant receipt PDF', [
                'case_id' => $id,
                'error'   => $e->getMessage(),
            ]);
            return back()->with('error', 'Could not send the receipt email right now.');
        }

        return back()->with('success', 'Receipt PDF emailed to ' . $email . '.');
    }

    // --------------------
    // Helpers
    // --------------------

    /**
     * Decode entity-encoded HTML (if needed) and sanitize using Purifier 'cases' profile.
     */
    private function cleanHtml(?string $html): string
    {
        $s = (string) ($html ?? '');
        if ($s === '') return '';
        // If TinyMCE content got entity-encoded (&lt;p&gt;), decode to real tags first
        if (str_contains($s, '&lt;') || str_contains($s, '&gt;')) {
            $s = htmlspecialchars_decode($s, ENT_QUOTES);
        }
        return Purifier::clean($s, 'cases');
    }

    /**
     * Count words in sanitized HTML (approx; strips tags first).
     */
    private function wordCount(string $html): int
    {
        return str_word_count(strip_tags($html));
    }
}
