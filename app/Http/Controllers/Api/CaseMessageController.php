<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\HandlesCaseAuthorization;
use App\Http\Controllers\Controller;
use App\Models\Applicant;
use App\Models\CourtCase;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CaseMessageController extends Controller
{
    use HandlesCaseAuthorization;

    public function index(Request $request, CourtCase $case)
    {
        $actor = $request->user();
        $this->assertCanViewCase($actor, $case);

        $messages = DB::table('case_messages as m')
            ->leftJoin('applicants as a', 'a.id', '=', 'm.sender_applicant_id')
            ->leftJoin('users as u', 'u.id', '=', 'm.sender_user_id')
            ->where('m.case_id', $case->id)
            ->orderBy('m.created_at')
            ->get([
                'm.id',
                'm.body',
                'm.created_at',
                'm.updated_at',
                DB::raw("CASE WHEN m.sender_user_id IS NOT NULL THEN 'staff' ELSE 'applicant' END as sender_type"),
                DB::raw("COALESCE(NULLIF(TRIM(CONCAT(a.first_name, ' ', a.last_name)), ''), u.name) as sender_name"),
            ]);

        return response()->json([
            'ok' => true,
            'messages' => $messages,
        ]);
    }

    public function store(Request $request, CourtCase $case)
    {
        $actor = $request->user();
        $this->assertCanViewCase($actor, $case);

        $data = $request->validate([
            'body' => ['required', 'string', 'max:5000'],
        ]);

        $payload = [
            'case_id' => $case->id,
            'body' => $data['body'],
            'created_at' => now(),
            'updated_at' => now(),
        ];

        if ($actor instanceof Applicant) {
            abort_unless((int) $case->applicant_id === (int) $actor->id, 403, 'Not your case.');
            $payload['sender_applicant_id'] = $actor->id;
        } elseif ($actor instanceof User) {
            abort_unless($actor->canDo('cases.view'), 403, 'Not authorized to message on this case.');
            $payload['sender_user_id'] = $actor->id;
        } else {
            abort(403, 'Not authorized to message on this case.');
        }

        $id = DB::table('case_messages')->insertGetId($payload);

        return response()->json([
            'ok' => true,
            'message' => [
                'id' => $id,
                'body' => $data['body'],
            ],
        ], 201);
    }
}
