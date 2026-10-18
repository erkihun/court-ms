<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;

test('the staff profile page opens on the security tab after a password change', function (): void {
    $user = inertiaStaff([]);

    $this->actingAs($user)
        ->get(route('profile.edit'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Profile/Edit')
            ->where('profile.email', $user->email)
            ->where('mfa', ['confirmed' => false, 'requiredByRole' => false])
            ->where('initialTab', 'profile'));

    $this->actingAs($user)
        ->withSession(['status' => 'password-updated'])
        ->get(route('profile.edit'))
        ->assertInertia(fn (Assert $page) => $page->where('initialTab', 'security'));
});

test('the sessions page hides raw ids and other portals sharing the same user id', function (): void {
    $user = inertiaStaff([]);
    $row = fn (string $id, array $payload): array => [
        'id' => $id,
        'user_id' => $user->id,
        'ip_address' => '203.0.113.9',
        'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0',
        'payload' => base64_encode(serialize($payload)),
        'last_activity' => now()->getTimestamp(),
    ];
    DB::table('sessions')->insert([$row('staff-other-session', []), $row('applicant-same-id', ['_auth_guard' => 'applicant'])]);

    $response = $this->actingAs($user)->get(route('profile.sessions.index'))->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Admin/Profile/Sessions')
        ->where('sessions', fn ($sessions) => collect($sessions)->count() === 2
            && collect($sessions)->where('isCurrent', false)->count() === 1));
    expect($response->getContent())->not->toContain('staff-other-session')->not->toContain('applicant-same-id');

    // Signing out "other sessions" leaves the applicant portal's row alone.
    $this->actingAs($user)->delete(route('profile.sessions.destroyOthers'))->assertRedirect();
    $this->assertDatabaseMissing('sessions', ['id' => 'staff-other-session']);
    $this->assertDatabaseHas('sessions', ['id' => 'applicant-same-id']);
});

test('disabling MFA lands back on the security tab with a toast', function (): void {
    $user = inertiaStaff([]);
    $user->forceFill(['mfa_secret' => 'JBSWY3DPEHPK3PXP', 'mfa_confirmed_at' => now()])->save();

    $this->actingAs($user)
        ->delete(route('mfa.setup.destroy'), ['password' => 'password'])
        ->assertRedirect(route('profile.edit', ['tab' => 'security']))
        ->assertSessionHas('success', __('auth.mfa_disabled'));

    expect($user->fresh()->mfa_confirmed_at)->toBeNull();
});

test('the notifications page lists unseen items per type for staff who can see cases', function (): void {
    $applicant = partyApplicant('notif-page');
    $caseId = partyCase($applicant, 'NOTIF/00001/26', ['created_at' => now()->subDay()]);

    $this->actingAs(inertiaStaff(['cases.view']))
        ->get(route('admin.notifications.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Notifications/Index')
            ->has('sections.message.data', 0)
            ->where('sections.case.total', 1)
            ->where('sections.case.data.0.caseId', $caseId)
            ->where('sections.case.data.0.href', route('cases.show', $caseId))
            ->has('sections.hearing')
            ->has('sections.respondent_view'));

    $this->actingAs(inertiaStaff(['users.manage']))
        ->get(route('admin.notifications.index'))
        ->assertForbidden();
});
