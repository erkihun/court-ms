<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AboutPage;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class AboutController extends Controller
{
    public function index()
    {
        $aboutPages = AboutPage::orderByDesc('updated_at')->paginate(10);

        // The list (React, Court/About/Index).
        return Inertia::render('Court/About/Index', [
            'pages' => $aboutPages->through(fn (AboutPage $page): array => [
                'id' => (int) $page->id,
                'title' => (string) $page->title,
                'slug' => (string) $page->slug,
                'is_published' => (bool) $page->is_published,
                'updated_at' => $page->updated_at?->toIso8601String(),
            ]),
        ]);
    }

    public function create()
    {
        return Inertia::render('Court/About/Form', ['page' => null]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'slug' => ['nullable', 'string', 'max:200', 'alpha_dash', 'unique:about_pages,slug'],
            'body' => ['required', 'string'],
            'is_published' => ['sometimes', 'boolean'],
        ]);

        $slug = $data['slug'] ?? Str::slug($data['title']);
        $slug = $this->uniqueSlug($slug);

        AboutPage::create([
            'title' => $data['title'],
            'slug' => $slug,
            'body' => $data['body'],
            'is_published' => (bool) ($data['is_published'] ?? false),
            'created_by_user_id' => $request->user()?->id,
            'updated_by_user_id' => $request->user()?->id,
        ]);

        return redirect()->route('about.index')->with('success', __('about.saved'));
    }

    public function show(AboutPage $about)
    {
        return Inertia::render('Court/About/Show', ['page' => $this->pageProps($about)]);
    }

    public function edit(AboutPage $about)
    {
        return Inertia::render('Court/About/Form', ['page' => $this->pageProps($about)]);
    }

    /** @return array{id: int, title: string, slug: string, body: string, is_published: bool} */
    private function pageProps(AboutPage $about): array
    {
        return [
            'id' => (int) $about->id,
            'title' => (string) $about->title,
            'slug' => (string) $about->slug,
            'body' => (string) $about->body,
            'is_published' => (bool) $about->is_published,
        ];
    }

    public function update(Request $request, AboutPage $about)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'slug' => ['nullable', 'string', 'max:200', 'alpha_dash', 'unique:about_pages,slug,' . $about->id],
            'body' => ['required', 'string'],
            'is_published' => ['sometimes', 'boolean'],
        ]);

        $slug = $data['slug'] ?? Str::slug($data['title']);
        $slug = $this->uniqueSlug($slug, $about->id);

        $about->update([
            'title' => $data['title'],
            'slug' => $slug,
            'body' => $data['body'],
            'is_published' => (bool) ($data['is_published'] ?? false),
            'updated_by_user_id' => $request->user()?->id,
        ]);

        return redirect()->route('about.index')->with('success', __('about.updated'));
    }

    public function destroy(AboutPage $about)
    {
        $about->delete();

        return redirect()->route('about.index')->with('success', __('about.deleted'));
    }

    private function uniqueSlug(string $slug, ?int $ignoreId = null): string
    {
        $base = $slug === '' ? 'about' : $slug;
        $candidate = $base;
        $i = 2;

        while (
            AboutPage::query()
                ->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))
                ->where('slug', $candidate)
                ->exists()
        ) {
            $candidate = $base . '-' . $i;
            $i++;
        }

        return $candidate;
    }
}
