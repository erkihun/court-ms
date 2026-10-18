<?php

declare(strict_types=1);

use App\Mail\ApplicantReceiptMail;
use App\Mail\CaseHearingScheduledMail;
use App\Mail\CaseMessageMail;
use App\Mail\CaseStatusChangedMail;
use App\Mail\LetterApprovedMail;
use App\Mail\RespondentViewedCaseMail;
use App\Services\Party\ActingRespondent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

test('notification mail is queued, receipts with a PDF are still sent directly', function (): void {
    foreach ([CaseHearingScheduledMail::class, CaseMessageMail::class, CaseStatusChangedMail::class, LetterApprovedMail::class, RespondentViewedCaseMail::class] as $mailable) {
        expect(is_subclass_of($mailable, ShouldQueue::class))->toBeTrue($mailable);
    }

    // The receipt carries raw PDF bytes, which a JSON job payload cannot hold.
    expect(is_subclass_of(ApplicantReceiptMail::class, ShouldQueue::class))->toBeFalse();
});

test('a staff message is queued to the applicant in the language it was written in', function (): void {
    Mail::fake();
    $applicant = partyApplicant('mail-queue');
    $caseId = partyCase($applicant, 'MQ/00001/26', ['review_status' => 'accepted']);

    $this->actingAs(inertiaStaff(['cases.view', 'cases.edit', 'message.create']))
        ->withSession(['app_locale' => 'am'])
        ->post(route('cases.messages.post', $caseId), ['body' => 'Please bring the original ID.'])
        ->assertRedirect();

    Mail::assertQueued(CaseMessageMail::class, fn (CaseMessageMail $mail): bool => $mail->hasTo($applicant->email) && $mail->locale === 'am');
    Mail::assertNothingSent();
});

test('the court and applicant hear about a respondent view once a day, not on every page load', function (): void {
    Mail::fake();
    $actor = partyApplicant('viewer');
    $owner = partyApplicant('viewer-owner');
    $caseId = partyCase($owner, 'MQ/00002/26', ['code' => '24680', 'review_status' => 'accepted']);
    $respondent = app(ActingRespondent::class)->resolve($actor);
    DB::table('respondent_case_views')->insert([
        'respondent_id' => $respondent->id, 'case_id' => $caseId, 'case_number' => 'MQ/00002/26',
        'viewed_at' => now(), 'created_at' => now(), 'updated_at' => now(),
    ]);

    $this->actingAs($actor, 'applicant')->get(route('respondent.cases.show', 'MQ/00002/26'))->assertOk();
    $this->actingAs($actor, 'applicant')->get(route('respondent.cases.show', 'MQ/00002/26'))->assertOk();

    Mail::assertQueued(RespondentViewedCaseMail::class, fn (RespondentViewedCaseMail $mail): bool => $mail->hasTo($owner->email));
    expect(Mail::queued(RespondentViewedCaseMail::class, fn (RespondentViewedCaseMail $mail): bool => $mail->hasTo($owner->email)))->toHaveCount(1)
        // Both views are still recorded in the case audit trail.
        ->and(DB::table('case_audits')->where('case_id', $caseId)->where('action', 'respondent_viewed')->count())->toBe(2);
});
