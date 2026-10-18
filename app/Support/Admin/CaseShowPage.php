<?php

declare(strict_types=1);

namespace App\Support\Admin;

use App\Models\TermsAndCondition;
use App\Models\User;
use App\Support\EthiopianDate;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Props for the admin case page (Admin/Cases/Show). The controller runs the
 * queries; this class decides what the page may see and in which shape.
 */
final class CaseShowPage
{
    private const REVIEW_STATUSES = ['awaiting_review', 'accepted', 'returned', 'rejected'];

    /**
     * @param  array<string, Collection<int, object>>  $sections  timeline-free result sets keyed by name
     * @return array<string, mixed>
     */
    public static function props(object $case, array $sections, ?User $user, bool $locked): array
    {
        $can = fn (string $permission): bool => $user?->hasPermission($permission) ?? false;
        $section = fn (string $name): Collection => $sections[$name] ?? collect();
        $dateTime = fn ($value): string => $value ? EthiopianDate::format($value, withTime: true) : '';

        $abilities = [
            'editStatus' => $can('cases.edit'),
            'assign' => $can('cases.assign.team') || $can('cases.assign.member') || $can('cases.assign'),
            'review' => $can('cases.review'),
            'manageResponseReplies' => $can('cases.response-replies.manage'),
            'writeLetter' => $can('letters.create'),
            'viewLetters' => $can('letters.view'),
            'manageBenchNotes' => $can('bench-notes.manage'),
            'viewHearings' => $can('hearing.view') || $can('hearing.create') || $can('hearing.update') || $can('hearing.delete'),
            'createHearings' => $can('hearing.create'),
            'updateHearings' => $can('hearing.update'),
            'deleteHearings' => $can('hearing.delete'),
            'viewFiles' => $can('file.view'),
            'filesSection' => $can('file.view') || $can('file.create') || $can('file.update') || $can('file.delete'),
            'createFiles' => $can('file.create'),
            'deleteFiles' => $can('file.delete'),
            'createMessages' => $can('message.create'),
            'manageInspectionRequests' => $can('inspection-requests.manage'),
            'manageInspectionFindings' => $can('inspection-findings.manage'),
            'acceptInspectionFindings' => $can('inspection-findings.manage') && ($user?->hasRole('admin') ?? false),
        ];

        $status = (string) ($case->status ?? 'pending');
        $reviewStatus = self::reviewStatus($case->review_status ?? 'accepted');
        $lawyerName = self::personName($case->applicant_first_name ?? null, $case->applicant_middle_name ?? null, $case->applicant_last_name ?? null)
            ?: trim((string) ($case->applicant_name ?? ''));
        $applicantAddress = trim((string) ($case->applicant_address ?? '')) ?: trim((string) ($case->applicant_profile_address ?? ''));
        $today = Carbon::today();

        return [
            'case' => [
                'id' => (int) $case->id,
                'caseNumber' => (string) $case->case_number,
                'title' => trim((string) ($case->title ?? '')),
                'status' => $status,
                'reviewStatus' => $reviewStatus,
                'reviewNote' => $case->review_note ?: null,
                'caseType' => $case->case_type,
                'filingDate' => $case->filing_date ? EthiopianDate::format($case->filing_date) : null,
                'applicant' => [
                    'address' => $applicantAddress !== '' ? $applicantAddress : null,
                    'email' => $case->applicant_email,
                ],
                'lawyer' => ! empty($case->applicant_is_lawyer) && $lawyerName !== '' ? [
                    'name' => $lawyerName,
                    'documentUrl' => ! empty($case->applicant_lawyer_document_path) ? route('cases.lawyer-document', $case->id) : null,
                ] : null,
                'respondent' => [
                    'name' => $case->respondent_name,
                    'address' => $case->respondent_address,
                ],
                'assignee' => $case->assignee_name ? [
                    'name' => (string) $case->assignee_name,
                    'teamName' => $case->assignee_team_name,
                    'assignedAt' => $case->assigned_at ? EthiopianDate::format($case->assigned_at, withTime: true, timeFormat: 'h:i A') : null,
                ] : null,
                'created' => $case->created_at ? EthiopianDate::format($case->created_at, withTime: true, timeFormat: 'h:i A') : null,
                'updated' => $case->updated_at ? EthiopianDate::format($case->updated_at, withTime: true, timeFormat: 'h:i A') : null,
                'descriptionHtml' => clean((string) ($case->description ?? ''), 'cases'),
                'reliefHtml' => clean((string) ($case->relief_requested ?? ''), 'cases'),
                'locked' => $locked,
            ],
            'abilities' => $abilities,
            'links' => [
                'assign' => route('cases.assign.form', $case->id),
                'benchNotes' => route('bench-notes.index', ['case_id' => $case->id]),
                'composeLetter' => route('letters.compose', ['case_id' => $case->id]),
                'decision' => route('decisions.create', ['case_id' => $case->id]),
                'inspectionRequest' => route('case-inspection-requests.create', ['case_id' => $case->id]),
            ],
            'termsItems' => self::termsItems(),
            'printedAt' => EthiopianDate::format(now(), withTime: true),
            'hearings' => $abilities['viewHearings'] ? $section('hearings')->map(function (object $hearing) use ($today): array {
                $at = Carbon::parse($hearing->hearing_at);

                return [
                    'id' => (int) $hearing->id,
                    'date' => $at->format('Y-m-d'),
                    'time' => $at->format('H:i'),
                    'display' => EthiopianDate::format($hearing->hearing_at, withTime: true),
                    'summaryDisplay' => EthiopianDate::smartFormat($hearing->hearing_at, true, '—', 'h:i A', 'F j, Y'),
                    'type' => $hearing->type ?: null,
                    'location' => $hearing->location ?: null,
                    'notes' => $hearing->notes ?: null,
                    'notesExcerpt' => $hearing->notes ? Str::limit(strip_tags((string) $hearing->notes), 60) : null,
                    // Hearings on or before today are part of the record.
                    'locked' => $at->copy()->startOfDay()->lte($today),
                ];
            })->values() : [],
            'audits' => $section('audits')->map(function (object $audit) use ($dateTime): array {
                $meta = $audit->meta ? json_decode((string) $audit->meta, true) : null;
                $actor = match (true) {
                    ! empty($audit->actor_name) => $audit->actor_name.($audit->actor_id ? " (#{$audit->actor_id})" : ''),
                    ! empty($audit->actor_id) => ($audit->actor_type ?? 'system')." (#{$audit->actor_id})",
                    default => $audit->actor_type ?? 'system',
                };

                return [
                    'id' => (int) $audit->id,
                    'when' => $dateTime($audit->created_at),
                    'action' => str_replace('_', ' ', ucfirst((string) $audit->action)),
                    'actor' => $actor,
                    'meta' => $meta ? json_encode($meta, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : null,
                ];
            })->values(),
            'inspectionRequests' => $abilities['manageInspectionRequests'] ? $section('inspectionRequests')->map(fn (object $request): array => [
                'id' => (int) $request->id,
                'date' => EthiopianDate::smartFormat($request->request_date),
                'subject' => (string) $request->subject,
                'status' => (string) $request->status,
                'inspector' => $request->assigned_inspector_name,
                'createdBy' => $request->created_by_name,
            ])->values() : [],
            'inspectionFindings' => $abilities['manageInspectionFindings'] ? $section('inspectionFindings')->map(fn (object $finding): array => [
                'id' => (int) $finding->id,
                'date' => EthiopianDate::smartFormat($finding->finding_date),
                'title' => (string) $finding->title,
                'accepted' => ! empty($finding->accepted_at),
                'severity' => (string) $finding->severity,
                'requestSubject' => $finding->request_subject,
                'inspector' => $finding->assigned_inspector_name,
                'recordedBy' => $finding->recorded_by_name,
                'details' => (string) ($finding->details ?? ''),
                'attachment' => ! empty($finding->attachment_path) ? [
                    'url' => route('case-inspection-findings.attachment', $finding->id),
                    'name' => $finding->attachment_original_name,
                ] : null,
            ])->values() : [],
            'documents' => $section('documents')->map(function (object $doc) use ($case, $dateTime): array {
                $path = $doc->file_path ?? $doc->path ?? null;

                return [
                    'id' => (int) $doc->id,
                    'title' => (string) (($doc->title ?? null) ?: ($path ? basename((string) $path) : __('cases.documents.document'))),
                    'descriptionHtml' => ! empty($doc->description) ? clean((string) $doc->description, 'cases') : null,
                    'mime' => $doc->mime ?? null,
                    'uploaded' => $dateTime($doc->created_at),
                    'sizeKb' => isset($doc->size) ? round(max(0, (int) $doc->size) / 1024, 1) : null,
                    'url' => $path ? route('cases.documents.view', [$case->id, $doc->id]) : null,
                ];
            })->values(),
            'witnesses' => $section('witnesses')->map(fn (object $witness): array => [
                'id' => (int) $witness->id,
                'name' => (string) $witness->full_name,
                'phone' => $witness->phone ?: null,
                'email' => $witness->email ?: null,
                'address' => $witness->address ?: null,
            ])->values(),
            'letters' => $section('letters')->map(fn (object $letter): array => [
                'id' => (int) $letter->id,
                'reference' => $letter->reference_number ?: null,
                'subject' => $letter->subject ?: null,
                'template' => $letter->template_title,
                'status' => strtolower((string) ($letter->approval_status ?? 'pending')),
                'date' => $dateTime($letter->created_at),
                'author' => $letter->author_name,
                'url' => $abilities['viewLetters'] ? route('letters.show', $letter->id) : null,
            ])->values(),
            'respondentResponses' => $section('respondentResponses')->map(fn (object $response): array => [
                'id' => (int) $response->id,
                'title' => $response->title ?: null,
                'status' => self::reviewStatus($response->review_status ?? 'awaiting_review'),
                'created' => EthiopianDate::smartFormat($response->created_at, true, ''),
                'caseNumber' => (string) ($response->case_number ?? $case->case_number),
                'bodyHtml' => clean((string) ($response->description ?? ''), 'cases'),
                'reviewNote' => $response->review_note ?: null,
                'pdfUrl' => ! empty($response->pdf_path) ? route('respondent-responses.download', ['response' => $response->id, 'inline' => 1]) : null,
            ])->values(),
            'responseReplies' => $abilities['manageResponseReplies'] ? $section('responseReplies')->map(fn (object $reply): array => [
                'id' => (int) $reply->id,
                'status' => self::reviewStatus($reply->review_status ?? 'awaiting_review'),
                'created' => EthiopianDate::smartFormat($reply->created_at, true, ''),
                'responseNumber' => $reply->response_number ?: null,
                'applicantName' => self::personName($reply->applicant_first_name ?? null, $reply->applicant_middle_name ?? null, $reply->applicant_last_name ?? null) ?: null,
                'description' => (string) ($reply->description ?? ''),
                'reviewNote' => $reply->review_note ?: null,
                'pdfUrl' => ! empty($reply->pdf_path) ? route('admin.applicant-response-replies.download', [
                    'case' => $case->id,
                    'response' => $reply->respondent_response_id,
                    'reply' => $reply->id,
                    'inline' => 1,
                ]) : null,
            ])->values() : [],
            'messages' => $section('messages')->map(function (object $message) use ($dateTime): array {
                $fromStaff = $message->sender_user_id !== null;
                $who = match (true) {
                    $fromStaff => $message->admin_name ?: __('cases.messages.court_staff'),
                    $message->sender_applicant_id !== null => self::personName($message->first_name ?? null, null, $message->last_name ?? null),
                    default => __('cases.messages_section.system'),
                };

                return [
                    'id' => (int) $message->id,
                    'fromStaff' => $fromStaff,
                    'who' => $who,
                    'when' => $dateTime($message->created_at),
                    'body' => (string) $message->body,
                ];
            })->values(),
            'files' => $abilities['filesSection'] ? $section('files')->map(fn (object $file): array => [
                'id' => (int) $file->id,
                'label' => (string) ($file->label ?: basename((string) $file->path)),
                'mime' => $file->mime ?: null,
                'sizeKb' => round(((int) ($file->size ?? 0)) / 1024, 1),
                'uploaded' => $dateTime($file->created_at),
                'by' => ($file->uploader_name ?? null) ?: (self::personName($file->first_name ?? null, null, $file->last_name ?? null) ?: null),
                'url' => $abilities['viewFiles'] ? route('cases.files.download', [$case->id, $file->id]) : null,
            ])->values() : [],
        ];
    }

    private static function reviewStatus(?string $status): string
    {
        return in_array($status, self::REVIEW_STATUSES, true) ? $status : 'accepted';
    }

    private static function personName(?string ...$parts): string
    {
        return collect($parts)->map(fn (?string $part): string => trim((string) $part))->filter()->implode(' ');
    }

    /**
     * The published terms as list rows for the printed case overview; only a
     * few inline tags survive (the text was purified first).
     *
     * @return list<string>
     */
    private static function termsItems(): array
    {
        $terms = TermsAndCondition::published()->orderByDesc('published_at')->first();
        if (! $terms || empty($terms->body)) {
            return [];
        }

        $html = clean((string) $terms->body, 'cases');
        if (preg_match_all('/<li\b[^>]*>(.*?)<\/li>/is', $html, $matches) && $matches[1] !== []) {
            $rows = $matches[1];
        } elseif (preg_match_all('/<p\b[^>]*>(.*?)<\/p>/is', $html, $matches) && $matches[1] !== []) {
            $rows = $matches[1];
        } else {
            $rows = preg_split('/<br\s*\/?>|\r\n|\n/i', $html) ?: [];
        }

        return collect($rows)
            ->map(fn (string $row): string => trim((string) preg_replace('/\s+/', ' ', strip_tags($row, '<strong><b><em><i><u><a>'))))
            ->filter()
            ->values()
            ->all();
    }
}
