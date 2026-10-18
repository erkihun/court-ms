<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Models\User;
use App\Services\AdminNotificationFeed;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class AdminNotificationController extends Controller
{
    public function count(AdminNotificationFeed $feed): JsonResponse
    {
        $user = auth()->user();

        return response()->json(['count' => $user instanceof User ? $feed->count($user) : 0]);
    }

    /**
     * The bell dropdown: the newest unseen items of each kind.
     */
    public function feed(AdminNotificationFeed $feed): JsonResponse
    {
        $user = auth()->user();

        return response()->json($user instanceof User ? $feed->latest($user) : ['count' => 0, 'groups' => []]);
    }

    public function index(AdminNotificationFeed $feed): Response
    {
        $user = auth()->user();
        abort_unless($user instanceof User && $feed->canSee($user), 403);

        $pageNames = [
            'message' => 'msgs_page',
            'case' => 'cases_page',
            'hearing' => 'hearings_page',
            'respondent_view' => 'respondent_views_page',
        ];

        return Inertia::render('Admin/Notifications/Index', [
            'sections' => collect(AdminNotificationFeed::TYPES)
                ->mapWithKeys(fn (string $type): array => [$type => $feed->paginate($user, $type, 10, $pageNames[$type])])
                ->all(),
        ]);
    }

    public function markOne(Request $request)
    {
        $uid = auth()->id();
        abort_if(!$uid, 403);

        $data = $request->validate([
            'type'     => 'required|in:message,case,hearing,respondent_view',
            'sourceId' => 'required|integer',
        ]);

        DB::table('admin_notification_reads')->updateOrInsert(
            ['user_id' => $uid, 'type' => $data['type'], 'source_id' => $data['sourceId']],
            ['seen_at' => now(), 'updated_at' => now(), 'created_at' => now()]
        );

        AdminNotificationFeed::forgetCount($uid);

        return back()->with('success', __('app.admin_notifications.marked_as_seen'));
    }

    public function markAll(AdminNotificationFeed $feed)
    {
        $user = auth()->user();
        abort_unless($user instanceof User, 403);
        abort_unless($feed->canSee($user), 403);

        $now = now();
        $rows = [];
        foreach ($feed->unseenIds($user) as $type => $ids) {
            foreach ($ids as $id) {
                $rows[] = ['user_id' => $user->id, 'type' => $type, 'source_id' => $id, 'seen_at' => $now, 'created_at' => $now, 'updated_at' => $now];
            }
        }

        if ($rows !== []) {
            DB::table('admin_notification_reads')->upsert($rows, ['user_id', 'type', 'source_id'], ['seen_at', 'updated_at']);
        }

        AdminNotificationFeed::forgetCount($user->id);

        return back()->with('success', __('app.admin_notifications.all_marked_as_seen'));
    }
}
