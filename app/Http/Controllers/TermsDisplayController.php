<?php

namespace App\Http\Controllers;

use App\Models\TermsAndCondition;
use Illuminate\View\View;

class TermsDisplayController extends Controller
{
    public function show(): View
    {
        $term = TermsAndCondition::published()->orderByDesc('published_at')->first();

        abort_if(!$term, 404);

        return view('public.terms', compact('term'));
    }
}
