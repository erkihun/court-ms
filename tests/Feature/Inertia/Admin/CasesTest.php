<?php

declare(strict_types=1);

use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function (): void {
    $this->applicant = partyApplicant('admin-cases');
});

test('the case list renders with its filters and hides unreviewed cases from non-reviewers', function (): void {
    partyCase($this->applicant, 'ADM/1/26', ['review_status' => 'accepted', 'status' => 'active']);
    partyCase($this->applicant, 'ADM/2/26');

    $this->actingAs(inertiaStaff(['cases.view']))
        ->get(route('cases.index', ['status' => 'active']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Cases/Index')
            ->where('filters.status', 'active')
            ->has('cases.data', 1)
            ->where('cases.data.0.caseNumber', 'ADM/1/26')
            ->where('cases.data.0.reviewStatus', 'accepted')
            ->has('types')
            ->has('users'));

    $this->actingAs(inertiaStaff(['cases.view', 'cases.review']))
        ->get(route('cases.index'))
        ->assertInertia(fn (Assert $page) => $page->has('cases.data', 2));
});

test('the case page shares the case, abilities and only the sections the user may see', function (): void {
    $caseId = partyCase($this->applicant, 'ADM/3/26', ['review_status' => 'accepted', 'description' => '<p>Facts</p><script>alert(1)</script>']);
    DB::table('case_messages')->insert(['case_id' => $caseId, 'sender_applicant_id' => $this->applicant->id, 'body' => 'Hello court', 'created_at' => now(), 'updated_at' => now()]);
    DB::table('case_files')->insert(['case_id' => $caseId, 'label' => 'Exhibit', 'path' => 'case_files/x.pdf', 'mime' => 'application/pdf', 'size' => 2048, 'created_at' => now(), 'updated_at' => now()]);

    $this->actingAs(inertiaStaff(['cases.view']))
        ->get(route('cases.show', $caseId))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Cases/Show')
            ->where('case.caseNumber', 'ADM/3/26')
            ->where('case.locked', false)
            ->where('case.descriptionHtml', fn (string $html) => str_contains($html, 'Facts') && ! str_contains($html, '<script'))
            ->where('abilities.createMessages', false)
            ->where('abilities.filesSection', false)
            ->where('messages.0.body', 'Hello court')
            ->where('messages.0.fromStaff', false)
            ->where('files', [])
            ->where('hearings', []));

    $this->actingAs(inertiaStaff(['cases.view', 'file.view', 'message.create']))
        ->get(route('cases.show', $caseId))
        ->assertInertia(fn (Assert $page) => $page
            ->where('abilities.createMessages', true)
            ->where('files.0.label', 'Exhibit')
            ->where('files.0.url', route('cases.files.download', [$caseId, DB::table('case_files')->value('id')])));
});

test('team members only reach the cases assigned to them, for reading and writing', function (): void {
    $member = inertiaStaff(['cases.view', 'cases.edit', 'message.create']);
    Team::query()->create(['name' => 'Bench A'])->users()->attach($member);
    $mine = partyCase($this->applicant, 'ADM/4/26', ['review_status' => 'accepted', 'assigned_member_user_id' => $member->id]);
    $other = partyCase($this->applicant, 'ADM/5/26', ['review_status' => 'accepted']);

    $this->actingAs($member)->get(route('cases.show', $mine))->assertOk();
    $this->actingAs($member)->get(route('cases.show', $other))->assertForbidden();

    $this->actingAs($member)->post(route('cases.messages.post', $other), ['body' => 'Not my case'])->assertForbidden();
    $this->actingAs($member)->post(route('cases.messages.post', $mine), ['body' => 'My case'])->assertRedirect();

    expect(DB::table('case_messages')->pluck('body')->all())->toBe(['My case']);
});

test('case actions need their own permission, not just cases.edit', function (): void {
    $caseId = partyCase($this->applicant, 'ADM/6/26', ['review_status' => 'accepted']);
    $editor = inertiaStaff(['cases.view', 'cases.edit']);

    $this->actingAs($editor)->post(route('cases.messages.post', $caseId), ['body' => 'Hi'])->assertForbidden();
    $this->actingAs($editor)->post(route('cases.hearings.store', $caseId), [
        'hearing_at' => now()->addWeek()->format('Y-m-d').'T09:00:00',
        'type' => 'Mention',
        'notes' => 'First hearing',
    ])->assertForbidden();

    expect(DB::table('case_messages')->count())->toBe(0)
        ->and(DB::table('case_hearings')->count())->toBe(0);
});

