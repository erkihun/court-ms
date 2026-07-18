<?php

use App\Models\User;
use App\Notifications\PasswordResetOtp;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;

// Staff password recovery is a custom OTP flow (PasswordResetLinkController /
// NewPasswordController), not Breeze's default token-link + ResetPassword notification.

test('reset password link screen can be rendered', function () {
    $response = $this->get('/forgot-password');

    $response->assertStatus(200);
});

test('requesting a reset code emails an OTP and redirects to the otp screen', function () {
    Notification::fake();

    $user = User::factory()->create();

    $this->post('/forgot-password', ['email' => $user->email])
        ->assertRedirect(route('admin.password.otp.show'));

    Notification::assertSentTo($user, PasswordResetOtp::class);
});

test('unknown email is rejected on the forgot-password form', function () {
    $this->post('/forgot-password', ['email' => 'nobody@example.com'])
        ->assertSessionHasErrors('email');
});

test('otp screen redirects to forgot-password without a pending request', function () {
    $this->get(route('admin.password.otp.show'))
        ->assertRedirect(route('password.request'));
});

test('invalid otp code is rejected', function () {
    Notification::fake();

    $user = User::factory()->create();

    $this->post('/forgot-password', ['email' => $user->email]);

    $this->post(route('admin.password.otp.verify'), ['code' => '000000'])
        ->assertSessionHasErrors('code');
});

test('password can be reset end-to-end with a valid otp code', function () {
    Notification::fake();

    $user = User::factory()->create();

    $this->post('/forgot-password', ['email' => $user->email]);

    Notification::assertSentTo($user, PasswordResetOtp::class, function (PasswordResetOtp $notification) {
        $code = (fn () => $this->code)->call($notification);

        $this->get(route('admin.password.otp.show'))->assertStatus(200);

        $this->post(route('admin.password.otp.verify'), ['code' => $code])
            ->assertRedirect(route('password.reset.new'));

        $this->get(route('password.reset.new'))->assertStatus(200);

        $this->post(route('password.store'), [
            'password' => 'NewPassword123',
            'password_confirmation' => 'NewPassword123',
        ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('login'));

        return true;
    });

    expect(Hash::check('NewPassword123', $user->fresh()->password))->toBeTrue();
});
