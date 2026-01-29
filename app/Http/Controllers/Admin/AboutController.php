<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AboutPage;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AboutController extends Controller
{
    public function index()
    {
        $aboutPages = AboutPage::orderByDesc('updated_at')->paginate(10);

        return view('admin.about.index', compact('aboutPages'));
    }

    public function create()
    {
        return view('admin.about.create');
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
        return view('admin.about.show', compact('about'));
    }

    public function edit(AboutPage $about)
    {
        return view('admin.about.edit', compact('about'));
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
