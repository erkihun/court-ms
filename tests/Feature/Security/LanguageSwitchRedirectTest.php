<?php

declare(strict_types=1);

/*
| The language switch sends users back where they came from. The return URL is
| user input, so it may only point back into this application.
*/

test('the language switch never redirects to another site', function (string $target): void {
    $this->get(route('language.switch', ['locale' => 'en', 'return' => $target]))
        ->assertRedirect(route('root'));
})->with([
    'absolute foreign url' => 'https://evil.example/phish',
    'protocol-relative url' => '//evil.example/phish',
    'backslash trick' => '/\\evil.example',
    'javascript scheme' => 'javascript:alert(1)',
    'foreign host with our path' => 'https://evil.example'.'/applicant/login',
]);

test('the language switch returns to a page in this application', function (): void {
    $this->get(route('language.switch', ['locale' => 'am', 'return' => url('/applicant/login?x=1')]))
        ->assertRedirect(url('/applicant/login?x=1'));

    $this->get(route('language.switch', ['locale' => 'en', 'return' => '/home']))
        ->assertRedirect(url('/home'));
});

test('the language switch still stores the chosen locale', function (): void {
    $this->get(route('language.switch', ['locale' => 'am', 'return' => 'https://evil.example']))
        ->assertSessionHas('app_locale', 'am');
});
