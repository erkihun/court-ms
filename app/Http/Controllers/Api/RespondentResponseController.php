<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Respondent;
use App\Models\RespondentResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RespondentResponseController extends Controller
{
    public function index(Request $request)
    {
        $actor = $request->user();
        abort_unless($actor instanceof Respondent, 403, 'Only respondents can view responses.');

        $responses = RespondentResponse::where('respondent_id', $actor->id)
            ->orderByDesc('created_at')
            ->get(['id', 'title', 'case_number', 'description', 'created_at']);

        return response()->json([
            'ok' => true,
            'data' => $responses,
        ]);
    }

    public function show(Request $request, RespondentResponse $response)
    {
        $actor = $request->user();
        abort_unless($actor instanceof Respondent, 403, 'Only respondents can view responses.');
        abort_unless((int) $response->respondent_id === (int) $actor->id, 403, 'Not your response.');

        return response()->json([
            'ok' => true,
            'data' => [
                'id' => $response->id,
                'title' => $response->title,
                'case_number' => $response->case_number,
                'description' => $response->description,
                'created_at' => $response->created_at,
            ],
        ]);
    }

    public function store(Request $request)
    {
        $actor = $request->user();
        abort_unless($actor instanceof Respondent, 403, 'Only respondents can submit responses.');

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'case_number' => ['nullable', 'string', 'max:64'],
            'pdf' => ['required', 'file', 'mimes:pdf', 'max:5120'],
        ]);

        if (!empty($data['case_number'])) {
            $status = DB::table('court_cases')
                ->where('case_number', $data['case_number'])
                ->value('status');

            if ($status === 'closed') {
                throw ValidationException::withMessages([
                    'case_number' => ['This case is closed; responses are not allowed.'],
                ]);
            }
        }

        $path = $request->file('pdf')->store('respondent/responses', 'private');

        $response = RespondentResponse::create([
            'respondent_id' => $actor->id,
            'case_number' => $data['case_number'] ?? null,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'pdf_path' => $path,
        ]);

        return response()->json([
            'ok' => true,
            'data' => [
                'id' => $response->id,
                'title' => $response->title,
                'case_number' => $response->case_number,
                'created_at' => $response->created_at,
            ],
        ], 201);
    }
}
