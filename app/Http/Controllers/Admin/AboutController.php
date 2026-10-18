<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AboutPage;
use App\Support\EthiopianDate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class AboutController extends Controller
{
    public function index(): Response
    {
        $pages = AboutPage::orderByDesc('updated_at')->paginate(10)->through(fn (AboutPage $about): array => [
            'id' => $about->id,
            'title' => (string) $about->title,
            'slug' => (string) $about->slug,
            'isPublished' => (bool) $about->is_published,
            'updated' => EthiopianDate::smartFormat($about->updated_at, true, '', 'h:i A', 'Y-m-d'),
        ]);

        return Inertia::render('Admin/About/Index', ['pages' => $pages]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/About/Form', ['about' => null]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'slug' => ['nullable', 'string', 'max:200', 'alpha_dash', 'unique:about_pages,slug'],
            'body' => ['required', 'string'],
            'is_published' => ['sometimes', 'boolean'],
        ]);

        AboutPage::create([
            'title' => $data['title'],
            'slug' => $this->uniqueSlug($data['slug'] ?? Str::slug($data['title'])),
            'body' => $this->cleanBody($data['body']),
            'is_published' => (bool) ($data['is_published'] ?? false),
            'created_by_user_id' => $request->user()?->id,
            'updated_by_user_id' => $request->user()?->id,
        ]);

        return redirect()->route('about.index')->with('success', __('about.saved'));
    }

    public function show(AboutPage $about): Response
    {
        return Inertia::render('Admin/About/Show', [
            'about' => [
                'id' => $about->id,
                'title' => (string) $about->title,
                'slug' => (string) $about->slug,
                'isPublished' => (bool) $about->is_published,
                'bodyHtml' => $this->cleanBody((string) $about->body),
            ],
        ]);
    }

    public function edit(AboutPage $about): Response
    {
        return Inertia::render('Admin/About/Form', [
            'about' => [
                'id' => $about->id,
                'title' => (string) $about->title,
                'slug' => (string) $about->slug,
                'body' => (string) $about->body,
                'isPublished' => (bool) $about->is_published,
            ],
        ]);
    }

    public function update(Request $request, AboutPage $about): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'slug' => ['nullable', 'string', 'max:200', 'alpha_dash', 'unique:about_pages,slug,'.$about->id],
            'body' => ['required', 'string'],
            'is_published' => ['sometimes', 'boolean'],
        ]);

        $about->update([
            'title' => $data['title'],
            'slug' => $this->uniqueSlug($data['slug'] ?? Str::slug($data['title']), $about->id),
            'body' => $this->cleanBody($data['body']),
            'is_published' => (bool) ($data['is_published'] ?? false),
            'updated_by_user_id' => $request->user()?->id,
        ]);

        return redirect()->route('about.index')->with('success', __('about.updated'));
    }

    public function destroy(AboutPage $about): RedirectResponse
    {
        $about->delete();

        return redirect()->route('about.index')->with('success', __('about.deleted'));
    }

    /** The body comes from the rich-text editor; the public page sanitises it again on output. */
    private function cleanBody(string $html): string
    {
        return (string) clean($html, 'cases');
    }

    private function uniqueSlug(string $slug, ?int $ignoreId = null): string
    {
        $base = $slug === '' ? 'about' : $slug;
        $candidate = $base;
        $i = 2;

        while (
            AboutPage::query()
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->where('slug', $candidate)
                ->exists()
        ) {
            $candidate = $base.'-'.$i;
            $i++;
        }

        return $candidate;
    }
}