test('hearings are scheduled once per day and past hearings are locked', function (): void {
    $caseId = partyCase($this->applicant, 'ADM/7/26', ['review_status' => 'accepted']);
    $clerk = inertiaStaff(['cases.view', 'cases.edit', 'hearing.view', 'hearing.create', 'hearing.delete']);
    $day = now()->addWeek()->format('Y-m-d');
    $payload = ['hearing_at' => "{$day}T09:00:00", 'type' => 'Mention', 'location' => 'Room 2', 'notes' => 'First hearing'];

    $this->actingAs($clerk)->from(route('cases.show', $caseId))->post(route('cases.hearings.store', $caseId), $payload)->assertRedirect(route('cases.show', $caseId));
    $this->actingAs($clerk)->from(route('cases.show', $caseId))->post(route('cases.hearings.store', $caseId), $payload)->assertSessionHasErrors('hearing_at');

    $past = DB::table('case_hearings')->insertGetId(['case_id' => $caseId, 'hearing_at' => now()->subDay(), 'type' => 'Old', 'created_at' => now(), 'updated_at' => now()]);

    $this->actingAs($clerk)
        ->get(route('cases.show', $caseId))
        ->assertInertia(fn (Assert $page) => $page
            ->has('hearings', 2)
            ->where('hearings.0.date', $day)
            ->where('hearings.0.time', '09:00')
            ->where('hearings.0.locked', false)
            ->where('hearings.1.locked', true));

    $this->actingAs($clerk)->delete(route('cases.hearings.delete', $past));
    expect(DB::table('case_hearings')->where('id', $past)->exists())->toBeTrue();
});

test('a closed case with a published decision accepts no further changes', function (): void {
    $caseId = partyCase($this->applicant, 'ADM/8/26', ['review_status' => 'accepted', 'status' => 'closed']);
    DB::table('decisions')->insert([
        'court_case_id' => $caseId,
        'case_number' => 'ADM/8/26',
        'name' => 'Judgment',
        'decision_content' => '<p>Final</p>',
        'status' => 'published',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    $clerk = inertiaStaff(['cases.view', 'cases.edit', 'message.create']);

    $this->actingAs($clerk)
        ->get(route('cases.show', $caseId))
        ->assertInertia(fn (Assert $page) => $page->where('case.locked', true));

    $this->actingAs($clerk)
        ->from(route('cases.show', $caseId))
        ->post(route('cases.messages.post', $caseId), ['body' => 'Too late'])
        ->assertRedirect(route('cases.show', $caseId))
        ->assertSessionHas('error', 'cases.show.actions_locked');

    expect(DB::table('case_messages')->count())->toBe(0);
});

test('reviewers can return a case with a note', function (): void {
    $caseId = partyCase($this->applicant, 'ADM/9/26');
    $reviewer = inertiaStaff(['cases.view', 'cases.review']);

    $this->actingAs($reviewer)
        ->from(route('cases.show', $caseId))
        ->patch(route('cases.review.update', $caseId), ['decision' => 'return', 'note' => ''])
        ->assertSessionHasErrors('note');

    $this->actingAs($reviewer)
        ->from(route('cases.show', $caseId))
        ->patch(route('cases.review.update', $caseId), ['decision' => 'return', 'note' => 'Attach the ID'])
        ->assertRedirect(route('cases.show', $caseId));

    expect(DB::table('court_cases')->where('id', $caseId)->value('review_status'))->toBe('returned');
});

test('the assignment page lists team leaders for administrators', function (): void {
    $caseId = partyCase($this->applicant, 'ADM/10/26', ['review_status' => 'accepted']);
    $leader = User::factory()->create(['status' => 'active', 'name' => 'Leader One']);
    Team::query()->create(['name' => 'Bench B', 'team_leader_id' => $leader->id]);

    $this->actingAs(inertiaStaff(['cases.view', 'cases.assign', 'cases.assign.team']))
        ->get(route('cases.assign.form', $caseId))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Cases/Assign')
            ->where('mode', 'admin')
            ->where('case.caseNumber', 'ADM/10/26')
            ->where('groups.0.users.0.name', 'Leader One')
            ->where('canAssignLeaders', true));
});
