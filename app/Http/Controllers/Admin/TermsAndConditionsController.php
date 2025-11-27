<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TermsAndCondition;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class TermsAndConditionsController extends Controller
{
    public function index(): View
    {
        $terms = TermsAndCondition::orderByDesc('created_at')->paginate(10);

        return view('admin.terms.index', compact('terms'));
    }

    public function create(): View
    {
        return view('admin.terms.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title'        => ['required', 'string', 'max:255'],
            'body'         => ['required', 'string'],
            'is_published' => ['nullable', 'boolean'],
        ]);

        $data['is_published'] = (bool) ($data['is_published'] ?? false);
        $data['published_at'] = $data['is_published'] ? now() : null;
        $data['slug'] = Str::slug($data['title'] . '-' . Str::random(4));

        TermsAndCondition::create($data);

        return redirect()->route('terms.index')->with('success', 'Terms created successfully.');
    }

    public function edit(TermsAndCondition $term): View
    {
        return view('admin.terms.edit', compact('term'));
    }

    public function update(Request $request, TermsAndCondition $term): RedirectResponse
    {
        $data = $request->validate([
            'title'        => ['required', 'string', 'max:255'],
            'body'         => ['required', 'string'],
            'is_published' => ['nullable', 'boolean'],
        ]);

        $data['is_published'] = (bool) ($data['is_published'] ?? false);
        if ($data['is_published'] && !$term->published_at) {
            $data['published_at'] = now();
        } elseif (!$data['is_published']) {
            $data['published_at'] = null;
        }

        $term->update($data);

        return redirect()->route('terms.index')->with('success', 'Terms updated.');
    }

    public function destroy(TermsAndCondition $term): RedirectResponse
    {
        $term->delete();

        return redirect()->route('terms.index')->with('success', 'Terms deleted.');
    }
}
