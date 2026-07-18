<?php

use App\Models\User;

test('profile page is displayed', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get('/profile');

    $response->assertOk();
});

test('profile information can be updated', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->patch('/profile', [
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/profile');

    $user->refresh();

    $this->assertSame('Test User', $user->name);
    $this->assertSame('test@example.com', $user->email);
    $this->assertNull($user->email_verified_at);
});

test('email verification status is unchanged when the email address is unchanged', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->patch('/profile', [
            'name' => 'Test User',
            'email' => $user->email,
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/profile');

    $this->assertNotNull($user->refresh()->email_verified_at);
});

test('user can delete their account', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->delete('/profile', [
            'password' => 'password',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/');

    $this->assertGuest();
    $this->assertNull($user->fresh());
});

test('sessions page shows real device and browser info from stored sessions', function () {
    $user = User::factory()->create();

    \Illuminate\Support\Facades\DB::table('sessions')->insert([
        'id' => 'other-session-id',
        'user_id' => $user->id,
        'ip_address' => '203.0.113.7',
        'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.4 Safari/605.1.15',
        'payload' => base64_encode(serialize([])),
        'last_activity' => now()->subMinutes(30)->getTimestamp(),
    ]);

    $response = $this
        ->actingAs($user)
        ->get('/profile/sessions');

    $response
        ->assertOk()
        ->assertSee('macOS')
        ->assertSee('Safari 17')
        ->assertSee('203.0.113.7');
});

test('sessions page shows exact os and browser version from stored client hints', function () {
    $user = User::factory()->create();

    \Illuminate\Support\Facades\DB::table('sessions')->insert([
        'id' => 'hinted-session-id',
        'user_id' => $user->id,
        'ip_address' => '203.0.113.9',
        'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/150.0.0.0 Safari/537.36 Edg/150.0.0.0',
        'payload' => base64_encode(serialize(['client_hints' => [
            'platform' => 'Windows',
            'platform_version' => '15.0.0',
            'brands' => '"Chromium";v="150.0.7204.50", "Microsoft Edge";v="150.0.3595.20", "Not?A_Brand";v="8.0.0.0"',
        ]])),
        'last_activity' => now()->getTimestamp(),
    ]);

    $this->actingAs($user)
        ->get('/profile/sessions')
        ->assertOk()
        ->assertSee('Windows 11')
        ->assertSee('Microsoft Edge 150.0.3595.20');
});

test('current session uses live client hint headers and hints are requested', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->withHeaders([
            'Sec-CH-UA-Platform' => '"Windows"',
            'Sec-CH-UA-Platform-Version' => '"10.0.0"',
            'Sec-CH-UA-Full-Version-List' => '"Chromium";v="150.0.7204.50", "Google Chrome";v="150.0.7204.50", "Not?A_Brand";v="8.0.0.0"',
        ])
        ->get('/profile/sessions');

    $response
        ->assertOk()
        ->assertSee('Windows 10')
        ->assertSee('Chrome 150.0.7204.50')
        ->assertHeader('Accept-CH');
});

test('user can revoke another session but not the current one', function () {
    $user = User::factory()->create();

    \Illuminate\Support\Facades\DB::table('sessions')->insert([
        'id' => 'other-session-id',
        'user_id' => $user->id,
        'ip_address' => '203.0.113.7',
        'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
        'payload' => base64_encode(serialize([])),
        'last_activity' => now()->getTimestamp(),
    ]);

    $this->actingAs($user)
        ->delete('/profile/sessions/other-session-id')
        ->assertRedirect();

    $this->assertDatabaseMissing('sessions', ['id' => 'other-session-id']);
});

test('correct password must be provided to delete account', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->from('/profile')
        ->delete('/profile', [
            'password' => 'wrong-password',
        ]);

    $response
        ->assertSessionHasErrorsIn('userDeletion', 'password')
        ->assertRedirect('/profile');

    $this->assertNotNull($user->fresh());
});
