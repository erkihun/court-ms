<?php

declare(strict_types=1);

/*
| ID-style route parameters are constrained to digits, so a malformed segment no
| longer reaches an int-typed controller argument (which threw a TypeError 500).
*/

test('a non-numeric id never matches an id route', function (string $uri): void {
    $this->get($uri)->assertNotFound();
})->with([
    'admin case' => '/admin/cases/abc',
    'admin case assign' => '/admin/cases/abc/assign',
    'admin case file download' => '/admin/cases/1/files/abc/download',
    'applicant case' => '/applicant/cases/abc',
    'applicant case receipt' => '/applicant/cases/abc/receipt',
    'applicant evidence download' => '/applicant/cases/1/evidences/abc/download',
    'decision' => '/admin/decisions/abc',
    'user' => '/users/abc',
]);

test('a numeric id still reaches its route', function (): void {
    // Guests are sent to log in, which proves the route matched.
    $this->get('/admin/cases/1')->assertRedirect();
    $this->get('/applicant/cases/1')->assertRedirect();
});
