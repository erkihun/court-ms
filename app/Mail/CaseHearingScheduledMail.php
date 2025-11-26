<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

class CaseHearingScheduledMail extends Mailable
{
    use Queueable, SerializesModels;

    public $case;
    public $hearing;

    public function __construct($case, $hearing)
    {
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

        $summary     = $escape('Court Hearing: ' . ($this->case->case_number ?? ''));
        $description = $escape(($this->case->title ?? '') . ' — Please arrive early with any documents.');
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

        return $this->subject('Hearing Scheduled: ' . ($this->case->case_number ?? 'Your Case'))
            ->view('mail.case_hearing_scheduled')  // you already have this blade
            ->attachData($ics, 'hearing.ics', [
                'mime' => 'text/calendar; charset=utf-8; method=PUBLISH',
            ]);
    }
}
