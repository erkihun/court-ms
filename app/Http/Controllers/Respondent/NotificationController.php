<?php

namespace App\Http\Controllers\Respondent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NotificationController extends Controller
{
    public function markOne(Request $request)
    {
        $respondentId = auth('respondent')->id();
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
        $respondentId = auth('respondent')->id();
        abort_if(!$respondentId, 403);

        $now = now();
        $viewIds = DB::table('respondent_case_views as v')
            ->where('v.respondent_id', $respondentId)
            ->whereNotExists(function ($q) use ($respondentId) {
                $q->from('respondent_notification_reads as r')
                    ->whereColumn('r.source_id', 'v.id')
                    ->where('r.type', 'respondent_view')
                    ->where('r.respondent_id', $respondentId);
            })
            ->pluck('v.id')
            ->all();

        if ($viewIds) {
            $rows = [];
            foreach ($viewIds as $id) {
                $rows[] = [
                    'respondent_id' => $respondentId,
                    'type'          => 'respondent_view',
                    'source_id'     => $id,
                    'seen_at'       => $now,
                    'created_at'    => $now,
                    'updated_at'    => $now,
                ];
            }

            DB::table('respondent_notification_reads')->insert($rows);
        }

        return back()->with('success', 'All marked as seen.');
    }
}
