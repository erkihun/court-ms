<?php

declare(strict_types=1);

namespace App\Http\Controllers\Applicant;

use App\Http\Controllers\Controller;
use App\Services\Party\ApplicantNotificationFeed;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ApplicantNotificationController extends Controller
{
    public function __construct(private readonly ApplicantNotificationFeed $feed) {}

    /** Unseen notifications, one independently paginated list per type. */
    public function index(): Response
    {
        $applicantId = $this->applicantId();
        $pageNames = [
            'message' => 'messages_page',
            'hearing' => 'hearings_page',
            'respondent_view' => 'respondent_views_page',
            'status' => 'status_page',
        ];

        $sections = collect(ApplicantNotificationFeed::TYPES)->mapWithKeys(function (string $type) use ($applicantId, $pageNames): array {
            /** @var LengthAwarePaginator $page */
            $page = $this->feed->query($type, $applicantId)->paginate(10, ['*'], $pageNames[$type])->withQueryString();
            $page->setCollection($page->getCollection()->map(fn (object $row): array => $this->feed->present($type, $row)));

            return [$type => $page];
        });

        return Inertia::render('Applicant/Notifications/Index', ['sections' => $sections]);
    }

    /** Latest unseen items for the header bell. */
    public function feed(): JsonResponse
    {
        return response()->json($this->feed->latest($this->applicantId()));
    }

    public function markOne(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'type' => ['required', Rule::in(ApplicantNotificationFeed::TYPES)],
            'sourceId' => ['required', 'integer'],
        ]);

        $this->feed->markOne($this->applicantId(), $data['type'], (int) $data['sourceId']);

        return back()->with('success', __('app.admin_notifications.marked_as_seen'));
    }

    public function markAll(): RedirectResponse
    {
        $this->feed->markAll($this->applicantId());

        return back()->with('success', __('app.admin_notifications.all_marked_as_seen'));
    }

    public function settingsEdit(): Response
    {
        $prefs = DB::table('applicant_notification_prefs')->where('applicant_id', $this->applicantId())->first();

        return Inertia::render('Applicant/Notifications/Settings', [
            'prefs' => [
                'email_status' => (bool) ($prefs->email_status ?? true),
                'email_hearing' => (bool) ($prefs->email_hearing ?? true),
                'email_message' => (bool) ($prefs->email_message ?? true),
                'email_weekly_digest' => (bool) ($prefs->email_weekly_digest ?? false),
            ],
        ]);
    }

    public function settingsUpdate(Request $request): RedirectResponse
    {
        $request->validate([
            'email_status' => 'sometimes|boolean',
            'email_hearing' => 'sometimes|boolean',
            'email_message' => 'sometimes|boolean',
            'email_weekly_digest' => 'sometimes|boolean',
        ]);

        DB::table('applicant_notification_prefs')->updateOrInsert(
            ['applicant_id' => $this->applicantId()],
            [
                'email_status' => (int) $request->boolean('email_status'),
                'email_hearing' => (int) $request->boolean('email_hearing'),
                'email_message' => (int) $request->boolean('email_message'),
                'email_weekly_digest' => (int) $request->boolean('email_weekly_digest'),
                'updated_at' => now(),
                'created_at' => now(),
            ],
        );

        return back()->with('success', __('app.notification_preferences.updated'));
    }

    private function applicantId(): int
    {
        $id = auth('applicant')->id();
        abort_if($id === null, 403);

        return (int) $id;
    }
}
