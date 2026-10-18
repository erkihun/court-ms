<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

test('user cannot upload a signature through self profile', function (): void {
    Storage::fake('public');
    $user = User::factory()->create(['email_verified_at' => now()]);

    $this->actingAs($user)->from(route('profile.edit'))->patch(route('profile.update'), [
        'name' => $user->name,
        'email' => $user->email,
        'signature' => UploadedFile::fake()->image('signature.png', 500, 180),
    ])->assertRedirect(route('profile.edit'))->assertSessionHasErrors('signature');

    expect($user->fresh()->signature_path)->toBeNull();
    Storage::disk('public')->assertDirectoryEmpty('signatures');
});

test('user cannot replace or remove an existing signature through self profile', function (): void {
    Storage::fake('public');
    $signature = UploadedFile::fake()->image('existing.png')->store('signatures', 'public');
    $user = User::factory()->create([
        'email_verified_at' => now(),
        'signature_path' => $signature,
    ]);

    $this->actingAs($user)->from(route('profile.edit'))->patch(route('profile.update'), [
        'name' => $user->name,
        'email' => $user->email,
        'signature' => UploadedFile::fake()->image('replacement.png'),
        'remove_signature' => '1',
    ])->assertRedirect(route('profile.edit'))
        ->assertSessionHasErrors(['signature', 'remove_signature']);

    expect($user->fresh()->signature_path)->toBe($signature);
    Storage::disk('public')->assertExists($signature);
});

test('self profile page displays signature as read only', function (): void {
    $user = User::factory()->create(['email_verified_at' => now()]);

    // The page only shows the stored signature; uploads are refused by UpdateProfileRequest (above).
    $this->actingAs($user)->get(route('profile.edit'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Profile/Edit')
            ->where('profile.signatureUrl', null)
            ->missing('profile.canChangeSignature'));

    expect(__('auth.signature_admin_only'))->toContain('only be changed by an authorized administrator');
});
