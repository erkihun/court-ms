<?php

declare(strict_types=1);

namespace App\Http\Controllers\Respondent;

use App\Http\Controllers\Controller;
use App\Models\Applicant;
use App\Services\Party\RespondentNotificationFeed;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class NotificationController extends Controller
{
    public function __construct(private readonly RespondentNotificationFeed $feed) {}

    /** Latest unseen items for the header bell; empty when the account has no respondent record yet. */
    public function feed(): JsonResponse
    {
        $respondentId = $this->respondentId(required: false);

        return response()->json($respondentId === null ? ['count' => 0, 'items' => []] : $this->feed->latest($respondentId));
    }

    public function markOne(Request $request): RedirectResponse
    {
        session(['acting_as_respondent' => true]);
        $data = $request->validate([
            'type' => ['required', Rule::in(RespondentNotificationFeed::TYPES)],
            'sourceId' => ['required', 'integer'],
        ]);

        $this->feed->markOne($this->respondentId(), $data['type'], (int) $data['sourceId']);

        return back()->with('success', __('app.admin_notifications.marked_as_seen'));
    }

    public function markAll(): RedirectResponse
    {
        session(['acting_as_respondent' => true]);
        $this->feed->markAll($this->respondentId());

        return back()->with('success', __('app.admin_notifications.all_marked_as_seen'));
    }

    private function respondentId(bool $required = true): ?int
    {
        $id = auth('respondent')->check()
            ? (int) auth('respondent')->id()
            : $this->feed->respondentIdFor(auth('applicant')->user() instanceof Applicant ? auth('applicant')->user() : null);

        abort_if($required && $id === null, 403);

        return $id;
    }
}
