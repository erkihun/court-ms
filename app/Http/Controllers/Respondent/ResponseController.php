<?php

namespace App\Http\Controllers\Respondent;

use App\Http\Controllers\Controller;
use App\Models\RespondentResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ResponseController extends Controller
{
    public function index(Request $request)
    {
        $responses = RespondentResponse::where('respondent_id', Auth::guard('respondent')->id())
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
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'case_number' => ['nullable', 'string', 'max:64'],
            'pdf' => ['required', 'file', 'mimes:pdf', 'max:5120'],
        ]);

        $path = $request->file('pdf')->store('respondent/responses', 'public');

        $response = RespondentResponse::create([
            'respondent_id' => Auth::guard('respondent')->id(),
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

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'case_number' => ['nullable', 'string', 'max:64'],
            'pdf' => ['nullable', 'file', 'mimes:pdf', 'max:5120'],
        ]);

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
        abort_if($response->respondent_id !== Auth::guard('respondent')->id(), 403);
    }
}
