<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminNotificationController extends Controller
{
    public function index(Request $request)
    {
        $uid = auth()->id();
        abort_if(!$uid, 403);

        // Applicant -> Admin messages (last 14 days)
        $msgs = DB::table('case_messages as m')
            ->join('court_cases as c', 'c.id', '=', 'm.case_id')
            ->select('m.id', 'm.body', 'm.created_at', 'c.case_number', 'c.id as case_id')
            ->whereNotNull('m.sender_applicant_id')
            ->where('m.created_at', '>=', now()->subDays(14))
            ->whereNotExists(function ($q) use ($uid) {
                $q->from('admin_notification_reads as nr')
                    ->whereColumn('nr.source_id', 'm.id')
                    ->where('nr.type', 'message')
                    ->where('nr.user_id', $uid);
            })
            ->orderByDesc('m.created_at')
            ->paginate(10, ['*'], 'msgs_page');

        // New & unassigned cases (last 14 days)
        $cases = DB::table('court_cases as c')
            ->select('c.id', 'c.case_number', 'c.title', 'c.created_at')
            ->where('c.status', 'pending')
            ->whereNull('c.assigned_user_id')
            ->where('c.created_at', '>=', now()->subDays(14))
            ->whereNotExists(function ($q) use ($uid) {
                $q->from('admin_notification_reads as nr')
                    ->whereColumn('nr.source_id', 'c.id')
                    ->where('nr.type', 'case')
                    ->where('nr.user_id', $uid);
            })
            ->orderByDesc('c.created_at')
            ->paginate(10, ['*'], 'cases_page');

        // Upcoming hearings for my assigned cases (next 14 days)
        $hearings = DB::table('case_hearings as h')
            ->join('court_cases as c', 'c.id', '=', 'h.case_id')
            ->select('h.id', 'h.hearing_at', 'h.location', 'h.type', 'c.id as case_id', 'c.case_number')
            ->where('c.assigned_user_id', $uid)
            ->whereBetween('h.hearing_at', [now(), now()->addDays(14)])
            ->whereNotExists(function ($q) use ($uid) {
                $q->from('admin_notification_reads as nr')
                    ->whereColumn('nr.source_id', 'h.id')
                    ->where('nr.type', 'hearing')
                    ->where('nr.user_id', $uid);
            })
            ->orderBy('h.hearing_at')
            ->paginate(10, ['*'], 'hearings_page');

        return view('admin.notifications.index', compact('msgs', 'cases', 'hearings'));
    }

    public function markOne(Request $request)
    {
        $uid = auth()->id();
        abort_if(!$uid, 403);

        $data = $request->validate([
            'type'     => 'required|in:message,case,hearing',
            'sourceId' => 'required|integer',
        ]);

        DB::table('admin_notification_reads')->updateOrInsert(
            ['user_id' => $uid, 'type' => $data['type'], 'source_id' => $data['sourceId']],
            ['seen_at' => now(), 'updated_at' => now(), 'created_at' => now()]
        );

        return back()->with('success', 'Marked as seen.');
    }

    public function markAll()
    {
        $uid = auth()->id();
        abort_if(!$uid, 403);
        $now = now();

        // collect unseen IDs per type
        $msgIds = DB::table('case_messages as m')
            ->whereNotNull('m.sender_applicant_id')
            ->where('m.created_at', '>=', now()->subDays(14))
            ->whereNotExists(function ($q) use ($uid) {
                $q->from('admin_notification_reads as nr')
                    ->whereColumn('nr.source_id', 'm.id')
                    ->where('nr.type', 'message')
                    ->where('nr.user_id', $uid);
            })->pluck('m.id')->all();

        $caseIds = DB::table('court_cases as c')
            ->where('c.status', 'pending')->whereNull('c.assigned_user_id')
            ->where('c.created_at', '>=', now()->subDays(14))
            ->whereNotExists(function ($q) use ($uid) {
                $q->from('admin_notification_reads as nr')
                    ->whereColumn('nr.source_id', 'c.id')
                    ->where('nr.type', 'case')
                    ->where('nr.user_id', $uid);
            })->pluck('c.id')->all();

        $hearingIds = DB::table('case_hearings as h')
            ->join('court_cases as c', 'c.id', '=', 'h.case_id')
            ->where('c.assigned_user_id', $uid)
            ->whereBetween('h.hearing_at', [now(), now()->addDays(14)])
            ->whereNotExists(function ($q) use ($uid) {
                $q->from('admin_notification_reads as nr')
                    ->whereColumn('nr.source_id', 'h.id')
                    ->where('nr.type', 'hearing')
                    ->where('nr.user_id', $uid);
            })->pluck('h.id')->all();

        $rows = [];
        foreach ($msgIds as $id)     $rows[] = ['user_id' => $uid, 'type' => 'message', 'source_id' => $id, 'seen_at' => $now, 'created_at' => $now, 'updated_at' => $now];
        foreach ($caseIds as $id)    $rows[] = ['user_id' => $uid, 'type' => 'case', 'source_id' => $id, 'seen_at' => $now, 'created_at' => $now, 'updated_at' => $now];
        foreach ($hearingIds as $id) $rows[] = ['user_id' => $uid, 'type' => 'hearing', 'source_id' => $id, 'seen_at' => $now, 'created_at' => $now, 'updated_at' => $now];

        if (!empty($rows)) {
            // upsert avoids duplicate-key issues
            DB::table('admin_notification_reads')->upsert(
                $rows,
                ['user_id', 'type', 'source_id'],
                ['seen_at', 'updated_at']
            );
        }

        return back()->with('success', 'All marked as seen.');
    }
}
