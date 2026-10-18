<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TermsAndCondition;
use App\Support\EthiopianDate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class TermsAndConditionsController extends Controller
{
    public function index(): Response
    {
        $terms = TermsAndCondition::orderByDesc('created_at')->paginate(10)->through(fn (TermsAndCondition $term): array => [
            'id' => $term->id,
            'title' => (string) $term->title,
            'excerpt' => Str::limit(strip_tags((string) $term->body), 120),
            'isPublished' => (bool) $term->is_published,
            'published' => $term->published_at ? EthiopianDate::format($term->published_at, withTime: true) : null,
            'editedRelative' => EthiopianDate::smartRelative($term->updated_at),
            'updatedDate' => EthiopianDate::format($term->updated_at),
            'updatedTime' => EthiopianDate::formatTime($term->updated_at, timeFormat: 'H:i'),
        ]);

        return Inertia::render('Admin/Terms/Index', [
            'terms' => $terms,
            'publicUrl' => route('public.terms'),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Terms/Form', ['term' => null]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['published_at'] = $data['is_published'] ? now() : null;
        $data['slug'] = Str::slug($data['title'].'-'.Str::random(4));

        TermsAndCondition::create($data);

        return redirect()->route('terms.index')->with('success', [
            'key' => 'messages.success.created',
            'replace' => ['resource' => __('messages.resources.terms')],
        ]);
    }

    public function edit(TermsAndCondition $term): Response
    {
        return Inertia::render('Admin/Terms/Form', [
            'term' => [
                'id' => $term->id,
                'title' => (string) $term->title,
                'body' => (string) $term->body,
                'isPublished' => (bool) $term->is_published,
            ],
        ]);
    }

    public function update(Request $request, TermsAndCondition $term): RedirectResponse
    {
        $data = $this->validated($request);
        if ($data['is_published'] && ! $term->published_at) {
            $data['published_at'] = now();
        } elseif (! $data['is_published']) {
            $data['published_at'] = null;
        }

        $term->update($data);

        return redirect()->route('terms.index')->with('success', [
            'key' => 'messages.success.updated',
            'replace' => ['resource' => __('messages.resources.terms')],
        ]);
    }

    public function destroy(TermsAndCondition $term): RedirectResponse
    {
        $term->delete();

        return redirect()->route('terms.index')->with('success', [
            'key' => 'messages.success.deleted',
            'replace' => ['resource' => __('messages.resources.terms')],
        ]);
    }

    /** @return array{title: string, body: string, is_published: bool} */
    private function validated(Request $request): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
            'is_published' => ['nullable', 'boolean'],
        ]);
        $data['is_published'] = (bool) ($data['is_published'] ?? false);

        return $data;
    }
}
