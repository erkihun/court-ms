<?php

namespace App\Http\Controllers\Respondent;

use App\Http\Controllers\Controller;
use App\Models\Respondent;
use App\Models\RespondentResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Session;

class ResponseController extends Controller
{
    public function index(Request $request)
    {
        $respondentId = $this->currentRespondentId();
        Session::put('acting_as_respondent', true);

        $responses = RespondentResponse::where('respondent_id', $respondentId)
            ->orderByDesc('created_at')
            ->get();

        return view('applicant.respondent.responses.index', compact('responses'));
    }

    public function create()
    {
        return view('applicant.respondent.responses.create');
    }

    public function store(Request $request)
    {
        $applicant = Auth::guard('applicant')->user();
        abort_unless($applicant, 403);
        $respondentId = $this->currentRespondentId();
        Session::put('acting_as_respondent', true);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'case_number' => ['nullable', 'string', 'max:64'],
            'pdf' => ['required', 'file', 'mimes:pdf', 'max:5120'],
        ]);

        $this->assertNotOwnCase($data['case_number'] ?? null, (int) $applicant->id);

        $path = $request->file('pdf')->store('respondent/responses', 'public');

        $response = RespondentResponse::create([
            'respondent_id' => $respondentId,
            'case_number' => $data['case_number'] ?? null,
            'title' => $data['title'],
            'description' => $data['description'],
            'pdf_path' => $path,
        ]);

        return redirect()->route('respondent.responses.show', $response);
    }

    public function show(RespondentResponse $response)
    {
        $this->authorizeOwnership($response);

        return view('applicant.respondent.responses.show', compact('response'));
    }

    public function edit(RespondentResponse $response)
    {
        $this->authorizeOwnership($response);

        return view('applicant.respondent.responses.edit', compact('response'));
    }

    public function update(Request $request, RespondentResponse $response)
    {
        $this->authorizeOwnership($response);
        $applicant = Auth::guard('applicant')->user();
        abort_unless($applicant, 403);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'case_number' => ['nullable', 'string', 'max:64'],
            'pdf' => ['nullable', 'file', 'mimes:pdf', 'max:5120'],
        ]);

        $this->assertNotOwnCase($data['case_number'] ?? null, (int) $applicant->id);

        if ($request->hasFile('pdf')) {
            Storage::disk('public')->delete($response->pdf_path);
            $path = $request->file('pdf')->store('respondent/responses', 'public');
            $response->pdf_path = $path;
        }

        $response->fill([
            'case_number' => $data['case_number'] ?? null,
            'title' => $data['title'],
            'description' => $data['description'],
        ]);
        $response->save();

        return redirect()->route('respondent.responses.show', $response);
    }

    public function destroy(RespondentResponse $response)
    {
        $this->authorizeOwnership($response);
        Storage::disk('public')->delete($response->pdf_path);
        $response->delete();

        return redirect()->route('respondent.responses.index');
    }

    private function authorizeOwnership(RespondentResponse $response): void
    {
        abort_if($response->respondent_id !== $this->currentRespondentId(), 403);
    }

    private function currentRespondentId(): int
    {
        // Respondent actions use the applicant guard while acting-as-respondent.
        $applicant = Auth::guard('applicant')->user();
        abort_unless($applicant, 403);

        $respondent = Respondent::where('email', $applicant->email)->first();
        if (!$respondent) {
            $phone = $applicant->phone ?? 'resp_' . substr(md5((string) microtime(true)), 0, 12);
            if (Respondent::where('phone', $phone)->where('email', '!=', $applicant->email)->exists()) {
                $phone = 'resp_' . substr(md5(uniqid('', true)), 0, 12);
            }

            $respondent = Respondent::create([
                'first_name'        => $applicant->first_name ?? '',
                'middle_name'       => $applicant->middle_name ?? '',
                'last_name'         => $applicant->last_name ?? '',
                'gender'            => $applicant->gender ?? null,
                'position'          => $applicant->position ?? '',
                'organization_name' => $applicant->organization_name ?? '',
                'address'           => $applicant->address ?? '',
                'national_id'       => $this->applicantNationalId($applicant),
                'phone'             => $phone,
                'email'             => $applicant->email,
                // Use the applicant's hashed password to keep credentials aligned.
                'password'          => $applicant->password,
            ]);
        } else {
            $dirty = false;
            $maybeNationalId = $this->applicantNationalId($applicant);
            if (!$respondent->national_id && $maybeNationalId) {
                $respondent->national_id = $maybeNationalId;
                $dirty = true;
            }
            if ($dirty) {
                $respondent->save();
            }
        }

        return (int) $respondent->id;
    }

    private function assertNotOwnCase(?string $caseNumber, int $applicantId): void
    {
        if (!$caseNumber) {
            return;
        }

        $case = DB::table('court_cases')
            ->select('applicant_id')
            ->where('case_number', $caseNumber)
            ->first();

        if ($case && (int) $case->applicant_id === $applicantId) {
            abort(403, 'You cannot submit a response to your own case.');
        }
    }

    private function applicantNationalId($applicant): ?string
    {
        // Normalize to digits-only and trim to 16 characters to avoid DB length violations.
        $digits = preg_replace('/\D/', '', (string) ($applicant->getRawOriginal('national_id_number') ?? $applicant->national_id_number ?? ''));
        if ($digits === '') {
            return null;
        }
        return substr($digits, 0, 16);
    }
}
