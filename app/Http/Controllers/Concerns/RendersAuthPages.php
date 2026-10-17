<?php

declare(strict_types=1);

namespace App\Http\Controllers\Concerns;

use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

trait RendersAuthPages
{
    /**
     * Render an auth screen that must never be served from a cache: it carries
     * a CSRF-bound form and, after logout, must not show a stale session.
     *
     * @param  array<string, mixed>  $props
     */
    protected function authPage(string $component, array $props = []): Response
    {
        $response = Inertia::render($component, $props)->toResponse(request());
        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        $response->headers->set('Pragma', 'no-cache');

        return $response;
    }
}
