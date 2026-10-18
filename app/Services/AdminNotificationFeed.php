<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Team;
use App\Models\User;
use App\Support\EthiopianDate;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Unseen staff notifications: applicant messages, unassigned new cases, the
 * user's upcoming hearings and respondent case views from the last 14 days,
 * limited to the cases the user may see.
 *
 * One definition backs the bell badge, the dropdown and "mark all as seen",
 * so the three can never disagree.
 */
final class AdminNotificationFeed
{
    public const TYPES = ['message', 'case', 'hearing', 'respondent_view'];

    private const WINDOW_DAYS = 14;

    public function canSee(User $user): bool
    {
        return $user->hasPermission('cases.view');
    }

    public function count(User $user): int
    {
        if (! $this->canSee($user)) {
            return 0;
        }

        return (int) Cache::remember(self::countCacheKey($user->id), 120, function () use ($user): int {
            $total = 0;
            foreach (self::TYPES as $type) {
                $total += $this->unseen($user, $type)->count();
            }

            return $total;
        });
    }

    /**
     * @return array{count: int, groups: list<array{type: string, items: list<array<string, mixed>>}>}
     */
    public function latest(User $user, int $perType = 5): array
    {
        if (! $this->canSee($user)) {
            return ['count' => 0, 'groups' => []];
        }

        $groups = [];
        foreach (self::TYPES as $type) {
            $items = $this->items($user, $type, $perType);
            if ($items !== []) {
                $groups[] = ['type' => $type, 'items' => $items];
            }
        }

        return ['count' => $this->count($user), 'groups' => $groups];
    }

    /**
     * @return array<string, list<int>>
     */
    public function unseenIds(User $user): array
    {
        if (! $this->canSee($user)) {
            return [];
        }

        $ids = [];
        foreach (self::TYPES as $type) {
            $ids[$type] = $this->unseen($user, $type)->pluck(self::idColumn($type))->map(fn ($id): int => (int) $id)->all();
        }

        return $ids;
    }

    /**
     * One page of unseen items of a type, for the full notifications page.
     */
    public function paginate(User $user, string $type, int $perPage, string $pageName): LengthAwarePaginator
    {
        return $this->ordered($this->unseen($user, $type), $type)
            ->paginate($perPage, ['*'], $pageName)
            ->withQueryString()
            ->through(fn (object $row): array => $this->present($type, $row, 120));
    }

    public static function forgetCount(int $userId): void
    {
        Cache::forget(self::countCacheKey($userId));
    }

    private static function countCacheKey(int $userId): string
    {
        return "admin_notif_count_{$userId}";
    }

