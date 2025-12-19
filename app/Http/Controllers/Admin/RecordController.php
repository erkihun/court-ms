<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CourtCase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class RecordController extends Controller
{
    public function index()
    {
        $cases = CourtCase::select('id', 'case_number', 'title', 'status', 'filing_date')
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        return view('admin.recordes.index', compact('cases'));
    }

    public function show(CourtCase $case)
    {
        $data = $this->recordData($case);

        return view('admin.recordes.record', $data);
    }

    public function pdf(CourtCase $case)
    {
        $data = $this->recordData($case);
        $safeCase = Str::of($case->case_number ?? $case->id)->replace(['/', '\\'], '-')->slug('-');
        $data['pdfFilename'] = 'case-record-' . ($safeCase ?: $case->id) . '.pdf';

        return view('admin.recordes.record-pdf', $data);
    }

    private function recordData(CourtCase $case): array
    {
        $letters = DB::table('letters as l')
            ->leftJoin('users as u', 'u.id', '=', 'l.user_id')
            ->select('l.*', 'u.name as author_name')
            ->where('l.case_number', $case->case_number)
            ->orderByDesc('l.created_at')
            ->get();

        $respondentResponses = DB::table('respondent_responses')
            ->where('case_number', $case->case_number)
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($resp) {
                $resp->pdf_embed = null;
                if (empty($resp->pdf_path)) {
                    return $resp;
                }

                $content = null;
                if (Storage::disk('private')->exists($resp->pdf_path)) {
                    $content = Storage::disk('private')->get($resp->pdf_path);
                } elseif (Storage::disk('public')->exists($resp->pdf_path)) {
                    $content = Storage::disk('public')->get($resp->pdf_path);
                } elseif (file_exists($resp->pdf_path)) {
                    $content = @file_get_contents($resp->pdf_path);
                }

                if ($content) {
                    $resp->pdf_embed = [
                        'mime' => 'application/pdf',
                        'data' => base64_encode($content),
                    ];
                }

                return $resp;
            });

        $hearings = DB::table('case_hearings')
            ->where('case_id', $case->id)
            ->orderBy('hearing_at')
            ->get();

        $benchNotes = DB::table('bench_notes as b')
            ->leftJoin('users as u', 'u.id', '=', 'b.user_id')
            ->leftJoin('users as j1', 'j1.id', '=', 'b.judge_one_id')
            ->leftJoin('users as j2', 'j2.id', '=', 'b.judge_two_id')
            ->leftJoin('users as j3', 'j3.id', '=', 'b.judge_three_id')
            ->select(
                'b.*',
                'u.name as author',
                'j1.name as judge_one_name',
                'j1.signature_path as judge_one_signature',
                DB::raw('COALESCE(j1.position, "") as judge_one_title'),
                'j2.name as judge_two_name',
                'j2.signature_path as judge_two_signature',
                DB::raw('COALESCE(j2.position, "") as judge_two_title'),
                'j3.name as judge_three_name',
                'j3.signature_path as judge_three_signature',
                DB::raw('COALESCE(j3.position, "") as judge_three_title')
            )
            ->where('b.case_id', $case->id)
            ->orderByDesc('b.created_at')
            ->get();

        $decision = DB::table('decisions')
            ->where('court_case_id', $case->id)
            ->orderByDesc('decision_date')
            ->orderByDesc('id')
            ->first();

        $files = DB::table('case_files')
            ->where('case_id', $case->id)
            ->orderByDesc('created_at')
            ->get();

        $evidences = DB::table('case_evidences')
            ->where('case_id', $case->id)
            ->orderByDesc('created_at')
            ->get();

        $firstEvidenceEmbed = null;
        foreach ($evidences as $ev) {
            $path = $ev->file_path ?? $ev->path ?? null;
            if (!$path) {
                continue;
            }

            $content = null;
            if (Storage::disk('private')->exists($path)) {
                $content = Storage::disk('private')->get($path);
            } elseif (Storage::disk('public')->exists($path)) {
                $content = Storage::disk('public')->get($path);
            } elseif (file_exists($path)) {
                $content = @file_get_contents($path);
            }

            if ($content) {
                $mime = $ev->mime ?? 'application/pdf';
                $firstEvidenceEmbed = [
                    'mime' => $mime,
                    'data' => base64_encode($content),
                    'title' => $ev->title ?? 'Document',
                ];
                break;
            }
        }

        $messages = DB::table('case_messages as m')
            ->leftJoin('users as u', 'u.id', '=', 'm.sender_user_id')
            ->leftJoin('applicants as a', 'a.id', '=', 'm.sender_applicant_id')
            ->select(
                'm.id',
                'm.body',
                'm.created_at',
                'm.sender_user_id',
                'm.sender_applicant_id',
                'u.name as user_name',
                DB::raw("TRIM(COALESCE(a.first_name,'') || ' ' || COALESCE(a.middle_name,'') || ' ' || COALESCE(a.last_name,'')) as applicant_name")
            )
            ->where('m.case_id', $case->id)
            ->orderByDesc('m.created_at')
            ->get();

        $statusLogs = DB::table('case_status_logs')
            ->where('case_id', $case->id)
            ->orderBy('created_at')
            ->get();

        $witnesses = DB::table('case_witnesses')
            ->where('case_id', $case->id)
            ->orderBy('id')
            ->get();

        $assignedUser = null;
        $assignedTeams = collect();
        if (!empty($case->assigned_user_id)) {
            $assignedUser = DB::table('users')->where('id', $case->assigned_user_id)->first();
            $assignedTeams = DB::table('team_user as tu')
                ->join('teams as t', 't.id', '=', 'tu.team_id')
                ->where('tu.user_id', $case->assigned_user_id)
                ->pluck('t.name');
        }

        $closedAt = optional(
            $statusLogs->filter(function ($log) {
                return in_array($log->to_status, ['closed', 'dismissed'], true);
            })->sortByDesc('created_at')->first()
        )->created_at;

        $case->loadMissing([
            'applicant:id,first_name,middle_name,last_name,email,phone',
            'caseType:id,name,prefix',
        ]);

        return [
            'case' => $case,
            'letters' => $letters,
            'respondentResponses' => $respondentResponses,
            'hearings' => $hearings,
            'benchNotes' => $benchNotes,
            'decision' => $decision,
            'files' => $files,
            'evidences' => $evidences,
            'messages' => $messages,
            'statusLogs' => $statusLogs,
            'witnesses' => $witnesses,
            'assignedUser' => $assignedUser,
            'assignedTeams' => $assignedTeams,
            'closedAt' => $closedAt,
            'firstEvidenceEmbed' => $firstEvidenceEmbed,
        ];
    }
}
