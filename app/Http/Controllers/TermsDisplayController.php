<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\TermsAndCondition;
use App\Support\EthiopianDate;
use Inertia\Inertia;
use Inertia\Response;

class TermsDisplayController extends Controller
{
    public function show(): Response
    {
        $term = TermsAndCondition::published()->orderByDesc('published_at')->first();

        abort_if(! $term, 404);

        return Inertia::render('Public/Terms', [
            'title' => (string) $term->title,
            'published' => $term->published_at ? EthiopianDate::format($term->published_at, withTime: true) : null,
            'bodyHtml' => clean((string) $term->body, 'cases'),
        ]);
    }
}
