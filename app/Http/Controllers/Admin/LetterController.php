<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Models\Letter;
use App\Models\LetterTemplate;
use App\Models\CourtCase;
use App\Mail\LetterApprovedMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class LetterController extends Controller
{
    public function index()
    {
        $letters = Letter::with(['template', 'author'])
            ->latest()
            ->paginate(5)
            ->withQueryString();

        return view('admin.letters.index', compact('letters'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'template_id'       => ['required', 'exists:letter_templates,id'],
            'recipient_name'    => ['nullable', 'string', 'max:255'],
            'recipient_title'   => ['nullable', 'string', 'max:255'],
            'recipient_company' => ['nullable', 'string', 'max:255'],
            'subject'           => ['nullable', 'string', 'max:255'],
            'case_number'       => ['nullable', 'string', 'max:60'],
            'body'              => ['required', 'string'],
            'cc'                => ['nullable', 'string', 'max:255'],
            'send_to_applicant' => ['nullable', 'boolean'],
            'send_to_respondent'=> ['nullable', 'boolean'],
            'approved_by_name'  => ['nullable', 'string', 'max:255'],
            'approved_by_title' => ['nullable', 'string', 'max:255'],
        ]);

        $sendToApplicant = array_key_exists('send_to_applicant', $data) ? (bool) $data['send_to_applicant'] : true;
        $sendToRespondent = array_key_exists('send_to_respondent', $data) ? (bool) $data['send_to_respondent'] : true;

        if (!$sendToApplicant && !$sendToRespondent) {
            return back()
                ->withErrors(['send_to_applicant' => __('letters.form.delivery_required')])
                ->withInput();
        }

        // Block letters for closed cases
        if (!empty($data['case_number'])) {
            $case = CourtCase::where('case_number', $data['case_number'])->first();
            if ($case && ($case->status === 'closed')) {
                return back()
                    ->withErrors(['case_number' => 'This case is closed; you cannot write a letter for it.'])
                    ->withInput();
            }
        }

        $data['send_to_applicant'] = $sendToApplicant;
        $data['send_to_respondent'] = $sendToRespondent;
        $data['body'] = $this->applyTemplatePlaceholders(
            (string) ($data['body'] ?? ''),
            $data['case_number'] ?? null
        );
        $recipientName = trim((string) ($data['recipient_name'] ?? ''));
        if ($recipientName === '') {
            $targets = [];
            $caseParty = null;
            if (!empty($data['case_number'])) {
                $caseParty = DB::table('court_cases as c')
                    ->leftJoin('applicants as a', 'a.id', '=', 'c.applicant_id')
                    ->select(
                        'c.respondent_name',
                        DB::raw("TRIM(CONCAT(COALESCE(a.first_name,''), ' ', COALESCE(a.last_name,''))) as applicant_name")
                    )
                    ->where('c.case_number', $data['case_number'])
                    ->first();
            }
            if ($sendToApplicant) {
                $targets[] = trim((string) ($caseParty->applicant_name ?? '')) ?: __('letters.form.deliver_applicant');
            }
            if ($sendToRespondent) {
                $targets[] = trim((string) ($caseParty->respondent_name ?? '')) ?: __('letters.form.deliver_respondent');
            }
            $recipientName = implode(', ', $targets);
        }
        $data['recipient_name'] = $recipientName;

        $letter          = null;
        $template        = LetterTemplate::findOrFail($data['template_id']);
        DB::transaction(function () use ($request, $data, $template, &$letter) {
            $lockedTemplate = LetterTemplate::where('id', $template->id)->lockForUpdate()->first();
            $lockedTemplate->reference_sequence = ($lockedTemplate->reference_sequence ?? 0) + 1;
            $lockedTemplate->save();

            $baseSubject = trim($data['subject'] ?? $lockedTemplate->title);
            $subjectValue = $baseSubject === '' ? null : $baseSubject;

            $baseSubject = trim($data['subject'] ?? $lockedTemplate->title);
            $subjectValue = $baseSubject === '' ? null : $baseSubject;

            // CASE NUMBER–BASED REFERENCE
            $caseNumber = $data['case_number'] ?? null;

            if ($caseNumber) {
                // Lock rows for this case during reference generation.
                $last = DB::table('letters')
                    ->where('case_number', $caseNumber)
                    ->lockForUpdate()
                    ->orderBy('id', 'desc')
                    ->first();

                if ($last && preg_match('/\/(\d{2})$/', $last->reference_number, $m)) {
                    $nextSeq = intval($m[1]) + 1;
                } else {
                    $nextSeq = 1;
                }

                do {
                    $seq = str_pad($nextSeq, 2, '0', STR_PAD_LEFT);
                    $referenceNumber = "{$caseNumber}/{$seq}";
                    $exists = DB::table('letters')
                        ->where('reference_number', $referenceNumber)
                        ->lockForUpdate()
                        ->exists();
                    $nextSeq++;
                } while ($exists);
            } else {
                $referenceNumber = null;
            }

            $letter = Letter::create([
                'letter_template_id' => $lockedTemplate->id,
                'user_id'            => $request->user()->id,
                'recipient_name'     => $data['recipient_name'],
                'recipient_title'    => $data['recipient_title'] ?? null,
                'recipient_company'  => $data['recipient_company'] ?? null,
                'subject'            => $subjectValue,
                'reference_number'   => $referenceNumber,
                'case_number'        => $data['case_number'] ?? null,
                'body'               => $data['body'],
                'cc'                 => $data['cc'] ?? null,
                'send_to_applicant'  => $data['send_to_applicant'],
                'send_to_respondent' => $data['send_to_respondent'],
                'approved_by_name'   => $data['approved_by_name'] ?? null,
                'approved_by_title'  => $data['approved_by_title'] ?? null,
            ]);
        });

        return redirect()->route('letters.show', $letter)->with('success', 'Letter created.');
    }

    public function edit(Letter $letter)
    {
        $letter->load('template');

        return view('admin.letters.edit', compact('letter'));
    }

    public function update(Request $request, Letter $letter)
    {
        if ($letter->approval_status === 'approved') {
            return back()->with('error', 'Approved letters cannot be updated.');
        }

        $data = $request->validate([
            'recipient_name'    => ['nullable', 'string', 'max:255'],
            'recipient_title'   => ['nullable', 'string', 'max:255'],
            'recipient_company' => ['nullable', 'string', 'max:255'],
            'subject'           => ['nullable', 'string', 'max:255'],
            'case_number'       => ['nullable', 'string', 'max:60'],
            'body'              => ['required', 'string'],
            'cc'                => ['nullable', 'string', 'max:255'],
            'send_to_applicant' => ['nullable', 'boolean'],
            'send_to_respondent'=> ['nullable', 'boolean'],
            'approved_by_name'  => ['nullable', 'string', 'max:255'],
            'approved_by_title' => ['nullable', 'string', 'max:255'],
        ]);

        $sendToApplicant = array_key_exists('send_to_applicant', $data)
            ? (bool) $data['send_to_applicant']
            : ($letter->send_to_applicant ?? true);
        $sendToRespondent = array_key_exists('send_to_respondent', $data)
            ? (bool) $data['send_to_respondent']
            : ($letter->send_to_respondent ?? true);

        if (!$sendToApplicant && !$sendToRespondent) {
            return back()
                ->withErrors(['send_to_applicant' => __('letters.form.delivery_required')])
                ->withInput();
        }

        $recipientName = trim((string) ($data['recipient_name'] ?? ''));
        if ($recipientName === '') {
            $targets = [];
            $caseParty = null;
            $caseNumber = trim((string) ($data['case_number'] ?? $letter->case_number ?? ''));
            if ($caseNumber !== '') {
                $caseParty = DB::table('court_cases as c')
                    ->leftJoin('applicants as a', 'a.id', '=', 'c.applicant_id')
                    ->select(
                        'c.respondent_name',
                        DB::raw("TRIM(CONCAT(COALESCE(a.first_name,''), ' ', COALESCE(a.last_name,''))) as applicant_name")
                    )
                    ->where('c.case_number', $caseNumber)
                    ->first();
            }
            if ($sendToApplicant) {
                $targets[] = trim((string) ($caseParty->applicant_name ?? '')) ?: __('letters.form.deliver_applicant');
            }
            if ($sendToRespondent) {
                $targets[] = trim((string) ($caseParty->respondent_name ?? '')) ?: __('letters.form.deliver_respondent');
            }
            $recipientName = implode(', ', $targets);
        }
        if ($recipientName === '') {
            $recipientName = (string) ($letter->recipient_name ?? '');
        }
        $data['recipient_name'] = $recipientName;
        $resolvedCaseNumber = array_key_exists('case_number', $data)
            ? (trim((string) ($data['case_number'] ?? '')) ?: null)
            : ($letter->case_number ?: null);
        $data['body'] = $this->applyTemplatePlaceholders(
            (string) ($data['body'] ?? ''),
            $resolvedCaseNumber
        );

        $letter->load('template');

        $baseSubject = trim($data['subject'] ?? $letter->template->title);
        $subjectValue = $baseSubject === '' ? null : $baseSubject;

        $letter->update([
            'recipient_name'    => $data['recipient_name'],
            'recipient_title'   => $data['recipient_title'] ?? null,
            'recipient_company' => $data['recipient_company'] ?? null,
            'subject'           => $subjectValue,
            'case_number'       => $resolvedCaseNumber,
            'body'              => $data['body'],
            'cc'                => $data['cc'] ?? null,
            'send_to_applicant'  => $sendToApplicant,
            'send_to_respondent' => $sendToRespondent,
            'approved_by_name'   => $data['approved_by_name'] ?? null,
            'approved_by_title'  => $data['approved_by_title'] ?? null,
        ]);

        return redirect()->route('letters.show', $letter)->with('success', 'Letter updated.');
    }

    public function destroy(Letter $letter)
    {
        if ($letter->approval_status === 'approved') {
            return back()->with('error', 'Approved letters cannot be deleted.');
        }

        $letter->delete();

        return redirect()->route('letters.index')->with('success', 'Letter deleted.');
    }

    public function show(Letter $letter)
    {
        $letter->load(['template', 'author']);

        return view('admin.letters.preview', [
            'letter'   => $letter,
            'template' => $letter->template,
        ]);
    }

    /**
     * Allow applicants/respondents (by case) to view a letter preview without /admin prefix.
     */
    public function publicPreview(Request $request, Letter $letter)
    {
        $letter->load(['template', 'author']);

        $sendToApplicant = $letter->send_to_applicant ?? true;
        $sendToRespondent = $letter->send_to_respondent ?? true;
        $case = null;
        if ($letter->case_number) {
            $case = CourtCase::where('case_number', $letter->case_number)->first();
        }

        $applicantId = auth('applicant')->id();
        $respondentId = auth('respondent')->id();
        $isAdmin = auth()->check();

        $authorized = URL::hasValidSignature($request);

        // Admin/staff (already signed in via default guard)
        if ($isAdmin) {
            $authorized = true;
        }

        // Applicant owns the case
        if (
            !$authorized
            && $sendToApplicant
            && $case
            && $applicantId
            && (int) $case->applicant_id === (int) $applicantId
        ) {
            $authorized = true;
        }

        // Respondent has viewed this case (association via respondent_case_views)
        if (!$authorized && $sendToRespondent && $case && $respondentId) {
            $hasAccess = DB::table('respondent_case_views')
                ->where('respondent_id', $respondentId)
                ->where('case_id', $case->id)
                ->exists();
            if ($hasAccess) {
                $authorized = true;
            }
        }

        abort_unless($authorized, 403);

        return view('admin.letters.preview', [
            'letter'   => $letter,
            'template' => $letter->template,
        ]);
    }

    public function approve(Request $request, Letter $letter)
    {
        $user = $request->user();

        $data = $request->validate([
            'status' => 'required|in:approved,returned,rejected',
        ]);

        $letter->update([
            'approved_by_name'  => $user?->name,
            'approved_by_title' => $user?->position,
            'approval_status'   => $data['status'],
        ]);

        if ($data['status'] === 'approved') {
            $this->notifyPartiesOfApprovedLetter($letter);
            $this->createSystemCaseMessage($letter, $user?->id);
        }

        return redirect()->route('letters.index')->with('success', "Letter {$data['status']}.");
    }

    /**
     * Email the approved letter preview to the applicant and any respondents tied to the case number.
     */
    protected function notifyPartiesOfApprovedLetter(Letter $letter): void
    {
        if (!$letter->case_number) {
            return;
        }

        $sendToApplicant = $letter->send_to_applicant ?? true;
        $sendToRespondent = $letter->send_to_respondent ?? true;
        $case = CourtCase::with('applicant')->where('case_number', $letter->case_number)->first();

        $recipients = collect();

        if ($sendToApplicant && $case?->applicant?->email) {
            $recipients->push([
                'email' => $case->applicant->email,
                'name'  => $case->applicant->full_name ?? $case->applicant->email,
            ]);
        }

        // Respondents who have viewed the case
        if ($sendToRespondent && $case) {
            $respondents = DB::table('respondent_case_views as v')
                ->join('respondents as r', 'r.id', '=', 'v.respondent_id')
                ->where('v.case_id', $case->id)
                ->select('r.email', 'r.first_name', 'r.middle_name', 'r.last_name')
                ->distinct()
                ->get();

            foreach ($respondents as $resp) {
                if (!$resp->email) {
                    continue;
                }
                $recipients->push([
                    'email' => $resp->email,
                    'name'  => trim("{$resp->first_name} {$resp->middle_name} {$resp->last_name}") ?: $resp->email,
                ]);
            }
        }

        // Fallback: respondents who submitted responses with this case number
        if ($sendToRespondent) {
            $responses = DB::table('respondent_responses as rr')
                ->join('respondents as r', 'r.id', '=', 'rr.respondent_id')
                ->where('rr.case_number', $letter->case_number)
                ->select('r.email', 'r.first_name', 'r.middle_name', 'r.last_name')
                ->distinct()
                ->get();

            foreach ($responses as $resp) {
                if (!$resp->email) {
                    continue;
                }
                $recipients->push([
                    'email' => $resp->email,
                    'name'  => trim("{$resp->first_name} {$resp->middle_name} {$resp->last_name}") ?: $resp->email,
                ]);
            }
        }

        $uniqueRecipients = $recipients->unique('email');

        foreach ($uniqueRecipients as $recipient) {
            Mail::to($recipient['email'])->send(
                new LetterApprovedMail($letter, $case, $recipient['name'])
            );
        }
    }

    /**
     * Create an in-app case message so applicants/respondents see an approval notification.
     */
    protected function createSystemCaseMessage(Letter $letter, ?int $senderUserId = null): void
    {
        if (!$letter->case_number) {
            return;
        }

        $caseId = DB::table('court_cases')
            ->where('case_number', $letter->case_number)
            ->value('id');

        if (!$caseId) {
            return;
        }

        $previewUrl = Route::has('letters.case-preview')
            ? URL::signedRoute('letters.case-preview', ['letter' => $letter->getKey()])
            : URL::to('/case-letters/' . $letter->getKey());

        $subject = $letter->subject ?: optional($letter->template)->title ?: __('cases.notifications.letter');
        $body = __('cases.notifications.approved_letter_subject', ['subject' => $subject])
            . "\n"
            . __('cases.notifications.preview_link', ['url' => $previewUrl]);

        DB::table('case_messages')->insert([
            'case_id'            => $caseId,
            'sender_applicant_id'=> null,
            'sender_user_id'     => $senderUserId,
            'body'               => $body,
            'created_at'         => now(),
            'updated_at'         => now(),
        ]);
    }

    private function applyTemplatePlaceholders(string $content, ?string $caseNumber): string
    {
        if ($content === '') {
            return $content;
        }

        $caseNumber = trim((string) $caseNumber);
        if ($caseNumber === '') {
            return $content;
        }

        $case = CourtCase::with('applicant')
            ->where('case_number', $caseNumber)
            ->first();

        if (!$case) {
            return $content;
        }

        $applicant = $case->applicant;
        $map = [
            '{case_number}' => (string) ($case->case_number ?? ''),
            '{case_code}' => (string) ($case->code ?? ''),
            '{case_title}' => (string) ($case->title ?? ''),
            '{case_filing_date}' => (string) ($case->filing_date ? \App\Support\EthiopianDate::format($case->filing_date) : ''),
            '{case_status}' => (string) ($case->status ?? ''),
            '{respondent_name}' => (string) ($case->respondent_name ?? ''),
            '{applicant_name}' => trim((string) ($applicant?->full_name ?? '')),
            '{applicant_first_name}' => (string) ($applicant?->first_name ?? ''),
            '{applicant_middle_name}' => (string) ($applicant?->middle_name ?? ''),
            '{applicant_last_name}' => (string) ($applicant?->last_name ?? ''),
            '{applicant_email}' => (string) ($applicant?->email ?? ''),
            '{applicant_phone}' => (string) ($applicant?->phone ?? ''),
            '{applicant_address}' => (string) ($applicant?->address ?? ''),
        ];

        return strtr($content, $map);
    }
}
