<?php

declare(strict_types=1);

use App\Models\Applicant;
use App\Models\Respondent;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Inertia\Testing\AssertableInertia as Assert;

function partyApplicant(string $tag): Applicant
{
    $applicant = Applicant::create([
        'first_name' => 'Hana',
        'middle_name' => 'B',
        'last_name' => 'Party',
        'gender' => 'female',
        'position' => 'Officer',
        'organization_name' => 'Org',
        'phone' => '09'.substr(sprintf('%08d', crc32($tag)), 0, 8),
        'email' => "party-{$tag}@example.test",
        'address' => 'Addis Ababa',
        'national_id_number' => substr(sprintf('%016d', crc32($tag)).'0000000000000000', 0, 16),
        'password' => Hash::make('password'),
        'is_active' => true,
        'is_lawyer' => false,
    ]);
    $applicant->markEmailAsVerified();

    return $applicant;
}

function partyCase(Applicant $applicant, string $number): int
{
    $typeId = DB::table('case_types')->insertGetId(['name' => 'Type '.$number, 'created_at' => now(), 'updated_at' => now()]);

    return DB::table('court_cases')->insertGetId([
        'applicant_id' => $applicant->id,
        'case_number' => $number,
        'title' => 'Case '.$number,
        'case_type_id' => $typeId,
        'filing_date' => now()->toDateString(),
        'status' => 'pending',
        'review_status' => 'awaiting_review',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}

test('the applicant dashboard renders with its stats', function (): void {
    $applicant = partyApplicant('dash');
    partyCase($applicant, 'PP-DASH-1');

    $this->actingAs($applicant, 'applicant')
        ->get(route('applicant.dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Applicant/Dashboard')
            ->where('stats.total', 1)
            ->where('stats.pending', 1)
            ->where('recent.0.caseNumber', 'PP-DASH-1')
            ->where('auth.applicant.actingAsRespondent', false));
});

test('the applicant bell lists unseen court messages and marking them seen clears them', function (): void {
    $applicant = partyApplicant('bell');
    $caseId = partyCase($applicant, 'PP-BELL-1');
    $staff = User::factory()->create();
    $messageId = DB::table('case_messages')->insertGetId([
        'case_id' => $caseId,
        'sender_user_id' => $staff->id,
        'body' => 'Please bring the original documents.',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    DB::table('case_messages')->insert([
        'case_id' => $caseId,
        'sender_user_id' => $staff->id,
        'body' => 'javascript://example.test/%0Aalert(1)',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $feed = $this->actingAs($applicant, 'applicant')->getJson(route('applicant.notifications.feed'))->assertOk();
    expect($feed->json('count'))->toBe(2)
        ->and($feed->json('groups.0.type'))->toBe('message')
        ->and(collect($feed->json('groups.0.items'))->pluck('url')->filter()->all())->toBe([]);

    $this->post(route('applicant.notifications.markOne'), ['type' => 'message', 'sourceId' => $messageId])->assertRedirect();
    expect($this->getJson(route('applicant.notifications.feed'))->json('count'))->toBe(1);

    $this->post(route('applicant.notifications.markAll'))->assertRedirect();
    expect($this->getJson(route('applicant.notifications.feed'))->json('count'))->toBe(0);
});

test('applicants only see notifications for their own cases', function (): void {
    $owner = partyApplicant('owner');
    $other = partyApplicant('other');
    $caseId = partyCase($owner, 'PP-OWN-1');
    DB::table('case_messages')->insert([
        'case_id' => $caseId,
        'sender_user_id' => User::factory()->create()->id,
        'body' => 'Private',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->actingAs($other, 'applicant')->getJson(route('applicant.notifications.feed'))->assertJsonPath('count', 0);
    $this->actingAs($other, 'applicant')->get(route('applicant.notifications.index'))
        ->assertInertia(fn (Assert $page) => $page->component('Applicant/Notifications/Index')->where('sections.message.total', 0));
});

test('marking a notification seen rejects unknown types', function (): void {
    $this->actingAs(partyApplicant('types'), 'applicant')
        ->post(route('applicant.notifications.markOne'), ['type' => 'anything', 'sourceId' => 1])
        ->assertSessionHasErrors('type');
});

test('respondent mode follows the area being viewed', function (): void {
    $applicant = partyApplicant('mode');

    $this->actingAs($applicant, 'applicant')
        ->post(route('applicant.switchToRespondent'))
        ->assertRedirect(route('respondent.dashboard'));

    $this->get(route('respondent.dashboard'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('Respondent/Dashboard')
            ->where('auth.applicant.actingAsRespondent', true));
    expect(session('acting_as_respondent'))->toBeTrue();

    // Opening an applicant page leaves respondent mode.
    $this->get(route('applicant.dashboard'))
        ->assertInertia(fn (Assert $page) => $page->where('auth.applicant.actingAsRespondent', false));
    expect(session('acting_as_respondent'))->toBeNull();
});

test('the respondent bell is scoped to the respondent record of the signed-in account', function (): void {
    $applicant = partyApplicant('resp-bell');

    $this->actingAs($applicant, 'applicant')
        ->getJson(route('respondent.notifications.feed'))
        ->assertOk()
        ->assertJson(['count' => 0, 'items' => []]);

    // Opening the dashboard links a respondent record to the account.
    $this->get(route('respondent.dashboard'))->assertOk();
    $respondent = Respondent::query()->where('email', $applicant->email)->firstOrFail();

    $caseId = partyCase(partyApplicant('resp-bell-owner'), 'PP-RESP-1');
    DB::table('respondent_case_views')->insert([
        'respondent_id' => $respondent->id,
        'case_id' => $caseId,
        'case_number' => 'PP-RESP-1',
        'viewed_at' => now(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $feed = $this->getJson(route('respondent.notifications.feed'))->assertOk();
    expect($feed->json('count'))->toBe(1)
        ->and($feed->json('items.0.caseNumber'))->toBe('PP-RESP-1');

    $this->post(route('respondent.notifications.markAll'))->assertRedirect();
    $this->getJson(route('respondent.notifications.feed'))->assertJsonPath('count', 0);
});

test('notification settings render and save', function (): void {
    $applicant = partyApplicant('prefs');

    $this->actingAs($applicant, 'applicant')
        ->get(route('applicant.notifications.settings'))
        ->assertInertia(fn (Assert $page) => $page->component('Applicant/Notifications/Settings')->where('prefs.email_weekly_digest', false));

    $this->post(route('applicant.notifications.settings.update'), ['email_status' => 1, 'email_weekly_digest' => 1])->assertRedirect();

    $this->get(route('applicant.notifications.settings'))
        ->assertInertia(fn (Assert $page) => $page->where('prefs.email_weekly_digest', true)->where('prefs.email_hearing', false));
});
