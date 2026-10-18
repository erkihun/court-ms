<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

/**
 * Queued: sent by the queue worker so the request that triggers it never waits on SMTP.
 * The locale is captured here because the worker renders in its own default language.
 */
class CaseHearingScheduledMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public int $tries = 3;

    /** @var list<int> */
    public array $backoff = [60, 300];

    public $case;
    public $hearing;

    public function __construct($case, $hearing)
    {
        $this->locale(app()->getLocale());
        $this->case = $case;
        $this->hearing = $hearing;
    }

    public function build()
    {
        // Build ICS content (1-hour default duration)
        $start = Carbon::parse($this->hearing->hearing_at)->utc();
        $end   = (clone $start)->addHour();
        $now   = Carbon::now('UTC');

        $host   = parse_url(config('app.url', 'https://court-ms.local'), PHP_URL_HOST) ?: 'court-ms.local';
        $uid    = 'hearing-' . $this->hearing->id . '@' . $host;

        $escape = function ($text) {
            $text = str_replace(["\\", ";", ","], ["\\\\", "\;", "\,"], $text ?? '');
            // fold long lines is optional—clients handle fine without
            return $text;
        };

        $summary     = $escape(__('notifications.mail.hearing_calendar_summary', [
            'case' => $this->case->case_number ?? '',
        ]));
        $description = $escape(__('notifications.mail.hearing_calendar_description', [
            'title' => $this->case->title ?? '',
        ]));
        $location    = $escape($this->hearing->location ?? '');

        $ics = implode("\r\n", [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//Court-MS//EN',
            'CALSCALE:GREGORIAN',
            'METHOD:PUBLISH',
            'BEGIN:VEVENT',
            'UID:' . $uid,
            'DTSTAMP:' . $now->format('Ymd\THis\Z'),
            'DTSTART:' . $start->format('Ymd\THis\Z'),
            'DTEND:'   . $end->format('Ymd\THis\Z'),
            'SUMMARY:' . $summary,
            'DESCRIPTION:' . $description,
            'LOCATION:' . $location,
            'END:VEVENT',
            'END:VCALENDAR',
            '',
        ]);

        return $this->subject(__('notifications.mail.hearing_scheduled_subject', [
            'case' => $this->case->case_number ?? __('notifications.mail.your_case'),
        ]))
            ->view('mail.case_hearing_scheduled')  // you already have this blade
            ->attachData($ics, 'hearing.ics', [
                'mime' => 'text/calendar; charset=utf-8; method=PUBLISH',
            ]);
    }
}