    private static function idColumn(string $type): string
    {
        return match ($type) {
            'message' => 'm.id',
            'case' => 'c.id',
            'hearing' => 'h.id',
            'respondent_view' => 'v.id',
        };
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function items(User $user, string $type, int $limit): array
    {
        return $this->ordered($this->unseen($user, $type), $type)
            ->limit($limit)
            ->get()
            ->map(fn (object $row): array => $this->present($type, $row, 70))
            ->all();
    }

    private function ordered(Builder $query, string $type): Builder
    {
        return match ($type) {
            'message' => $query->select('m.id', 'm.body', 'm.created_at', 'c.case_number', 'c.id as case_id')
                ->orderByDesc('m.created_at'),
            'case' => $query->select('c.id', 'c.case_number', 'c.title', 'c.created_at', 'c.id as case_id')
                ->orderByDesc('c.created_at'),
            'hearing' => $query->select('h.id', 'h.hearing_at', 'c.id as case_id', 'c.case_number')
                ->orderBy('h.hearing_at'),
            'respondent_view' => $query->join('respondents as r', 'r.id', '=', 'v.respondent_id')
                ->select('v.id', 'v.viewed_at', 'v.case_id', 'c.case_number', 'r.first_name', 'r.middle_name', 'r.last_name')
                ->orderByDesc('v.viewed_at'),
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function present(string $type, object $row, int $textLimit): array
    {
        return [
            'type' => $type,
            'sourceId' => (int) $row->id,
            'caseId' => (int) $row->case_id,
            'caseNumber' => (string) $row->case_number,
            'href' => route('cases.show', $row->case_id),
            'text' => match ($type) {
                'message' => Str::limit(self::messageBody((string) $row->body), $textLimit),
                'case' => Str::limit((string) $row->title, $textLimit),
                'hearing' => EthiopianDate::format($row->hearing_at, withTime: true),
                'respondent_view' => __('app.admin_notifications.respondent_viewed_case', [
                    'name' => trim(implode(' ', array_filter([$row->first_name, $row->middle_name, $row->last_name])))
                        ?: __('app.admin_notifications.respondent_default'),
                ]),
            },
            'when' => match ($type) {
                'message' => EthiopianDate::smartRelative($row->created_at),
                'case' => EthiopianDate::smartRelative($row->created_at),
                'hearing' => null,
                'respondent_view' => EthiopianDate::smartRelative($row->viewed_at),
            },
        ];
    }

    private function unseen(User $user, string $type): Builder
    {
        $uid = $user->id;
        $since = now()->subDays(self::WINDOW_DAYS);
        $notSeen = fn (string $column) => fn ($q) => $q->from('admin_notification_reads as nr')
            ->whereColumn('nr.source_id', $column)
            ->where('nr.type', $type)
            ->where('nr.user_id', $uid);

        $query = match ($type) {
            'message' => DB::table('case_messages as m')
                ->join('court_cases as c', 'c.id', '=', 'm.case_id')
                ->whereNotNull('m.sender_applicant_id')
                ->where('m.created_at', '>=', $since)
                ->whereNotExists($notSeen('m.id')),
            'case' => DB::table('court_cases as c')
                ->where('c.status', 'pending')
                ->whereNull('c.assigned_user_id')
                ->where('c.created_at', '>=', $since)
                ->whereNotExists($notSeen('c.id')),
            'hearing' => DB::table('case_hearings as h')
                ->join('court_cases as c', 'c.id', '=', 'h.case_id')
                ->where('c.assigned_user_id', $uid)
                ->whereBetween('h.hearing_at', [now(), now()->addDays(self::WINDOW_DAYS)])
                ->whereNotExists($notSeen('h.id')),
            'respondent_view' => DB::table('respondent_case_views as v')
                ->join('court_cases as c', 'c.id', '=', 'v.case_id')
                ->where(fn ($q) => $q->where('c.assigned_user_id', $uid)->orWhereNull('c.assigned_user_id'))
                ->where('v.viewed_at', '>=', $since)
                ->whereNotExists($notSeen('v.id')),
        };

        return $this->scopeToVisibleCases($query, $user);
    }

    /**
     * Team leaders see their team's cases; plain team members see only cases
     * assigned to them; everyone else with cases.view sees all.
     */
    private function scopeToVisibleCases(Builder $query, User $user): Builder
    {
        $uid = $user->id;
        $isLeader = $user->hasPermission('cases.assign.member');
        $canAssignTeams = $user->hasPermission('cases.assign.team');

        if ($isLeader && ! $canAssignTeams) {
            $team = Team::with(['users' => fn ($q) => $q->where('status', 'active')])
                ->where('team_leader_id', $uid)
                ->first();
            $memberIds = collect([$uid])->merge($team?->users->pluck('id') ?? [])->filter()->unique()->values()->all();

            return $query->where(function ($q) use ($memberIds, $team): void {
                $q->whereIn('c.assigned_user_id', $memberIds);
                if ($team !== null) {
                    $q->orWhere('c.assigned_team_id', $team->id);
                }
            });
        }

        $isTeamMember = DB::table('team_user')->where('user_id', $uid)->exists();
        if ($isTeamMember && ! $isLeader && ! $canAssignTeams) {
            return $query->where(fn ($q) => $q->where('c.assigned_member_user_id', $uid)->orWhere('c.assigned_user_id', $uid));
        }

        return $query;
    }

    private static function messageBody(string $body): string
    {
        // Older rows stored this fixed English sentence; show it translated.
        $legacy = 'Applicant updated the case details. Please review the submission.';

        return trim($body) === $legacy ? (string) __('cases.notifications.applicant_updated_submission') : $body;
    }
}
