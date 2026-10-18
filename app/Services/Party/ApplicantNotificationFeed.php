<?php

declare(strict_types=1);

namespace App\Services\Party;

use App\Support\EthiopianDate;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Unseen notifications for an applicant: admin messages, upcoming hearings,
 * respondents opening their cases, and status changes. "Seen" is recorded
 * per item in notification_reads.
 */
final class ApplicantNotificationFeed
{
    /** Display order of the groups. */
    public const TYPES = ['message', 'hearing', 'respondent_view', 'status'];

    /** Body of a legacy system message that is shown translated instead. */
    private const LEGACY_UPDATE_BODY = 'Applicant updated the case details. Please review the submission.';

    public function query(string $type, int $applicantId): Builder
    {
        return match ($type) {
            'hearing' => $this->hearings($applicantId),
            'message' => $this->messages($applicantId),
            'status' => $this->statusChanges($applicantId),
            'respondent_view' => $this->respondentViews($applicantId),
        };
    }

    public function count(int $applicantId): int
    {
        return array_sum(array_map(
            fn (string $type): int => $this->query($type, $applicantId)->count(),
            self::TYPES,
        ));
    }

    /**
     * @return array{count: int, groups: list<array{type: string, items: list<array<string, mixed>>}>}
     */
    public function latest(int $applicantId, int $perType = 10): array
    {
        $groups = [];
        foreach (self::TYPES as $type) {
            $items = $this->query($type, $applicantId)->limit($perType)->get()
                ->map(fn (object $row): array => $this->present($type, $row))
                ->all();

            if ($items !== []) {
                $groups[] = ['type' => $type, 'items' => $items];
            }
        }

        return ['count' => $this->count($applicantId), 'groups' => $groups];
    }

    public function markAll(int $applicantId): void
    {
        $now = now();
        $rows = [];

        foreach (self::TYPES as $type) {
            $source = match ($type) {
                'hearing' => 'h.id',
                'message' => 'm.id',
                'status' => 'l.id',
                'respondent_view' => 'v.id',
            };

            foreach ($this->query($type, $applicantId)->pluck($source) as $id) {
                $rows[] = [
                    'applicant_id' => $applicantId,
                    'type' => $type,
                    'source_id' => $id,
                    'seen_at' => $now,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        if ($rows !== []) {
            DB::table('notification_reads')->upsert($rows, ['applicant_id', 'type', 'source_id'], ['seen_at', 'updated_at']);
        }
    }

    public function markOne(int $applicantId, string $type, int $sourceId): void
    {
        DB::table('notification_reads')->updateOrInsert(
            ['applicant_id' => $applicantId, 'type' => $type, 'source_id' => $sourceId],
            ['seen_at' => now(), 'updated_at' => now(), 'created_at' => now()],
        );
    }

    /**
     * @return array{type: string, sourceId: int, caseId: int, caseNumber: string, href: string, text: string, when: string|null, url: string|null}
     */
    public function present(string $type, object $row): array
    {
        [$text, $when] = match ($type) {
            'message' => [
                Str::limit(trim((string) $row->body) === self::LEGACY_UPDATE_BODY
                    ? __('cases.notifications.applicant_updated_submission')
                    : (string) $row->body, 80),
                EthiopianDate::smartRelative($row->created_at),
            ],
            'hearing' => [
                collect([$row->type ?: __('app.Hearing'), $row->location])->filter()->implode(' · '),
                EthiopianDate::format($row->hearing_at, withTime: true),
            ],
            'respondent_view' => [
                __('app.admin_notifications.respondent_viewed_case', [
                    'name' => $row->respondent_name ?: __('app.admin_notifications.respondent_default'),
                ]),
                EthiopianDate::smartRelative($row->viewed_at),
            ],
            'status' => [
                __('app.admin_notifications.status_changed', [
                    'from' => $this->statusLabel($row->from_status),
                    'to' => $this->statusLabel($row->to_status),
                ]),
                EthiopianDate::smartRelative($row->created_at),
            ],
        };

        // Some system messages are just a link (e.g. a letter preview); only web links are offered.
        $url = null;
        if ($type === 'message') {
            $body = trim((string) $row->body);
            if (filter_var($body, FILTER_VALIDATE_URL) && in_array(parse_url($body, PHP_URL_SCHEME), ['http', 'https'], true)) {
                $url = $body;
                $text = __('app.admin_notifications.view_letter_preview');
            }
        }

        return [
            'type' => $type,
            'sourceId' => (int) $row->id,
            'caseId' => (int) $row->case_id,
            'caseNumber' => (string) $row->case_number,
            'href' => route('applicant.cases.show', $row->case_id),
            'text' => (string) $text,
            'when' => $when !== '' ? $when : null,
            'url' => $url,
        ];
    }

    private function statusLabel(?string $status): string
    {
        $key = "app.status.{$status}";

        return trans()->has($key) ? (string) __($key) : Str::headline($status ?? '-');
    }

    private function hearings(int $applicantId): Builder
    {
        return DB::table('case_hearings as h')
            ->join('court_cases as c', 'c.id', '=', 'h.case_id')
            ->select('h.id', 'h.hearing_at', 'h.type', 'h.location', 'c.id as case_id', 'c.case_number')
            ->where('c.applicant_id', $applicantId)
            ->whereBetween('h.hearing_at', [now()->subDay(), now()->addDays(60)])
            ->whereNotExists($this->unseen('h.id', 'hearing', $applicantId))
            ->orderBy('h.hearing_at');
    }

    private function messages(int $applicantId): Builder
    {
        return DB::table('case_messages as m')
            ->join('court_cases as c', 'c.id', '=', 'm.case_id')
            ->select('m.id', 'm.body', 'm.created_at', 'c.id as case_id', 'c.case_number')
            ->whereNotNull('m.sender_user_id')
            ->where('c.applicant_id', $applicantId)
            ->where('m.created_at', '>=', now()->subDays(14))
            ->whereNotExists($this->unseen('m.id', 'message', $applicantId))
            ->orderByDesc('m.created_at');
    }

    private function statusChanges(int $applicantId): Builder
    {
        return DB::table('case_status_logs as l')
            ->join('court_cases as c', 'c.id', '=', 'l.case_id')
            ->select('l.id', 'l.from_status', 'l.to_status', 'l.created_at', 'c.id as case_id', 'c.case_number')
            ->where('c.applicant_id', $applicantId)
            ->where('l.created_at', '>=', now()->subDays(14))
            ->whereNotExists($this->unseen('l.id', 'status', $applicantId))
            ->orderByDesc('l.created_at');
    }

    private function respondentViews(int $applicantId): Builder
    {
        return DB::table('respondent_case_views as v')
            ->join('court_cases as c', 'c.id', '=', 'v.case_id')
            ->join('respondents as r', 'r.id', '=', 'v.respondent_id')
            ->select(
                'v.id',
                'v.viewed_at',
                'v.case_id',
                'c.case_number',
                DB::raw("TRIM(CONCAT_WS(' ', r.first_name, r.middle_name, r.last_name)) as respondent_name"),
            )
            ->where('c.applicant_id', $applicantId)
            ->where('v.viewed_at', '>=', now()->subDays(14))
            ->whereNotExists($this->unseen('v.id', 'respondent_view', $applicantId))
            ->orderByDesc('v.viewed_at');
    }

    private function unseen(string $column, string $type, int $applicantId): \Closure
    {
        return fn (Builder $query) => $query->from('notification_reads as nr')
            ->whereColumn('nr.source_id', $column)
            ->where('nr.type', $type)
            ->where('nr.applicant_id', $applicantId);
    }
}
