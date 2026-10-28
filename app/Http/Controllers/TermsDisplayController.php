<?php

namespace App\Http\Controllers;

use App\Models\TermsAndCondition;
use App\Support\Ui\PublicSiteChrome;
use Inertia\Inertia;
use Inertia\Response;

class TermsDisplayController extends Controller
{
    public function show(): Response
    {
        $term = TermsAndCondition::published()->orderByDesc('published_at')->first();

        abort_if(!$term, 404);

        return Inertia::render('Court/Public/Terms', [
            'term' => [
                'id' => $term->id,
                'title' => (string) $term->title,
                'body' => (string) clean($term->body, 'cases'),
                'published_at' => $term->published_at?->toIso8601String(),
            ],
            'publicSite' => PublicSiteChrome::props(),
        ]);
    }
}
