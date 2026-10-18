<?php

declare(strict_types=1);

namespace App\Services\Party;

use App\Models\Applicant;
use App\Models\Respondent;
use App\Support\EthiopianDate;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Unseen notifications for an applicant acting as a respondent, about the
 * cases that respondent has opened: the views themselves, hearings, status
 * changes and court messages. "Seen" is recorded in respondent_notification_reads.
 */
final class RespondentNotificationFeed
{
    public const TYPES = ['respondent_view', 'hearing', 'status', 'message'];

    /** The respondent record shares the applicant account's email. */
    public function respondentIdFor(?Applicant $applicant): ?int
    {
        if ($applicant === null) {
            return null;
        }

        $id = Respondent::query()->where('email', $applicant->email)->value('id');

        return $id === null ? null : (int) $id;
    }

    public function query(string $type, int $respondentId, bool $recentOnly = true): Builder
    {
        $caseIds = DB::table('respondent_case_views')->where('respondent_id', $respondentId)->select('case_id');
        $now = now();

        $query = match ($type) {
            'respondent_view' => DB::table('respondent_case_views as v')
                ->join('court_cases as c', 'c.id', '=', 'v.case_id')
                ->select('v.id', 'v.viewed_at as at', 'c.case_number', 'c.title')
                ->where('v.respondent_id', $respondentId),
            'hearing' => DB::table('case_hearings as v')
                ->join('court_cases as c', 'c.id', '=', 'v.case_id')
                ->select('v.id', 'v.hearing_at as at', 'c.case_number', 'c.title')
                ->whereIn('v.case_id', $caseIds)
                ->when($recentOnly, fn (Builder $q) => $q->whereBetween('v.hearing_at', [$now->copy()->subDay(), $now->copy()->addDays(60)])),
            'status' => DB::table('case_status_logs as v')
                ->join('court_cases as c', 'c.id', '=', 'v.case_id')
                ->select('v.id', 'v.created_at as at', 'c.case_number', 'c.title')
                ->whereIn('v.case_id', $caseIds)
                ->when($recentOnly, fn (Builder $q) => $q->where('v.created_at', '>=', $now->copy()->subDays(14))),
            'message' => DB::table('case_messages as v')
                ->join('court_cases as c', 'c.id', '=', 'v.case_id')
                ->select('v.id', 'v.created_at as at', 'c.case_number', 'c.title')
                ->whereIn('v.case_id', $caseIds)
                ->whereNotNull('v.sender_user_id')
                ->when($recentOnly, fn (Builder $q) => $q->where('v.created_at', '>=', $now->copy()->subDays(14))),
        };

        return $query->whereNotExists(fn (Builder $q) => $q->from('respondent_notification_reads as r')
            ->whereColumn('r.source_id', 'v.id')
            ->where('r.type', $type)
            ->where('r.respondent_id', $respondentId));
    }

    public function count(int $respondentId): int
    {
        return array_sum(array_map(fn (string $type): int => $this->query($type, $respondentId)->count(), self::TYPES));
    }

    /**
     * Newest first, across all types.
     *
     * @return array{count: int, items: list<array<string, mixed>>}
     */
    public function latest(int $respondentId, int $limit = 8): array
    {
        $items = collect(self::TYPES)
            ->flatMap(fn (string $type) => $this->query($type, $respondentId)->orderByDesc('at')->limit($limit)->get()
                ->map(fn (object $row): array => ['type' => $type, 'row' => $row]))
            ->sortByDesc(fn (array $entry) => (string) $entry['row']->at)
            ->take($limit)
            ->map(fn (array $entry): array => [
                'type' => $entry['type'],
                'sourceId' => (int) $entry['row']->id,
                'caseNumber' => (string) $entry['row']->case_number,
                'title' => $entry['row']->title,
                'href' => route('respondent.cases.show', $entry['row']->case_number),
                'when' => EthiopianDate::smartRelative($entry['row']->at) ?: null,
            ])
            ->values()
            ->all();

        return ['count' => $this->count($respondentId), 'items' => $items];
    }

    /** Everything still unseen, regardless of age (matches the previous behaviour of "mark all"). */
    public function markAll(int $respondentId): void
    {
        $now = now();
        $rows = [];

        foreach (self::TYPES as $type) {
            foreach ($this->query($type, $respondentId, recentOnly: false)->pluck('v.id') as $id) {
                $rows[] = [
                    'respondent_id' => $respondentId,
                    'type' => $type,
                    'source_id' => $id,
                    'seen_at' => $now,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        if ($rows !== []) {
            DB::table('respondent_notification_reads')->upsert($rows, ['respondent_id', 'type', 'source_id'], ['seen_at', 'updated_at']);
        }
    }

    public function markOne(int $respondentId, string $type, int $sourceId): void
    {
        DB::table('respondent_notification_reads')->updateOrInsert(
            ['respondent_id' => $respondentId, 'type' => $type, 'source_id' => $sourceId],
            ['seen_at' => now(), 'created_at' => now(), 'updated_at' => now()],
        );
    }
}
