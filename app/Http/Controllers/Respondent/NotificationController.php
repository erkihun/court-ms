<?php

namespace App\Http\Controllers\Respondent;

use App\Http\Controllers\Controller;
use App\Models\Respondent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NotificationController extends Controller
{
    public function markOne(Request $request)
    {
        session(['acting_as_respondent' => true]);
        $respondentId = $this->resolveRespondentId();
        abort_if(!$respondentId, 403);

        $data = $request->validate([
            'type'     => 'required|string',
            'sourceId' => 'required|integer',
        ]);

        DB::table('respondent_notification_reads')->updateOrInsert(
            [
                'respondent_id' => $respondentId,
                'type'          => $data['type'],
                'source_id'     => $data['sourceId'],
            ],
            [
                'seen_at'    => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        return back()->with('success', 'Marked as seen.');
    }

    public function markAll()
    {
        session(['acting_as_respondent' => true]);
        $respondentId = $this->resolveRespondentId();
        abort_if(!$respondentId, 403);

        $now = now();
        $tablesExist = DB::getSchemaBuilder()->hasTable('respondent_notification_reads')
            && DB::getSchemaBuilder()->hasTable('respondent_case_views')
            && DB::getSchemaBuilder()->hasTable('case_hearings')
            && DB::getSchemaBuilder()->hasTable('case_status_logs')
            && DB::getSchemaBuilder()->hasTable('case_messages');

        if (!$tablesExist) {
            return back()->with('success', 'Marked as seen.');
        }

        $caseIds = DB::table('respondent_case_views')
            ->where('respondent_id', $respondentId)
            ->pluck('case_id')
            ->all();

        $types = [
            'respondent_view' => DB::table('respondent_case_views as v')->select('v.id as source_id')
                ->where('v.respondent_id', $respondentId)
                ->whereNotExists(function ($q) use ($respondentId) {
                    $q->from('respondent_notification_reads as r')
                        ->whereColumn('r.source_id', 'v.id')
                        ->where('r.type', 'respondent_view')
                        ->where('r.respondent_id', $respondentId);
                }),
            'hearing' => DB::table('case_hearings as h')->select('h.id as source_id')
                ->whereIn('h.case_id', $caseIds)
                ->whereNotExists(function ($q) use ($respondentId) {
                    $q->from('respondent_notification_reads as r')
                        ->whereColumn('r.source_id', 'h.id')
                        ->where('r.type', 'hearing')
                        ->where('r.respondent_id', $respondentId);
                }),
            'status' => DB::table('case_status_logs as s')->select('s.id as source_id')
                ->whereIn('s.case_id', $caseIds)
                ->whereNotExists(function ($q) use ($respondentId) {
                    $q->from('respondent_notification_reads as r')
                        ->whereColumn('r.source_id', 's.id')
                        ->where('r.type', 'status')
                        ->where('r.respondent_id', $respondentId);
                }),
            'message' => DB::table('case_messages as m')->select('m.id as source_id')
                ->whereIn('m.case_id', $caseIds)
                ->whereNotExists(function ($q) use ($respondentId) {
                    $q->from('respondent_notification_reads as r')
                        ->whereColumn('r.source_id', 'm.id')
                        ->where('r.type', 'message')
                        ->where('r.respondent_id', $respondentId);
                }),
        ];

        $rows = [];
        foreach ($types as $type => $builder) {
            foreach ($builder->pluck('source_id') as $id) {
                $rows[] = [
                    'respondent_id' => $respondentId,
                    'type'          => $type,
                    'source_id'     => $id,
                    'seen_at'       => $now,
                    'created_at'    => $now,
                    'updated_at'    => $now,
                ];
            }
        }

        if (!empty($rows)) {
            DB::table('respondent_notification_reads')->insert($rows);
        }

        return back()->with('success', 'All marked as seen.');
    }

    private function resolveRespondentId(): ?int
    {
        if (auth('respondent')->check()) {
            return auth('respondent')->id();
        }

        $applicant = auth('applicant')->user();
        if (!$applicant) {
            return null;
        }

        $resp = Respondent::where('email', $applicant->email)->first();
        return $resp?->id;
    }
}
