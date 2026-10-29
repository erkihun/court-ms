<?php

test('staff password changes reject weak passwords', function () {
    // Staff accounts are created by administrators; the policy applies when a
    // staff member sets their own password.
    $user = \App\Models\User::factory()->create(['password' => \Illuminate\Support\Facades\Hash::make('Current!Passw0rd#1')]);

    $this->actingAs($user)
        ->from(route('profile.edit'))
        ->put(route('password.update'), [
            'current_password' => 'Current!Passw0rd#1',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])
        ->assertSessionHasErrors('password');

    expect(\Illuminate\Support\Facades\Hash::check('Current!Passw0rd#1', $user->fresh()->password))->toBeTrue();
});

test('applicant registration rejects weak passwords', function () {
    $response = $this->post(route('applicant.register.submit'), [
        'first_name' => 'Test',
        'middle_name' => 'Weak',
        'last_name' => 'Applicant',
        'gender' => 'male',
        'position' => 'Officer',
        'organization_name' => 'Test Org',
        'phone' => '0911000000',
        'email' => 'weak-applicant@example.com',
        'address' => 'Addis Ababa',
        'is_lawyer' => 0,
        'national_id_number' => '1234567890123456',
        'password' => '123456',
        'password_confirmation' => '123456',
    ]);

    $response->assertSessionHasErrors('password');
});
