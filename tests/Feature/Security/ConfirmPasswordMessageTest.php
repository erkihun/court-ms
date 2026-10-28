<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Support\Facades\Hash;

/*
| A wrong password on the confirm-password screen said "Password" (the auth.password
| label key was reused). It now says the password is incorrect (#3, 1fc9fc5).
*/

test('a wrong password on the confirm screen says it is incorrect', function (): void {
    $user = User::factory()->create(['password' => Hash::make('Right!Passw0rd#1')]);

    $this->actingAs($user)->from(route('password.confirm'))
        ->post(route('password.confirm'), ['password' => 'wrong-password'])
        ->assertSessionHasErrors(['password' => __('validation.current_password')]);
});
